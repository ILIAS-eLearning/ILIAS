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
	public static function _add($left_operand, $right_operand, $scale = NULL)
	{
		if (function_exists("bcadd"))
		{
			return bcadd($left_operand, $right_operand, $scale);
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
	public static function _comp($left_operand, $right_operand, $scale = NULL)
	{
		if (function_exists("bccomp"))
		{
			return bccomp($left_operand, $right_operand, $scale);
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
	public static function _div($left_operand, $right_operand, $scale = NULL)
	{
		if (function_exists("bcdiv"))
		{
			return bcdiv($left_operand, $right_operand, $scale);
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
			return bcmod($left_operand, $modulus);
		}
		else
		{
			return $left_operand % $right_operand;
		}
	}

	/*
	* Multiplicate two numbers
	*/
	public static function _mul($left_operand, $right_operand, $scale = NULL)
	{
		if (function_exists("bcmul"))
		{
			return bcmul($left_operand, $right_operand, $scale);
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
	public static function _pow($left_operand, $right_operand, $scale = NULL)
	{
		if (function_exists("bcpow"))
		{
			return bcpow($left_operand, $right_operand, $scale);
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
	public static function _sqrt($operand, $scale = NULL)
	{
		if (function_exists("bcsqrt"))
		{
			return bcsqrt($operand, $scale);
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
	public static function _sub($left_operand, $right_operand, $scale = NULL)
	{
		if (function_exists("bcsub"))
		{
			return bcsub($left_operand, $right_operand, $scale);
		}
		else
		{
			$res = $left_operand - $right_operand;
			if (is_numeric($scale)) $res = round($res, $scale);
			return $res;
		}
	}

}
?>
