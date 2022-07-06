<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

/**
 * Formula Question Variable
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestionVariable.php 465 2009-06-29 08:27:36Z hschottm $
 * @ingroup       ModulesTestQuestionPool
 * */
class assFormulaQuestionVariable
{
    private $variable;
    private $range_min;
    private $range_max;
    private $unit;
    private $value;
    private $precision;
    private $intprecision;
    private $range_min_txt;
    private $range_max_txt;


    /**
     * assFormulaQuestionVariable constructor
     * @param string  $variable     Variable name
     * @param float   $range_min    Range minimum
     * @param float   $range_max    Range maximum
     * @param object  $unit         Unit
     * @param integer $precision    Number of decimal places of the value
     * @param integer $intprecision Values with precision 0 must be divisible by this value
     * @access public
     */
    public function __construct($variable, $range_min, $range_max, $unit = null, $precision = 0, $intprecision = 1)
    {
        $this->variable = $variable;
        $this->setRangeMin($range_min);
        $this->setRangeMax($range_max);
        $this->unit = $unit;
        $this->value = null;
        $this->precision = $precision;
        $this->intprecision = $intprecision;
        $this->setRangeMinTxt($range_min);
        $this->setRangeMaxTxt($range_max);
    }

    public function getRandomValue()
    {
        if ($this->getPrecision() == 0) {
            if (!$this->isIntPrecisionValid(
                $this->getIntprecision(),
                $this->getRangeMin(),
                $this->getRangeMax()
            )) {
                global $DIC;
                $lng = $DIC['lng'];
                $DIC->ui()->mainTemplate()->setOnScreenMessage(
                    "failure",
                    $lng->txt('err_divider_too_big')
                );
            }
        }
        
        include_once "./Services/Math/classes/class.ilMath.php";
        $mul = ilMath::_pow(10, $this->getPrecision());
        $r1 = round(ilMath::_mul($this->getRangeMin(), $mul));
        $r2 = round(ilMath::_mul($this->getRangeMax(), $mul));
        $calcval = $this->getRangeMin() - 1;
        //test

        $roundedRangeMIN = round($this->getRangeMin(), $this->getPrecision());
        $roundedRangeMAX = round($this->getRangeMax(), $this->getPrecision());
        while ($calcval < $roundedRangeMIN || $calcval > $roundedRangeMAX) {
        
    
//		while($calcval < $this->getRangeMin() || $calcval > $this->getRangeMax())
            $rnd = mt_rand($r1, $r2);
            $calcval = ilMath::_div($rnd, $mul, $this->getPrecision());
            if (($this->getPrecision() == 0) && ($this->getIntprecision() != 0)) {
                if ($this->getIntprecision() > 0) {
                    $modulo = $calcval % $this->getIntprecision();
                    if ($modulo != 0) {
                        if ($modulo < ilMath::_div($this->getIntprecision(), 2)) {
                            $calcval = ilMath::_sub($calcval, $modulo, $this->getPrecision());
                        } else {
                            $calcval = ilMath::_add($calcval, ilMath::_sub($this->getIntprecision(), $modulo, $this->getPrecision()), $this->getPrecision());
                        }
                    }
                }
            }
        }
        return $calcval;
    }

    public function setRandomValue() : void
    {
        $this->setValue($this->getRandomValue());
    }
    
    public function isIntPrecisionValid($int_precision, $min_range, $max_range)
    {
        $min_abs = abs($min_range);
        $max_abs = abs($max_range);
        $bigger_abs = $max_abs > $min_abs ? $max_abs : $min_abs;
        if ($int_precision > $bigger_abs) {
            return false;
        }
        return true;
    }

    /************************************
     * Getter and Setter
     ************************************/

    public function setValue($value) : void
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
            include_once "./Services/Math/classes/class.ilMath.php";
            return ilMath::_mul($this->value, $this->getUnit()->getFactor());
        }
    }

    public function setPrecision($precision) : void
    {
        $this->precision = $precision;
    }

    public function getPrecision() : int
    {
        //@todo TEST
        
        return $this->precision;
    }

    public function setVariable($variable) : void
    {
        $this->variable = $variable;
    }

    public function getVariable() : string
    {
        return $this->variable;
    }

    public function setRangeMin($range_min) : void
    {
        include_once "./Services/Math/classes/class.EvalMath.php";
        $math = new EvalMath();
        $math->suppress_errors = true;
        $result = $math->evaluate($range_min);
        
        $this->range_min = $result;
    }

    public function getRangeMin() : float
    {
        return (double) $this->range_min;
    }

    public function setRangeMax($range_max) : void
    {
        include_once "./Services/Math/classes/class.EvalMath.php";
        $math = new EvalMath();
        $math->suppress_errors = true;
        $result = $math->evaluate($range_max);
        $this->range_max = $result;
    }

    public function getRangeMax() : float
    {
        return (double) $this->range_max;
    }

    public function setUnit($unit) : void
    {
        $this->unit = $unit;
    }

    public function getUnit() : ?object
    {
        return $this->unit;
    }

    public function setIntprecision($intprecision) : void
    {
        $this->intprecision = $intprecision;
    }

    public function getIntprecision() : int
    {
        return $this->intprecision;
    }

    public function setRangeMaxTxt($range_max_txt) : void
    {
        $this->range_max_txt = $range_max_txt;
    }

    public function getRangeMaxTxt()
    {
        return $this->range_max_txt;
    }

    public function setRangeMinTxt($range_min_txt) : void
    {
        $this->range_min_txt = $range_min_txt;
    }

    public function getRangeMinTxt()
    {
        return $this->range_min_txt;
    }
}
