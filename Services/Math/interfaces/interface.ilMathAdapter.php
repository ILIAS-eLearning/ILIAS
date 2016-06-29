<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMathAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMathAdapter
{
	/**
	 * Adds two numbers
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return mixed
	 */
	public function add($left_operand, $right_operand, $scale = 50);

	/**
	 * Subtracts two numbers
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return mixed
	 */
	public function sub($left_operand, $right_operand, $scale = 50);

	/**
	 * Multiplies two numbers
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return mixed
	 */
	public function mul($left_operand, $right_operand, $scale = 50);

	/**
	 * Divides two numbers
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return mixed
	 * @throws ilMathDevisionByZeroException
	 */
	public function div($left_operand, $right_operand, $scale = 50);

	/**
	 * Gets modulus of two numbers
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @return mixed
	 * @throws ilMathDevisionByZeroException
	 */
	public function mod($left_operand, $right_operand);

	/**
	 * Raises a number to another
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return mixed
	 */
	public function pow($left_operand, $right_operand, $scale = 50);

	/**
	 * Gets the square root of a number
	 * @param  mixed $operand
	 * @param int $scale
	 * @return mixed
	 */
	public function sqrt($operand, $scale = 50);


	/**
	 * Compares two numbers
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return mixed
	 */
	public function comp($left_operand, $right_operand, $scale = 50);

	/**
	 * Checks whether or not two numbers are identical
	 * @param  mixed $left_operand
	 * @param  mixed $right_operand
	 * @param int $scale
	 * @return bool
	 */
	public function equals($left_operand, $right_operand, $scale = 50);
}