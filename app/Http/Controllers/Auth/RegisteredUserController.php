<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use App\Services\RegistrationWhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly RegistrationWhatsappService $registrationWhatsappService)
    {
    }

    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $refCode = trim($request->string('ref')->toString());

        $sponsor = User::resolveAffiliateCode($refCode);

        if ($sponsor === null) {
            $sponsor = $this->resolveDefaultSponsor();
        }

        abort_if($sponsor === null, 404);

        return view('auth.register', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Check if registration fields are available.
     */
    public function availability(Request $request): JsonResponse
    {
        $email = Str::lower(trim($request->string('email')->toString()));
        $identification = trim($request->string('identification')->toString());

        $emailExists = $email !== ''
            ? User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()
            : false;

        $identificationExists = $identification !== ''
            ? User::query()->where('identification', $identification)->exists()
            : false;

        return response()->json([
            'email_exists' => $emailExists,
            'identification_exists' => $identificationExists,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:32', 'unique:'.User::class.',phone'],
            'identification' => ['required', 'string', 'max:50', 'unique:'.User::class.',identification'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->string('phone')->toString(),
                'identification' => $request->string('identification')->toString(),
                'password' => Hash::make($request->password),
                'sponsor_id' => $request->integer('sponsor_id'),
                'approved_at' => now(),
            ]);

            if (Schema::hasColumn('users', 'affiliate_code')) {
                $user->affiliate_code = User::buildAffiliateCode($user->name, $user->id);
                $user->save();
            }

            $user->assignRole('user');

            $freeMembershipType = MembershipType::query()->firstOrCreate(
                ['name' => 'free'],
                [
                    'affiliates_required' => 0,
                    'cost' => 0,
                    'profit' => 0,
                ]
            );

            Membership::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'membership_type_id' => $freeMembershipType->id,
                    'status' => 'free',
                    'started_at' => now(),
                    'expires_at' => null,
                    'last_payment_id' => null,
                ]
            );

            return $user;
        });

        $this->registrationWhatsappService->send($user);

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }

    private function resolveDefaultSponsor(): ?User
    {
        return User::query()->find(1)
            ?? User::role('admin')->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();
    }
}
