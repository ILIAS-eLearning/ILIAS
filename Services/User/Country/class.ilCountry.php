<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Country utility class
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilCountry
{
    /**
     * Get country codes (DIN EN 3166-1)
     * @return string[] array of country codes
     */
    public static function getCountryCodes(): array
    {
        $cntcodes = array(
            "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AN",
            "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX",
            "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH",
            "BI", "BJ", "BL", "BM", "BN", "BO", "BR", "BS",
            "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CF",
            "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO",
            "CR", "CU", "CV", "CX", "CY", "CZ", "DE", "DJ",
            "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH",
            "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO",
            "FR", "FX", "GA", "GB", "GD", "GE", "GF", "GG",
            "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR",
            "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN",
            "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN",
            "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO",
            "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP",
            "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI",
            "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA",
            "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML",
            "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT",
            "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC",
            "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR",
            "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH",
            "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW",
            "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA",
            "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ",
            "SK", "SL", "SM", "SN", "SO", "SR", "ST", "SV",
            "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ",
            "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV",
            "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ",
            "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF",
            "WS", "YE", "YT", "ZA", "ZM", "ZW", "XK");
        return $cntcodes;
    }
}
