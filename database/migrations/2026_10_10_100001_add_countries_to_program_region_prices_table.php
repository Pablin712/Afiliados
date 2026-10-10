<?php

use App\Support\Countries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_region_prices', function (Blueprint $table) {
            // Country codes and group names ("US", "LATAM") the region charges
            // to. The buyer no longer picks a region: the country they connect
            // from decides it.
            $table->json('countries')->nullable()->after('region');
        });

        Schema::table('payments', function (Blueprint $table) {
            // Country the buyer was connecting from when they paid, which is
            // what picked the region in region_label.
            $table->char('country_code', 2)->nullable()->after('region_label');
        });

        // Regions created before this only had a name. Guess their countries
        // from it so they keep applying; a name that cannot be read ("Resto
        // del mundo") stays empty until the admin fills it in.
        foreach (DB::table('program_region_prices')->get(['id', 'region']) as $row) {
            $countries = $this->guessCountries((string) $row->region);

            if ($countries !== []) {
                DB::table('program_region_prices')
                    ->where('id', $row->id)
                    ->update(['countries' => json_encode($countries)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });

        Schema::table('program_region_prices', function (Blueprint $table) {
            $table->dropColumn('countries');
        });
    }

    /** @return list<string> */
    private function guessCountries(string $region): array
    {
        $key = preg_replace('/[^a-z]/', '', Str::lower(Str::ascii($region)));

        $aliases = [
            'US' => ['eeuu', 'usa', 'us', 'estadosunidos', 'unitedstates'],
            'LATAM' => ['latam', 'latinoamerica', 'americalatina', 'latinamerica', 'sudamerica', 'suramerica', 'hispanoamerica'],
            'EUROPA' => ['europa', 'europe', 'ue', 'eu', 'unioneuropea'],
            'NORTEAMERICA' => ['norteamerica', 'northamerica'],
        ];

        foreach ($aliases as $token => $names) {
            if (in_array($key, $names, true)) {
                return [$token];
            }
        }

        if (strlen($key) === 2 && Countries::isCode(strtoupper($key))) {
            return [strtoupper($key)];
        }

        // A single country by name: "Ecuador", "México", "España".
        foreach (Countries::all() as $code) {
            foreach (['es', 'en'] as $locale) {
                if (preg_replace('/[^a-z]/', '', Str::lower(Str::ascii(Countries::name($code, $locale)))) === $key) {
                    return [$code];
                }
            }
        }

        return [];
    }
};
