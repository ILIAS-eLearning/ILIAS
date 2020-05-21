<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/classes/class.ilMathBaseAdapter.php';

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
     * {@inheritdoc}
     */
    public function add($left_operand, $right_operand, $scale = null)
    {
        return bcadd($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function sub($left_operand, $right_operand, $scale = null)
    {
        return bcsub($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function mul($left_operand, $right_operand, $scale = null)
    {
        return bcmul($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function div($left_operand, $right_operand, $scale = null)
    {
        if ($right_operand == 0) {
            require_once 'Services/Math/exceptions/class.ilMathDivisionByZeroException.php';
            throw new ilMathDivisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
        }

        return bcdiv($this->normalize($left_operand), $this->normalize($right_operand), $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function mod($left_operand, $right_operand)
    {
        if ($right_operand == 0) {
            require_once 'Services/Math/exceptions/class.ilMathDivisionByZeroException.php';
            throw new ilMathDivisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
        }

        return bcmod($this->normalize($left_operand), $this->normalize($right_operand));
    }

    /**
     * {@inheritdoc}
     */
    public function pow($left_operand, $right_operand, $scale = null)
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

        return $this->applyScale(pow($left_operand, $right_operand), $scale);
    }

    /**
     * {@inheritdoc}
     */
    public function sqrt($operand, $scale = null)
    {
        return bcsqrt($operand, $scale);
    }

    /**
     * {@inheritdoc}
     */
    public function comp($left_operand, $right_operand, $scale = null)
    {
        return bccomp($left_operand, $right_operand, $scale);
    }
}
