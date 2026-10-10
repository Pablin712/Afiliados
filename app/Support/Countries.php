<?php

namespace App\Support;

use Locale;

/**
 * Country codes a plan's region can list, and the named groups that save the
 * admin from typing twenty codes for "LATAM".
 */
class Countries
{
    /** ISO 3166-1 alpha-2, plus XK (Kosovo), which geolocation services use. */
    private const CODES = 'AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR '
        .'BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO DZ EC EE EG EH ER '
        .'ES ET FI FJ FK FM FO FR GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY HK HM HN HR HT HU ID IE IL '
        .'IM IN IO IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY MA MC MD '
        .'ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL NO NP NR NU NZ OM PA PE PF '
        .'PG PH PK PL PM PN PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX '
        .'SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN VU WF WS XK YE YT '
        .'ZA ZM ZW';

    /**
     * Group names are three letters or more so they can never be mistaken for
     * a country code ("NA" is Namibia, not North America).
     */
    private const GROUPS = [
        'LATAM' => 'AR BO BR CL CO CR CU DO EC GT HN MX NI PA PE PR PY SV UY VE',
        'NORTEAMERICA' => 'US CA',
        'EUROPA' => 'AD AL AT BA BE BG CH CY CZ DE DK EE ES FI FR GB GR HR HU IE IS IT LI LT LU LV MC MD ME MK MT NL '
            .'NO PL PT RO RS SE SI SK SM UA VA XK',
    ];

    public static function isCode(string $code): bool
    {
        return in_array($code, self::all(), true);
    }

    public static function isGroup(string $name): bool
    {
        return array_key_exists($name, self::GROUPS);
    }

    /** @return list<string> */
    public static function groupNames(): array
    {
        return array_keys(self::GROUPS);
    }

    /**
     * Splits what the admin typed ("us, ca latam") into upper-case tokens,
     * without checking them.
     *
     * @return list<string>
     */
    public static function parse(string|array|null $input): array
    {
        if (is_array($input)) {
            $input = implode(',', array_filter($input, 'is_string'));
        }

        $tokens = preg_split('/[\s,;]+/', mb_strtoupper(trim((string) $input)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique($tokens));
    }

    /**
     * Country codes covered by a list of codes and group names.
     *
     * @param  list<string>  $tokens
     * @return list<string>
     */
    public static function expand(array $tokens): array
    {
        $codes = [];

        foreach ($tokens as $token) {
            if (self::isGroup($token)) {
                array_push($codes, ...explode(' ', self::GROUPS[$token]));
            } elseif (self::isCode($token)) {
                $codes[] = $token;
            }
        }

        return array_values(array_unique($codes));
    }

    /** "Estados Unidos" for "US" in Spanish, or the code itself without intl. */
    public static function name(string $code, ?string $locale = null): string
    {
        if (! class_exists(Locale::class)) {
            return $code;
        }

        $name = Locale::getDisplayRegion('-'.$code, $locale ?? app()->getLocale());

        return $name === '' ? $code : $name;
    }

    /** @return list<string> */
    public static function all(): array
    {
        static $codes = null;

        return $codes ??= explode(' ', self::CODES);
    }
}
