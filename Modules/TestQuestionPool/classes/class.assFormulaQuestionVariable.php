<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	function __construct($variable, $range_min, $range_max, $unit = null, $precision = 0, $intprecision = 1)
	{
		$this->variable = $variable;
		$this->setRangeMin($range_min);
		$this->setRangeMax($range_max);
		$this->unit         = $unit;
		$this->value        = NULL;
		$this->precision    = $precision;
		$this->intprecision = $intprecision;
		$this->setRangeMinTxt($range_min);
		$this->setRangeMaxTxt($range_max);
	}

	public function getRandomValue()
	{
		
//		@todo check this
		
		include_once "./Services/Math/classes/class.ilMath.php";
		$mul     = ilMath::_pow(10, $this->getPrecision());
		$r1      = round(ilMath::_mul($this->getRangeMin(), $mul));
		$r2      = round(ilMath::_mul($this->getRangeMax(), $mul));
		$calcval = $this->getRangeMin() - 1;
//test		

		$roundedRangeMIN = round($this->getRangeMin(), $this->getPrecision());
		$roundedRangeMAX = round($this->getRangeMax(), $this->getPrecision());
		while($calcval < $roundedRangeMIN || $calcval > $roundedRangeMAX)
		
	
//		while($calcval < $this->getRangeMin() || $calcval > $this->getRangeMax())
		{
			$rnd     = mt_rand($r1, $r2);
			$calcval = ilMath::_div($rnd, $mul, $this->getPrecision());
			if(($this->getPrecision() == 0) && ($this->getIntprecision() != 0))
			{
				if($this->getIntprecision() > 0)
				{
					$modulo = $calcval % $this->getIntprecision();
					if($modulo != 0)
					{
						if($modulo < ilMath::_div($this->getIntprecision(), 2))
						{
							$calcval = ilMath::_sub($calcval, $modulo, $this->getPrecision());
						}
						else
						{
							$calcval = ilMath::_add($calcval, ilMath::_sub($this->getIntprecision(), $modulo, $this->getPrecision()), $this->getPrecision());
						}
					}
				}
			}
		}
		return $calcval;
	}

	public function setRandomValue()
	{
		$this->setValue($this->getRandomValue());
	}

	/************************************
	 * Getter and Setter
	 ************************************/

	function setValue($value)
	{
		$this->value = $value;
	}

	function getValue()
	{
		return $this->value;
	}

	function getBaseValue()
	{
		if(!is_object($this->getUnit()))
		{
			return $this->value;
		}
		else
		{
			include_once "./Services/Math/classes/class.ilMath.php";
			return ilMath::_mul($this->value, $this->getUnit()->getFactor());
		}
	}

	function setPrecision($precision)
	{
		$this->precision = $precision;
	}

	function getPrecision()
	{
	//@todo TEST  
		
		return (int)$this->precision;
	}

	function setVariable($variable)
	{
		$this->variable = $variable;
	}

	function getVariable()
	{
		return $this->variable;
	}

	function setRangeMin($range_min)
	{
		include_once "./Services/Math/classes/class.EvalMath.php";
		$math                  = new EvalMath();
		$math->suppress_errors = TRUE;
		$result                = $math->evaluate($range_min);
		
		$this->range_min       = $result;
	}

	function getRangeMin()
	{
		return (double)$this->range_min;
	}

	function setRangeMax($range_max)
	{
		include_once "./Services/Math/classes/class.EvalMath.php";
		$math                  = new EvalMath();
		$math->suppress_errors = TRUE;
		$result                = $math->evaluate($range_max);
		$this->range_max       = $result;
	}

	function getRangeMax()
	{
		return (double)$this->range_max;
	}

	function setUnit($unit)
	{
		$this->unit = $unit;
	}

	function getUnit()
	{
		return $this->unit;
	}

	function setIntprecision($intprecision)
	{
		$this->intprecision = $intprecision;
	}

	function getIntprecision()
	{
		return $this->intprecision;
	}

	public function setRangeMaxTxt($range_max_txt)
	{
		$this->range_max_txt = $range_max_txt;
	}

	public function getRangeMaxTxt()
	{
		return $this->range_max_txt;
	}

	public function setRangeMinTxt($range_min_txt)
	{
		$this->range_min_txt = $range_min_txt;
	}

	public function getRangeMinTxt()
	{
		return $this->range_min_txt;
	}

}

?>
