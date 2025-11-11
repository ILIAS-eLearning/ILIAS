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

declare(strict_types=1);

/**
 * Formula Question Variable
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestionVariable.php 465 2009-06-29 08:27:36Z hschottm $
 * @ingroup       ModulesTestQuestionPool
 * */
class assFormulaQuestionVariable
{
    private $value = null;
    private float $range_min;
    private float $range_max;

    public function __construct(
        private string $variable,
        private string $range_min_txt,
        private string $range_max_txt,
        private ?assFormulaQuestionUnit $unit = null,
        private int $precision = 0,
        private int $intprecision = 1
    ) {
        $this->setRangeMin($range_min_txt);
        $this->setRangeMax($range_max_txt);
    }

    public function getRandomValue(): float
    {
        if (
            $this->getPrecision() === 0
            && !$this->isIntPrecisionValid(
                $this->getIntprecision(),
                $this->getRangeMin(),
                $this->getRangeMax()
            )
        ) {
            global $DIC;
            $DIC['tpl']->setOnScreenMessage('failure', $DIC['lng']->txt('err_divider_too_big'));
            return 0.0;
        }

        $mul = ilMath::_pow(10, $this->getPrecision());
        $r1 = round((float) ilMath::_mul($this->getRangeMin(), $mul));
        $r2 = round((float) ilMath::_mul($this->getRangeMax(), $mul));
        $calc_val = $this->getRangeMin() - 1.0;

        $rounded_range_min = round($this->getRangeMin(), $this->getPrecision());
        $rounded_range_max = round($this->getRangeMax(), $this->getPrecision());
        while ($calc_val < $rounded_range_min || $calc_val > $rounded_range_max) {
            $rnd = random_int((int) $r1, (int) $r2);
            $calc_val = (float) ilMath::_div($rnd, $mul, $this->getPrecision());

            if ($this->getPrecision() === 0 && $this->getIntprecision() > 0) {
                $modulo = $calc_val % $this->getIntprecision();
                if ($modulo !== 0) {
                    $calc_val = $modulo < ilMath::_div($this->getIntprecision(), 2)
                        ? (float) ilMath::_sub($calc_val, $modulo, $this->getPrecision())
                        : (float) ilMath::_add($calc_val, ilMath::_sub($this->getIntprecision(), $modulo, $this->getPrecision()), $this->getPrecision());
                }
            }
        }

        return $calc_val;
    }

    public function setRandomValue(): void
    {
        $this->setValue($this->getRandomValue());
    }

    public function isIntPrecisionValid(?int $int_precision, float $min_range, float $max_range): bool
    {
        return $int_precision !== null && $int_precision <= max(abs($max_range), abs($min_range));
    }

    /************************************
     * Getter and Setter
     ************************************/

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getBaseValue()
    {
        if (!is_object($this->getUnit())) {
            return $this->value;
        } else {
            return ilMath::_mul($this->value, $this->getUnit()->getFactor());
        }
    }

    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function setVariable($variable): void
    {
        $this->variable = $variable;
    }

    public function getVariable(): string
    {
        return $this->variable;
    }

    public function setRangeMin(string $range_min): void
    {
        $math = new EvalMath();
        $math->suppress_errors = true;
        $this->range_min = (float) $math->evaluate($range_min);
    }

    public function getRangeMin(): float
    {
        return $this->range_min;
    }

    public function setRangeMax(string $range_max): void
    {
        $math = new EvalMath();
        $math->suppress_errors = true;
        $this->range_max = (float) $math->evaluate($range_max);
    }

    public function getRangeMax(): float
    {
        return $this->range_max;
    }

    public function setUnit(?assFormulaQuestionUnit $unit): void
    {
        $this->unit = $unit;
    }

    public function getUnit(): ?assFormulaQuestionUnit
    {
        return $this->unit;
    }

    public function setIntprecision($intprecision): void
    {
        $this->intprecision = $intprecision;
    }

    public function getIntprecision(): int
    {
        return $this->intprecision;
    }

    public function setRangeMaxTxt(string $range_max_txt): void
    {
        $this->range_max_txt = $range_max_txt;
    }

    public function getRangeMaxTxt(): string
    {
        return $this->range_max_txt;
    }

    public function setRangeMinTxt(string $range_min_txt): void
    {
        $this->range_min_txt = $range_min_txt;
    }

    public function getRangeMinTxt(): string
    {
        return $this->range_min_txt;
    }
}
