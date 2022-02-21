<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilMathPhpAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMathPhpAdapter extends ilMathBaseAdapter
{
    /**
     * @param string|null $operand
     * @return float|int|string|null
     */
    private function transformToNumeric(?string $operand)
    {
        if (is_string($operand)) {
            if (strpos($operand, '.') !== false) {
                $operand = (float) $operand;
            } else {
                $operand = (int) $operand;
            }
        }

        return $operand;
    }

    /**
     * {@inheritdoc}
     */
    public function add($left_operand, $right_operand, int $scale = null)
    {
        $res = $this->normalize($left_operand) + $this->normalize($right_operand);

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function sub($left_operand, $right_operand, int $scale = null)
    {
        $res = $this->normalize($left_operand) - $this->normalize($right_operand);

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function mul($left_operand, $right_operand, int $scale = null)
    {
        try {
            $left_operand = $this->normalize($left_operand);
            $right_operand = $this->normalize($right_operand);

            $left_operand = $this->transformToNumeric($left_operand);
            $right_operand = $this->transformToNumeric($right_operand);

            $res = $left_operand * $right_operand;

            $multiplication = $this->applyScale($res, $this->normalize($scale));
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), 'A non-numeric value encountered') !== false) {
                $multiplication = 0;
            } else {
                throw $e;
            }
        }

        return $multiplication;
    }

    /**
     * {@inheritdoc}
     */
    public function div($left_operand, $right_operand, int $scale = null)
    {
        if ($right_operand == 0) {
            throw new ilMathDivisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
        }

        // This ensures the old PHP <= 7.0.x behaviour, see: #27785 / #26361
        try {
            $left_operand = $this->normalize($left_operand);
            $right_operand = $this->normalize($right_operand);

            $left_operand = $this->transformToNumeric($left_operand);
            $right_operand = $this->transformToNumeric($right_operand);

            $res = $left_operand / $right_operand;

            $division = $this->applyScale($res, $this->normalize($scale));
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), 'A non-numeric value encountered') !== false) {
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
    public function mod($left_operand, $right_operand) : int
    {
        if ($right_operand == 0) {
            throw new ilMathDivisionByZeroException(sprintf("Division of %s by %s not possible!", $left_operand, $right_operand));
        }

        $res = $this->normalize($left_operand) % $this->normalize($right_operand);

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function pow($left_operand, $right_operand, int $scale = null)
    {
        $res = pow($this->normalize($left_operand), $this->normalize($right_operand));

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function sqrt($operand, int $scale = null)
    {
        $res = sqrt($this->normalize($operand));

        return $this->applyScale($res, $this->normalize($scale));
    }

    /**
     * {@inheritdoc}
     */
    public function comp($left_operand, $right_operand, int $scale = null)
    {
        $left_operand = $this->normalize($left_operand);
        $right_operand = $this->normalize($right_operand);
        $scale = $this->normalize($scale);

        if (is_numeric($scale)) {
            $left_operand = $this->applyScale($left_operand, $scale);
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
