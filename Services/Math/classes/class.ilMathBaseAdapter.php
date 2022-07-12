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
 * Class ilMathBaseAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMathBaseAdapter implements ilMathAdapter
{
    /**
     * @inheritDoc
     */
    public function applyScale($left_operand, int $scale = null) : string
    {
        if (is_numeric($left_operand)) {
            $scale = (int) $scale;

            $left_operand = $this->exp2dec($left_operand);
            if (strpos($left_operand, '.') === false) {
                $number_of_decimals = 0;
            } else {
                $number_of_decimals = strlen(substr($left_operand, strpos($left_operand, '.') + 1));
            }

            if ($number_of_decimals > 0 && $number_of_decimals < $scale) {
                $left_operand = str_pad($left_operand, $scale - $number_of_decimals, '0');
            } elseif ($number_of_decimals > $scale) {
                $left_operand = substr($left_operand, 0, -($number_of_decimals - $scale));
            }
        }

        return $left_operand;
    }

    /**
     * @inheritDoc
     */
    public function round($value, int $precision = 0) : string
    {
        return number_format($value, $precision, '.', '');
    }

    /**
     * @inheritDoc
     */
    public function equals($left_operand, $right_operand, int $scale = null) : bool
    {
        return $this->comp($left_operand, $right_operand, $scale) === 0;
    }

    /**
     * This function fixes problems which occur when locale ist set to de_DE for example,
     * because bc* function expecting strings
     * @param mixed $number
     * @return string
     */
    protected function normalize($number) : ?string
    {
        if (null === $number) {
            return null;
        }

        $number = trim((string) $number);
        $number = $this->exp2dec($number);
        $locale_info = localeconv();

        if ($locale_info['decimal_point'] !== '.') {
            $append = '';
            $number_of_decimals = (int) ini_get('precision') - (int) floor(log10(abs($number)));
            if (0 > $number_of_decimals) {
                $number *= 10 ** $number_of_decimals;
                $append = str_repeat('0', -$number_of_decimals);
                $number_of_decimals = 0;
            }

            return number_format($number, $number_of_decimals, '.', '') . $append;
        }

        return $number;
    }

    /**
     * @param float|string|int $float_str
     * @return string
     */
    protected function exp2dec($float_str) : string
    {
        // make sure its a standard php float string (i.e. change 0.2e+2 to 20)
        // php will automatically format floats decimally if they are within a certain range
        $original = $float_str; // store original float, so we can return a float keeping the pecision when possible
        $float_str = (string) ((float) ($float_str));
        $float_str = str_replace(",", ".", $float_str); // convert ',' to '.' (float casting was locale sensitive)

        // if there is an E in the float string
        if (($pos = stripos($float_str, 'e')) !== false) {
            // get either side of the E, e.g. 1.6E+6 => exp E+6, num 1.6
            $exp = substr($float_str, $pos + 1);
            $num = substr($float_str, 0, $pos);

            // strip off num sign, if there is one, and leave it off if its + (not required)
            if ((($num_sign = $num[0]) === '+') || ($num_sign === '-')) {
                $num = substr($num, 1);
            } else {
                $num_sign = '';
            }
            if ($num_sign === '+') {
                $num_sign = '';
            }

            // strip off exponential sign ('+' or '-' as in 'E+6') if there is one, otherwise throw error, e.g. E+6 => '+'
            if ((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) {
                $exp = substr($exp, 1);
            } else {
                trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);
            }

            // get the number of decimal places to the right of the decimal point (or 0 if there is no dec point), e.g., 1.6 => 1
            $right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos + 1));
            // get the number of decimal places to the left of the decimal point (or the length of the entire num if there is no dec point), e.g. 1.6 => 1
            $left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));

            // work out number of zeros from exp, exp sign and dec places, e.g. exp 6, exp sign +, dec places 1 => num zeros 5
            if ($exp_sign === '+') {
                $num_zeros = $exp - $right_dec_places;
            } else {
                $num_zeros = $exp - $left_dec_places;
            }

            // build a string with $num_zeros zeros, e.g. '0' 5 times => '00000'
            $zeros = str_pad('', $num_zeros, '0');

            // strip decimal from num, e.g. 1.6 => 16
            if ($dec_pos !== false) {
                $num = str_replace('.', '', $num);
            }

            // if positive exponent, return like 1600000
            if ($exp_sign === '+') {
                return $num_sign . $num . $zeros;
            }

            // if negative exponent, return like 0.0000016
            return $num_sign . '0.' . $zeros . $num;
        }

        // otherwise, assume already in decimal notation and return
        return $original;
    }
}
