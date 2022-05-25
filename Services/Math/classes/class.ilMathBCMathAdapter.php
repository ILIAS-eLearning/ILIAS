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
 * Class ilMathBCMathAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMathBCMathAdapter extends ilMathBaseAdapter
{
    /**
     * ilMathBcMathAdapter constructor.
     * @param int $scale
     */
    public function __construct($scale = 0)
    {
        bcscale($scale);
    }

    /**
     * @inheritDoc
     */
    public function add($left_operand, $right_operand, int $scale = null)
    {
        return bcadd($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * @inheritDoc
     */
    public function sub($left_operand, $right_operand, int $scale = null)
    {
        return bcsub($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * @inheritDoc
     */
    public function mul($left_operand, $right_operand, int $scale = null)
    {
        return bcmul($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * @inheritDoc
     */
    public function div($left_operand, $right_operand, int $scale = null)
    {
        if ($right_operand == 0) {
            throw new ilMathDivisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
        }

        return bcdiv($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * @inheritDoc
     */
    public function mod($left_operand, $right_operand)
    {
        if ($right_operand == 0) {
            throw new ilMathDivisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
        }

        return bcmod($this->normalize($left_operand), $this->normalize($right_operand));
    }

    /**
     * @inheritDoc
     */
    public function pow($left_operand, $right_operand, int $scale = null)
    {
        $left_operand = $this->normalize($left_operand);
        $right_operand = $this->normalize($right_operand);
        $scale = $this->normalize($scale);

        // bcpow() only supports exponents less than or equal to 2^31-1.
        // Also, bcpow() does not support decimal numbers.
        // If you have scale set to 0, then the exponent is converted to an integer; otherwise an error is generated.
        $left_operand_dec = $this->exp2dec($left_operand);
        $right_operand_dec = $this->exp2dec($right_operand);

        // bcpow does NOT support decimal exponents
        if (strpos($right_operand_dec, '.') === false) {
            return bcpow($left_operand_dec, $right_operand_dec, $scale);
        }

        return $this->applyScale($left_operand ** $right_operand, $scale);
    }

    /**
     * @inheritDoc
     */
    public function sqrt($operand, int $scale = null)
    {
        return bcsqrt($operand, $scale);
    }

    /**
     * @inheritDoc
     */
    public function comp($left_operand, $right_operand, int $scale = null) : int
    {
        return bccomp($left_operand, $right_operand, $scale);
    }
}
