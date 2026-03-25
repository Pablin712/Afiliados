<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;

class CreateTestUsersCommand extends Command
{
    /**
     * php artisan users:create-test --sponsor=1 --free=3 --customer=5
     *
     * @var string
     */
    protected $signature = 'users:create-test
                            {--sponsor= : ID o código de afiliado del sponsor (requerido)}
                            {--free=0   : Usuarios que NO realizan pago (membresía free)}
                            {--customer=0 : Usuarios con pago PENDIENTE (esperando aprobación)}
                            {--password=Test1234* : Contraseña para todos los usuarios creados}';

    protected $description = 'Crea usuarios de prueba bajo un sponsor específico, con control sobre cuántos son free o tienen pago pendiente.';

    public function handle(): int
    {
        // ── Resolver sponsor ────────────────────────────────────────────────
        $sponsorInput = $this->option('sponsor');

        if ($sponsorInput === null || $sponsorInput === '') {
            $this->error('Debes indicar un sponsor con --sponsor=<id_o_código>');
            return self::FAILURE;
        }

        $sponsor = $this->resolveSponsor((string) $sponsorInput);

        if ($sponsor === null) {
            $this->error("No se encontró ningún usuario con ID o código de afiliado: {$sponsorInput}");
            return self::FAILURE;
        }

        // ── Cantidades ───────────────────────────────────────────────────────
        $freeCount     = max(0, (int) $this->option('free'));
        $customerCount = max(0, (int) $this->option('customer'));
        $total         = $freeCount + $customerCount;

        if ($total === 0) {
            $this->error('Indica al menos un usuario: --free=N y/o --customer=N');
            return self::FAILURE;
        }

        $password = (string) $this->option('password');

        // ── Dependencias del flujo de pago ───────────────────────────────────
        $freeMembershipType = MembershipType::query()->where('name', 'free')->first();

        if ($freeMembershipType === null) {
            $this->error('No existe el tipo de membresía "free". Corre los seeders base primero.');
            return self::FAILURE;
        }

        $program = null;
        $bank    = null;

        if ($customerCount > 0) {
            $program = Program::query()->where('is_active', true)->orderBy('id')->first();
            $bank    = Bank::query()->orderBy('id')->first();

            if ($program === null) {
                $this->error('No existe ningún programa activo. Corre los seeders base primero.');
                return self::FAILURE;
            }

            if ($bank === null) {
                $this->error('No existe ningún banco registrado. Corre los seeders base primero.');
                return self::FAILURE;
            }
        }

        // ── Crear usuarios ──────────────────────────────────────────────────
        $faker   = Faker::create('es_ES');
        $results = [];

        $this->info("Sponsor: [{$sponsor->id}] {$sponsor->name}");
        $this->info("Creando {$total} usuarios ({$freeCount} free · {$customerCount} con pago pendiente)...");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        for ($i = 0; $i < $total; $i++) {
            $type = $i < $freeCount ? 'free' : 'customer';

            try {
                $userData = $this->generateUniqueUserData($faker, $password);

                $result = DB::transaction(function () use ($userData, $sponsor, $freeMembershipType, $type, $program, $bank): array {
                    // 1. Crear usuario
                    $user = User::create([
                        'name'           => $userData['name'],
                        'email'          => $userData['email'],
                        'identification' => $userData['identification'],
                        'password'       => Hash::make($userData['password']),
                        'sponsor_id'     => $sponsor->id,
                        'approved_at'    => now(),
                    ]);

                    if (Schema::hasColumn('users', 'affiliate_code')) {
                        $user->affiliate_code = User::buildAffiliateCode($user->name, $user->id);
                        $user->save();
                    }

                    $user->assignRole('user');

                    // 2. Membresía inicial free (igual que el registro real)
                    Membership::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'membership_type_id' => $freeMembershipType->id,
                            'status'             => 'free',
                            'started_at'         => now(),
                            'expires_at'         => null,
                            'last_payment_id'    => null,
                        ]
                    );

                    // 3. Si es customer: crear pago pendiente (igual que PlansController::store)
                    if ($type === 'customer' && $program !== null && $bank !== null) {
                        $transaction = Transaction::create([
                            'bank_id'         => $bank->id,
                            'type'            => 'income',
                            'amount_previous' => 0,
                            'amount'          => 0,
                            'amount_now'      => 0,
                            'detail'          => null,
                            'is_annulled'     => false,
                            'created_at'      => now(),
                        ]);

                        Payment::create([
                            'user_id'        => $user->id,
                            'program_id'     => $program->id,
                            'transaction_id' => $transaction->id,
                            'number'         => 'TEST-' . strtoupper(substr(md5($user->id . microtime()), 0, 8)),
                            'photo'          => null,
                            'amount'         => (float) $program->first_payment_cost,
                            'state'          => 'pending',
                        ]);
                    }

                    return [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'pass'  => $userData['password'],
                        'type'  => $type,
                        'code'  => $user->affiliate_code ?? User::buildAffiliateCode($user->name, $user->id),
                    ];
                });

                $results[] = $result;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Error al crear usuario #{$i}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ── Tabla de resultados ──────────────────────────────────────────────
        $this->table(
            ['ID', 'Nombre', 'Email', 'Contraseña', 'Código afiliado', 'Tipo'],
            array_map(fn (array $r) => [
                $r['id'],
                $r['name'],
                $r['email'],
                $r['pass'],
                $r['code'],
                $r['type'] === 'free' ? '<comment>free</comment>' : '<info>pendiente ▷ aprobar/rechazar</info>',
            ], $results)
        );

        $created  = count($results);
        $createdF = count(array_filter($results, fn ($r) => $r['type'] === 'free'));
        $createdC = count(array_filter($results, fn ($r) => $r['type'] === 'customer'));

        $this->newLine();
        $this->line("  Sponsor : <options=bold>[{$sponsor->id}] {$sponsor->name}</>");
        if ($program !== null) {
            $this->line("  Programa: <options=bold>{$program->name}</> · Monto pago: <options=bold>\${$program->first_payment_cost}</>");
        }
        $this->line("  Total creados : <info>{$created}</info>  (<comment>free: {$createdF}</comment> · <info>pendientes: {$createdC}</info>)");
        $this->newLine();
        $this->line('  Los pagos pendientes están en <options=bold>Admin → Registros pendientes</>.');
        $this->newLine();

        return self::SUCCESS;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveSponsor(string $input): ?User
    {
        // Intentar por ID numérico
        if (ctype_digit($input)) {
            return User::query()->find((int) $input);
        }

        // Intentar por código de afiliado
        return User::resolveAffiliateCode($input);
    }

    private function generateUniqueUserData(\Faker\Generator $faker, string $password): array
    {
        $maxAttempts = 20;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $name           = $faker->firstName() . ' ' . $faker->lastName();
            $email          = 'test.' . strtolower(str_replace(' ', '.', $name)) . '.' . mt_rand(100, 999) . '@test.local';
            $identification = (string) mt_rand(10000000, 99999999);

            $emailExists = User::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->exists();
            $idExists    = User::query()->where('identification', $identification)->exists();

            if (! $emailExists && ! $idExists) {
                return compact('name', 'email', 'identification', 'password');
            }
        }

        // Fallback con timestamp para garantizar unicidad
        $name           = $faker->firstName() . ' ' . $faker->lastName();
        $email          = 'test.user.' . time() . '.' . mt_rand(1000, 9999) . '@test.local';
        $identification = (string) (time() + mt_rand(1, 99999));

        return compact('name', 'email', 'identification', 'password');
    }
}
