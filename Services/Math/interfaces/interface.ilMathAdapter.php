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
 * Interface ilMathAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMathAdapter
{
    /**
     * Adds two numbers
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return mixed
     */
    public function add($left_operand, $right_operand, int $scale = null);

    /**
     * Subtracts two numbers
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return mixed
     */
    public function sub($left_operand, $right_operand, int $scale = null);

    /**
     * Multiplies two numbers
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return mixed
     */
    public function mul($left_operand, $right_operand, int $scale = null);

    /**
     * Divides two numbers
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return mixed
     * @throws ilMathDivisionByZeroException
     */
    public function div($left_operand, $right_operand, int $scale = null);

    /**
     * Gets modulus of two numbers
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @return mixed
     * @throws ilMathDivisionByZeroException
     */
    public function mod($left_operand, $right_operand);

    /**
     * Raises a number to another
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return mixed
     */
    public function pow($left_operand, $right_operand, int $scale = null);

    /**
     * Gets the square root of a number
     * @param  mixed $operand
     * @param int|null $scale
     * @return mixed
     */
    public function sqrt($operand, int $scale = null);


    /**
     * Compares two numbers
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return mixed
     */
    public function comp($left_operand, $right_operand, int $scale = null);

    /**
     * Checks whether or not two numbers are identical
     * @param mixed $left_operand
     * @param mixed $right_operand
     * @param int|null $scale
     * @return bool
     */
    public function equals($left_operand, $right_operand, int $scale = null): bool;

    /**
     * This method adapts the behaviour of bcscale()
     * @param mixed $left_operand
     * @param int|null $scale
     * @return mixed
     */
    public function applyScale($left_operand, int $scale = null);

    /**
     * @param mixed $value
     */
    public function round($value, int $precision = 0): string;
}
