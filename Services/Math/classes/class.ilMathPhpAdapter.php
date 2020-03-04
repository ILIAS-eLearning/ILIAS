<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/classes/class.ilMathBaseAdapter.php';

/**
 * Class ilMathPhpAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMathPhpAdapter extends ilMathBaseAdapter
{
    /**
     * {@inheritdoc}
     */
    public function add($left_operand, $right_operand, $scale = null)
    {
        $res = $this->normalize($left_operand) + $this->normalize($right_operand);

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function sub($left_operand, $right_operand, $scale = null)
    {
        $res = $this->normalize($left_operand) - $this->normalize($right_operand);

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function mul($left_operand, $right_operand, $scale = null)
    {
        $res = $this->normalize($left_operand) * $this->normalize($right_operand);

        return $this->applyScale($res, $this->normalize($scale));
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

        // This ensures the old PHP <= 7.0.x behaviour, see: #27785 / #26361
        try {
            $res = $this->normalize($left_operand) / $this->normalize($right_operand);

            $division = $this->applyScale($res, $this->normalize($scale));
        } catch (Throwable $e) {
            if  (strpos($e->getMessage(), 'A non-numeric value encountered') !== false) {
                $division = 0;
            } else {
                throw $e;
            }
        }

        return $division;
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

        $res = $this->normalize($left_operand) % $this->normalize($right_operand);

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function pow($left_operand, $right_operand, $scale = null)
    {
        $res = pow($this->normalize($left_operand), $this->normalize($right_operand));

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function sqrt($operand, $scale = null)
    {
        $res = sqrt($this->normalize($operand));

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function comp($left_operand, $right_operand, $scale = null)
    {
        $left_operand  = $this->normalize($left_operand);
        $right_operand = $this->normalize($right_operand);
        $scale         = $this->normalize($scale);

        if (is_numeric($scale)) {
            $left_operand  = $this->applyScale($left_operand, $scale);
            $right_operand = $this->applyScale($right_operand, $scale);
        }

        if ($left_operand == $right_operand) {
            return 0;
        } elseif ($left_operand > $right_operand) {
            return 1;
        } else {
            return -1;
        }
    }
}
