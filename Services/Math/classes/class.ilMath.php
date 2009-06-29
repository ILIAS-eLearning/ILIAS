<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilMath
*
* Wrapper for mathematical operations
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* $Id$
*/
class ilMath
{
	/*
	* Add two numbers
	*/
	public static function _add($left_operand, $right_operand, $scale = 50)
	{
		if (function_exists("bcadd"))
		{
			return bcadd(ilMath::exp2dec($left_operand), ilMath::exp2dec($right_operand), $scale);
		}
		else
		{
			$res = $left_operand + $right_operand;
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

	/*
	* Compare two numbers
	*/
	public static function _comp($left_operand, $right_operand, $scale = 50)
	{
		if (function_exists("bccomp"))
		{
			return bccomp(ilMath::exp2dec($left_operand), ilMath::exp2dec($right_operand), $scale);
		}
		else
		{
			if (is_numeric($scale)) 
			{
				$left_operand = round($left_operand, $scale);
				$right_operand = round($right_operand, $scale);
			}
			if ($left_operand == $right_operand) return 0;
			if ($left_operand > $right_operand) return 1;
			return -1;
		}
	}

	/*
	* Divide two numbers
	*/
	public static function _div($left_operand, $right_operand, $scale = 50)
	{
		if (function_exists("bcdiv"))
		{
			return bcdiv(ilMath::exp2dec($left_operand), ilMath::exp2dec($right_operand), $scale);
		}
		else
		{
			if ($right_operand == 0) return NULL;
			$res = $left_operand / $right_operand;
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

	/*
	* Get modulus of two numbers
	*/
	public static function _mod($left_operand, $modulus)
	{
		if (function_exists("bcmod"))
		{
			return bcmod(ilMath::exp2dec($left_operand), $modulus);
		}
		else
		{
			return $left_operand % $right_operand;
		}
	}

	/*
	* Multiplicate two numbers
	*/
	public static function _mul($left_operand, $right_operand, $scale = 50)
	{
		if (function_exists("bcmul"))
		{
			return bcmul(ilMath::exp2dec($left_operand), ilMath::exp2dec($right_operand), $scale);
		}
		else
		{
			$res = $left_operand * $right_operand;
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

	/*
	* Raise a number to another
	*/
	public static function _pow($left_operand, $right_operand, $scale = 50)
	{
		if (function_exists("bcpow"))
		{
			return bcpow(ilMath::exp2dec($left_operand), ilMath::exp2dec($right_operand), $scale);
		}
		else
		{
			$res = pow($left_operand, $right_operand);
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

	/*
	* Get the square root of a number
	*/
	public static function _sqrt($operand, $scale = 50)
	{
		if (function_exists("bcsqrt"))
		{
			return bcsqrt(ilMath::exp2dec($operand), $scale);
		}
		else
		{
			$res = sqrt($operand);
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

	/*
	* Subtract two numbers
	*/
	public static function _sub($left_operand, $right_operand, $scale = 50)
	{
		if (function_exists("bcsub"))
		{
			return bcsub(ilMath::exp2dec($left_operand), ilMath::exp2dec($right_operand), $scale);
		}
		else
		{
			$res = $left_operand - $right_operand;
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

	/*
	* Converts numbers in the form "1.5e4" into decimal notation
	* Only available for bcmath
	*/
	function exp2dec($float_str)
	{
		// make sure its a standard php float string (i.e. change 0.2e+2 to 20)
		// php will automatically format floats decimally if they are within a certain range
		$float_str = (string)((float)($float_str));

		// if there is an E in the float string
		if(($pos = strpos(strtolower($float_str), 'e')) !== false)
		{
			// get either side of the E, e.g. 1.6E+6 => exp E+6, num 1.6
			$exp = substr($float_str, $pos+1);
			$num = substr($float_str, 0, $pos);

			// strip off num sign, if there is one, and leave it off if its + (not required)
			if((($num_sign = $num[0]) === '+') || ($num_sign === '-')) $num = substr($num, 1);
			else $num_sign = '';
			if($num_sign === '+') $num_sign = '';

			// strip off exponential sign ('+' or '-' as in 'E+6') if there is one, otherwise throw error, e.g. E+6 => '+'
			if((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) $exp = substr($exp, 1);
			else trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);

			// get the number of decimal places to the right of the decimal point (or 0 if there is no dec point), e.g., 1.6 => 1
			$right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos+1));
			// get the number of decimal places to the left of the decimal point (or the length of the entire num if there is no dec point), e.g. 1.6 => 1
			$left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));

			// work out number of zeros from exp, exp sign and dec places, e.g. exp 6, exp sign +, dec places 1 => num zeros 5
			if($exp_sign === '+') $num_zeros = $exp - $right_dec_places;
			else $num_zeros = $exp - $left_dec_places;

			// build a string with $num_zeros zeros, e.g. '0' 5 times => '00000'
			$zeros = str_pad('', $num_zeros, '0');

			// strip decimal from num, e.g. 1.6 => 16
			if($dec_pos !== false) $num = str_replace('.', '', $num);

			// if positive exponent, return like 1600000
			if($exp_sign === '+') return $num_sign.$num.$zeros;
			// if negative exponent, return like 0.0000016
			else return $num_sign.'0.'.$zeros.$num;
		}
		// otherwise, assume already in decimal notation and return
		else return $float_str;
	}
}
?>
