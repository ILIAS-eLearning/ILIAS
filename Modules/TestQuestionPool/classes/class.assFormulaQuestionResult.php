<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Formula Question Result
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestionResult.php 944 2009-11-09 16:11:30Z hschottm $
 * @ingroup       ModulesTestQuestionPool
 * */
class assFormulaQuestionResult
{
	const RESULT_NO_SELECTION = 0;
	const RESULT_DEC          = 1;
	const RESULT_FRAC         = 2;
	const RESULT_CO_FRAC      = 3;

	private $result;
	private $range_min;
	private $range_max;
	private $tolerance;
	private $unit;
	private $formula;
	private $rating_simple;
	private $rating_sign;
	private $rating_value;
	private $rating_unit;
	private $points;
	private $precision;
	private $result_type;
	private $range_min_txt;
	private $range_max_txt;
	
	private $available_units = array();

	/**
	 * assFormulaQuestionResult constructor
	 * @param string  $result        Result name
	 * @param double  $range_min     Range minimum
	 * @param double  $range_max     Range maximum
	 * @param double  $tolerance     Tolerance of the result in percent
	 * @param object  $unit          Unit
	 * @param string  $formula       The formula to calculate the result
	 * @param double  $points        The maximum available points for the result
	 * @param integer $precision     Number of decimal places of the value
	 * @param boolean $rating_simple Use simple rating (100% if right, 0 % if wrong)
	 * @param double  $rating_sign   Percentage of rating for the correct sign
	 * @param double  $rating_value  Percentage of rating for the correct value
	 * @param double  $rating_unit   Percentage of rating for the correct unit
	 * @access public
	 */
	function __construct($result, $range_min, $range_max, $tolerance, $unit, $formula, $points, $precision, $rating_simple = TRUE, $rating_sign = 33, $rating_value = 34, $rating_unit = 33, $result_type = 0)
	{
		$this->result = $result;
		#	$this->setRangeMin((is_numeric($range_min)) ? $range_min : NULL);
		#	$this->setRangeMax((is_numeric($range_max)) ? $range_max : NULL);
		$this->setRangeMin($range_min);
		$this->setRangeMax($range_max);


		$this->tolerance     = $tolerance;
		$this->unit          = $unit;
		$this->formula       = $formula;
		$this->points        = $points;
		$this->precision     = $precision;
		$this->rating_simple = $rating_simple;
		$this->rating_sign   = $rating_sign;
		$this->rating_value  = $rating_value;
		$this->rating_unit   = $rating_unit;
		$this->result_type   = $result_type;
		$this->setRangeMinTxt($range_min);
		$this->setRangeMaxTxt($range_max);
	}

	public function substituteFormula($variables, $results)
	{
		global $lng;

		$formula = $this->getFormula();

		if(preg_match_all("/(\\\$r\\d+)/ims", $formula, $matches))
		{
			foreach($matches[1] as $result)
			{
				if(strcmp($result, $this->getResult()) == 0)
				{
					ilUtil::sendFailure($lng->txt("errRecursionInResult"));
					return false;
				}

				if(is_object($results[$result]))
				{
					$formula = str_replace($result, $results[$result]->substituteFormula($variables, $results), $formula);	
				}
				else
				{	
					ilUtil::sendFailure($lng->txt("errFormulaQuestion"));
					return false;
				}
			}
		}

		return "(".$formula.")";
	}

	public function calculateFormula($variables, $results, $question_id = 0, $use_precision = true)
	{

		$resultunits = array();
		if($question_id > 0)
		{
			$resultunits = $this->getAvailableResultUnits($question_id);		
		}
	
		include_once "./Services/Math/classes/class.ilMath.php";
		include_once "./Services/Math/classes/class.EvalMath.php";
		$formula = $this->substituteFormula($variables, $results);
		if(preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches))
		{
			foreach($matches[1] as $variable)
			{
				$varObj  = $variables[$variable];
				if(!is_object($varObj))
				{
					continue;
				}
				$value   = $varObj->getBaseValue();
				$formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(".$value.")" . "\\1", $formula);
			}
		}
		$math                  = new EvalMath();
		$math->suppress_errors = TRUE;

		$formula = str_replace(",", ".", $formula);
		$result                = $math->evaluate($formula);
		if(is_object($this->getUnit()))
		{
			$result = ilMath::_div($result, $this->getUnit()->getFactor(), 100);
		}

		// @todo DON'T USE ilMath::_mul() ... bcmul() returns wrong result !!!!
	
		if($use_precision == true)
		{
			$res = $result * 1;
			if (is_numeric($this->getPrecision()))
			{
				if( $this->getResultType()==RESULT_CO_DEC || $this->getResultType()==RESULT_NO_SELECTION )
				{
					$result = round($res, $this->getPrecision());
				}			
			}
		}
		return $result;
	}

	public function findValidRandomVariables($variables, $results)
	{
		include_once "./Services/Math/classes/class.EvalMath.php";
		$i       = 0;
		$inRange = FALSE;
		while($i < 1000 && !$inRange)
		{
			$formula = $this->substituteFormula($variables, $results);
			if(preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches))
			{
				foreach($matches[1] as $variable)
				{
					$varObj = $variables[$variable];
					if(!is_object($varObj))
					{
						continue;
					}
					$varObj->setRandomValue();
					$formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(".$varObj->getBaseValue().")" . "\\1", $formula);
				}
			}
			$math                  = new EvalMath();
			$math->suppress_errors = TRUE;
			$result                = $math->evaluate($formula);
			$inRange               = (is_numeric($result)) ? TRUE : FALSE;
			if($inRange)
			{
				if(is_numeric($this->getRangeMin()))
				{
					if($result < $this->getRangeMinBase())
					{
						$inRange = FALSE;
					}
				}
				if(is_numeric($this->getRangeMax()))
				{
					if($result > $this->getRangeMaxBase())
					{
						$inRange = FALSE;
					}
				}
			}
			$i++;
		}
	}

	public function suggestRange($variables, $results)
	{
		
//		@todo Check this 
		include_once "./Services/Math/classes/class.EvalMath.php";
		$range_min = NULL;
		$range_max = NULL;
		for($i = 0; $i < 1000; $i++)
		{
			$formula = $this->substituteFormula($variables, $results);
			if(preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches))
			{
				foreach($matches[1] as $variable)
				{
					$varObj = $variables[$variable];
					if(!is_object($varObj))
					{
						continue;
					}
					$varObj->setRandomValue();
					$formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(".$varObj->getBaseValue().")" . "\\1", $formula);
				}
			}
			$math                  = new EvalMath();
			$math->suppress_errors = TRUE;
			$result                = $math->evaluate($formula);
			if(($range_min == NULL) || ($result < $range_min)) $range_min = $result;
			if(($range_max == NULL) || ($result > $range_max)) $range_max = $result;
		}
		include_once "./Services/Math/classes/class.ilMath.php";
		if(is_object($this->getUnit()))
		{
			$range_min = ilMath::_div($range_min, $this->getUnit()->getFactor());
			$range_max = ilMath::_div($range_max, $this->getUnit()->getFactor());
		}
		$this->setRangeMin(ilMath::_mul($range_min, 1, $this->getPrecision()));
		$this->setRangeMax(ilMath::_mul($range_max, 1, $this->getPrecision()));
	}

	/**
	 * @param      $variables      formula variables containing units
	 * @param      $results        formula results containing units
	 * @param      $value          user input value
	 * @param null $unit           user input unit
	 * @return bool
	 */
	public function isCorrect($variables, $results, $value, $unit = NULL)
	{
		// The user did not answer the question ....  
		if($value == NULL)
		{
			return false;
		}
		$value=str_replace(' ', '',$value);
		
		include_once "./Services/Math/classes/class.EvalMath.php";
		include_once "./Services/Math/classes/class.ilMath.php";
		$formula = $this->substituteFormula($variables, $results);

		$check_valid_chars = true;
		
		if(preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches))
		{
			foreach($matches[1] as $variable)
			{
				$varObj = $variables[$variable];
				if(!is_object($varObj))
				{
					continue;
				}

				if($varObj->getUnit() != NULL)
				{
					//convert unit and value to baseunit.... because vars could have different units
					if($varObj->getUnit()->getBaseUnit() != -1) #$this->getUnit() != NULL)
					{
						$tmp_value = $varObj->getValue() * $varObj->getUnit()->getFactor();
					}
					else
					{
						$tmp_value = $varObj->getValue();
					}
				}
				else
				{
					$tmp_value = $varObj->getValue();
				}

				$formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(".$tmp_value.")" . "\\1", $formula);
			}
		}

		$math                  = new EvalMath();
		$math->suppress_errors = false;
		$result                = $math->evaluate($formula); // baseunit-result!!

		$result = round($result, $this->getPrecision());
		
		//	check for valid chars ("0-9",",|.|/","0-9","e|E","+|-","0-9")
		$has_valid_chars = preg_match("/^-?([0-9]*)(,|\\.|\\/){0,1}([0-9]*)([eE][\\+|-]([0-9])+)?$/", $value, $matches);
		if(!$has_valid_chars)
		{
			$check_valid_chars = false;
		}
		else if($matches[2] == '/' && strtolower($matches[4]) == "e" && (!strlen($matches[1]) || !strlen($matches[3]) || $matches[3] == 0))
		{
			$check_valid_chars = false;
		}
// result_type extension
		switch($this->getResultType())
		{
			case assFormulaQuestionResult::RESULT_DEC:
				if(substr_count($value, '.') == 1 || substr_count($value, ',') == 1)
				{
					$exp_val    = $value;
					$frac_value = str_replace(',', '.', $exp_val);
				}
				else
				{
					$frac_value = $value;
				}
				
				$frac_value =  round($frac_value, $this->getPrecision());

				if(substr_count($value, '/') >= 1)
				{
					$check_fraction = FALSE;
				}
				else
				{
					$check_fraction = TRUE;	
				}	
				break;
			
			case assFormulaQuestionResult::RESULT_FRAC: 
			case assFormulaQuestionResult::RESULT_CO_FRAC: 
				$exp_val = explode('/', $value);
				if(count($exp_val) == 1)
				{	
					// @todo: Use ilMath::_div?
					$frac_value = ($exp_val[0] / 1);
					if($frac_value == $result)
					{
						$check_fraction = TRUE;
					}
					else
					{
						$check_fraction = FALSE;
					}
				}
				else
				{
					// @todo: Use ilMath::_div?
					$frac_value = $exp_val[0] / $exp_val[1];
					$frac_value = round($frac_value, $this->getPrecision());
					$frac_value = str_replace(',', '.', $frac_value);

					if($frac_value == $result)
					{
						$check_fraction = TRUE;
					}
					
					if($this->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC)
					{
						if(!self::isCoprimeFraction($exp_val[0], $exp_val[1]))
						{
							$check_fraction = FALSE;
						}
					}
				}
				
				if(substr_count($value, '.') >= 1 || substr_count($value, ',') >= 1)
				{
					$check_fraction = FALSE;
				}
				break;
			
			case assFormulaQuestionResult::RESULT_NO_SELECTION:
			default:
				if(substr_count($value, '.') == 1 || substr_count($value, ',') == 1)
				{
					$exp_val    = $value;
					$frac_value = str_replace(',', '.', $exp_val);
				}
				else
				{
					$frac_value = $value;
				}
				$frac_value = round($frac_value, $this->getPrecision());
				$check_fraction = TRUE;
			break;
		}

		// result unit!!
		if(is_object($this->getUnit()))
		{
			//there is a "fix" result_unit defined!
			
			// if expected resultunit != baseunit convert to "fix" result_unit
			if($this->getUnit()->getBaseUnit() != -1)
			{
				$result = ilMath::_div($result, $this->getUnit()->getFactor(), $this->getPrecision());
			}
			else
			{
				//if resultunit == baseunit calculate to get correct precision
				$result = ilMath::_mul($result, $this->getUnit()->getFactor(), $this->getPrecision());
			}
		}
		else if($this->getUnit() == NULL && $unit != NULL)
		{
			// there is no "fix" result_unit defined, but the user has selected a unit ... 
			// so .... there are "available resultunits" in multi-selectbox selected
			// -> check if selected user-unit is baseunit
			
			if((int)$unit->getFactor() == 1)
			{
				// result is already calculated to baseunit.... -> get correct precision..
				$result = ilMath::_mul($result, 1, $this->getPrecision());
			}
			else
			{
				$result = ilMath::_div($result, $unit->getFactor(), 100);
			}
		}
		if(is_object($unit))
		{
			if(isset($frac_value))
			{
				$value = ilMath::_mul($frac_value, $unit->getFactor(), 100);
			}
		}

		$checkvalue = FALSE;
		if(isset($frac_value))
		{			
			if($this->isInTolerance($frac_value, $result, $this->getTolerance()))
			{
				$checkvalue = TRUE;
			}
		}
		else
		{
			if($this->isInTolerance($value, $result, $this->getTolerance()))
			{
				$checkvalue = TRUE;
			}
		}

		$checkunit = TRUE;
		if(is_object($this->getUnit()))
		{
			if(is_object($unit))
			{
				if($unit->getId() != $this->getUnit()->getId())
				{
					$checkunit = FALSE;
				}
			}
		}
		return $checkvalue && $checkunit && $check_fraction && $check_valid_chars;
	}

	protected function isInTolerance($v1, $v2, $p)
	{
		include_once "./Services/Math/classes/class.ilMath.php";
		$v1 = ilMath::_mul($v1, 1, $this->getPrecision());
		$b1 = ilMath::_sub($v2, abs(ilMath::_div(ilMath::_mul($p, $v2, 100), 100)), $this->getPrecision());
		$b2 = ilMath::_add($v2, abs(ilMath::_div(ilMath::_mul($p, $v2, 100), 100)), $this->getPrecision());
		if(($b1 <= $v1) && ($b2 >= $v1)) return TRUE;
		else return FALSE;
	}

	protected function checkSign($v1, $v2)
	{
		if((($v1 >= 0) && ($v2 >= 0)) || (($v1 <= 0) && ($v2 <= 0)))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function getReachedPoints($variables, $results, $value, $unit, $units)
	{
		global $ilLog;
		if($this->getRatingSimple())
		{
			if($this->isCorrect($variables, $results, $value, $units[$unit]))
			{
				return $this->getPoints();
			}
			else
			{
				return 0;
			}
		}
		else
		{
			$points = 0;
			include_once "./Services/Math/classes/class.EvalMath.php";
			include_once "./Services/Math/classes/class.ilMath.php";
			$formula = $this->substituteFormula($variables, $results);

			if(preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches))
			{
				foreach($matches[1] as $variable)
				{
					$varObj = $variables[$variable];
					if(!is_object($varObj))
					{
						continue;
					}
					if($varObj->getUnit() != NULL)
					{
						//convert unit and value to baseunit 
						if($varObj->getUnit()->getBaseUnit() != -1)
						{
							$tmp_value = $varObj->getValue() * $varObj->getUnit()->getFactor();
						}
						else
						{
							$tmp_value = $varObj->getValue();
						}
					}
					else
					{
						$tmp_value = $varObj->getValue();
					}
					$formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(".$tmp_value.")" . "\\1", $formula);
				}
			}

			$math                  = new EvalMath();
			$math->suppress_errors = TRUE;
			$result                = $math->evaluate($formula);

// result_type extension
			switch($this->getResultType())
			{
				case assFormulaQuestionResult::RESULT_DEC:
					if((substr_count($value, '.') == 1) || (substr_count($value, ',') == 1))
					{
						$exp_val    = $value;
						$frac_value = str_replace(',', '.', $exp_val);
					}
					else
					{
						$frac_value = $value;
					}
					$check_fraction = TRUE;
					break;
				case assFormulaQuestionResult::RESULT_FRAC:
					$exp_val = explode('/', $value);
					if(count($exp_val) == 1)
					{
						$frac_value = $exp_val[0] / 1;
						if(abs($frac_value) == abs($result))
						{
							$check_fraction = TRUE;
						}
						else
						{
							$check_fraction = FALSE;
						}
					}
					else
					{
						$frac_value = $exp_val[0] / $exp_val[1];
						if(abs($frac_value) == abs($result))
						{
							$check_fraction = TRUE;
						}
					}
					break;
				case assFormulaQuestionResult::RESULT_CO_FRAC:
					$exp_val = explode('/', $value);
					if(count($exp_val) == 1)
					{
						$check_fraction = FALSE;
					}
					else
					{
						$frac_value = $exp_val[0] / $exp_val[1];
						if(self::isCoprimeFraction($exp_val[0], $exp_val[1]))
						{
							$check_fraction = TRUE;
						}
					}
					break;
				case assFormulaQuestionResult::RESULT_NO_SELECTION:
				default:
					$check_fraction = TRUE;
					break;
			}

			// result unit!!
			if(is_object($this->getUnit()))
			{
				// if expected resultunit != baseunit convert to resultunit
				if($this->getUnit()->getBaseUnit() != -1)
				{
					$result = ilMath::_div($result, $this->getUnit()->getFactor(), $this->getPrecision());
				}
				else
				{
					//if resultunit == baseunit calculate to get correct precision
					$result = ilMath::_mul($result, $this->getUnit()->getFactor(), $this->getPrecision());
				}
			}

			if(is_object($unit))
			{
				if(isset($frac_value))
				{
					$value = ilMath::_mul($frac_value, $unit->getFactor(), 100);
				}
			}

			if($this->checkSign($result, $value))
			{
				$points += ilMath::_mul($this->getPoints(), ilMath::_div($this->getRatingSign(), 100));
			}
			if($this->isInTolerance(abs($value), abs($result), $this->getTolerance()))
			{
				$points += ilMath::_mul($this->getPoints(), ilMath::_div($this->getRatingValue(), 100));
			}
			if(is_object($this->getUnit()))
			{
				$base1 = $units[$unit];
				if(is_object($base1)) $base1 = $units[$base1->getBaseUnit()];
				$base2 = $units[$this->getUnit()->getBaseUnit()];
				if(is_object($base1) && is_object($base2) && $base1->getId() == $base2->getId())
				{
					$points += ilMath::_mul($this->getPoints(), ilMath::_div($this->getRatingUnit(), 100));
				}
			}
			return $points;
		}
	}

	public function getResultInfo($variables, $results, $value, $unit, $units)
	{
		if($this->getRatingSimple())
		{
			if($this->isCorrect($variables, $results, $value, $units[$unit]))
			{
				return array("points" => $this->getPoints());
			}
			else
			{
				return array("points" => 0);
			}
		}
		else
		{
			include_once "./Services/Math/classes/class.EvalMath.php";
			include_once "./Services/Math/classes/class.ilMath.php";
			$totalpoints = 0;
			$formula     = $this->substituteFormula($variables, $results);
			if(preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches))
			{
				foreach($matches[1] as $variable)
				{
					$varObj  = $variables[$variable];
					$formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(".$varObj->getBaseValue().")" . "\\1", $formula);
				}
			}
			$math                  = new EvalMath();
			$math->suppress_errors = TRUE;
			$result                = $math->evaluate($formula);
			if(is_object($this->getUnit()))
			{
				$result = ilMath::_mul($result, $this->getUnit()->getFactor(), 100);
			}
			if(is_object($unit))
			{
				$value = ilMath::_mul($value, $unit->getFactor(), 100);
			}
			else
			{
			}
			$details = array();
			if($this->checkSign($result, $value))
			{
				$points = ilMath::_mul($this->getPoints(), $this->getRatingSign()/100);
				$totalpoints += $points;
				$details['sign'] = $points;
			}
			if($this->isInTolerance(abs($value), abs($result), $this->getTolerance()))
			{
				$points     = ilMath::_mul($this->getPoints(), $this->getRatingValue()/100);
				$totalpoints += $points;
				$details['value'] = $points;
			}
			if(is_object($this->getUnit()))
			{
				$base1 = $units[$unit];
				if(is_object($base1)) $base1 = $units[$base1->getBaseUnit()];
				$base2 = $units[$this->getUnit()->getBaseUnit()];
				if(is_object($base1) && is_object($base2) && $base1->getId() == $base2->getId())
				{
					$points = ilMath::_mul($this->getPoints(), $this->getRatingUnit()/100);
					$totalpoints += $points;
					$details['unit'] = $points;
				}
			}	
			$details['points'] = $totalpoints;
			return $details;
		}
	}

	/************************************
	 * Getter and Setter
	 ************************************/

	public function setResult($result)
	{
		$this->result = $result;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function setRangeMin($range_min)
	{
//		include_once "./Services/Math/classes/class.EvalMath.php";
//		$math = new EvalMath();
//		$math->suppress_errors = TRUE;
//		$result = $math->evaluate($range_min);
//		$val = (strlen($result) > 8) ? strtoupper(sprintf("%e", $result)) : $result;
//		$this->range_min = $val;

		include_once "./Services/Math/classes/class.EvalMath.php";
		$math                  = new EvalMath();
		$math->suppress_errors = TRUE;
		$result                = $math->evaluate($range_min);
		$this->range_min       = $result;


	}

	public function getRangeMin()
	{
		return $this->range_min;
	}

	public function getRangeMinBase()
	{
		if(is_numeric($this->getRangeMin()))
		{
			if(is_object($this->getUnit()))
			{
				include_once "./Services/Math/classes/class.ilMath.php";
				return ilMath::_mul($this->getRangeMin(), $this->getUnit()->getFactor(), 100);
			}
		}
		return $this->getRangeMin();
	}

	public function setRangeMax($range_max)
	{
//		include_once "./Services/Math/classes/class.EvalMath.php";
//		$math = new EvalMath();
//		$math->suppress_errors = TRUE;
//		$result = $math->evaluate($range_max);
//		$val = (strlen($result) > 8) ? strtoupper(sprintf("%e", $result)) : $result;
//		$this->range_max = $val;

		include_once "./Services/Math/classes/class.EvalMath.php";
		$math                  = new EvalMath();
		$math->suppress_errors = TRUE;
		$result                = $math->evaluate($range_max);
		$this->range_max       = $result;

	}

	public function getRangeMax()
	{
		return $this->range_max;
	}

	public function getRangeMaxBase()
	{
		if(is_numeric($this->getRangeMax()))
		{
			if(is_object($this->getUnit()))
			{
				include_once "./Services/Math/classes/class.ilMath.php";
				return ilMath::_mul($this->getRangeMax(), $this->getUnit()->getFactor(), 100);
			}
		}
		return $this->getRangeMax();
	}

	public function setTolerance($tolerance)
	{
		$this->tolerance = $tolerance;
	}

	public function getTolerance()
	{
		return $this->tolerance;
	}

	public function setUnit($unit)
	{
		$this->unit = $unit;
	}

	public function getUnit()
	{
		return $this->unit;
	}

	public function setFormula($formula)
	{
		$this->formula = $formula;
	}

	public function getFormula()
	{
		return $this->formula;
	}

	public function setPoints($points)
	{
		$this->points = $points;
	}

	public function getPoints()
	{
		return $this->points;
	}

	public function setRatingSimple($rating_simple)
	{
		$this->rating_simple = $rating_simple;
	}

	public function getRatingSimple()
	{
		return $this->rating_simple;
	}

	public function setRatingSign($rating_sign)
	{
		$this->rating_sign = $rating_sign;
	}

	public function getRatingSign()
	{
		return $this->rating_sign;
	}

	public function setRatingValue($rating_value)
	{
		$this->rating_value = $rating_value;
	}

	public function getRatingValue()
	{
		return $this->rating_value;
	}

	public function setRatingUnit($rating_unit)
	{
		$this->rating_unit = $rating_unit;
	}

	public function getRatingUnit()
	{
		return $this->rating_unit;
	}

	public function setPrecision($precision)
	{
		$this->precision = $precision;
	}

	public function getPrecision()
	{
		return (int)$this->precision;
	}

	public function setResultType($a_result_type)
	{
		$this->result_type = $a_result_type;
	}

	public function getResultType()
	{
		return (int)$this->result_type;
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

	public static function getResultTypeByQstId($a_qst_id, $a_result)
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT result_type
			FROM il_qpl_qst_fq_res
			WHERE question_fi = %s
			AND result = %s',
			array('integer', 'text'),
			array($a_qst_id, $a_result));

		$row = $ilDB->fetchAssoc($res);

		return $row['result_type'];

	}
	
	public static function isCoprimeFraction($numerator, $denominator)
	{
		$gcd = self::getGreatestCommonDivisor(abs($numerator), abs($denominator));

		return $gcd == 1 ? true : false;
	}

	public static function convertDecimalToCoprimeFraction($decimal_value, $tolerance = 1.e-9) 
	{
		$to_string   = (string) $decimal_value;
		$is_negative = strpos($to_string, '-') === 0;
		if($is_negative)
		{
			$decimal_value = substr($decimal_value, 1);
		}
		$h1=1;
		$h2=0;
		$k1=0;
		$k2=1;
		$b = 1 / $decimal_value;
		do {
				$b = 1 / $b;
				$a = floor($b);
				$aux = $h1;
				$h1 = $a * $h1 + $h2;
				$h2 = $aux;
				$aux = $k1;
				$k1 = $a * $k1 + $k2;
				$k2 = $aux;
				$b = $b - $a;
			}while ((abs($decimal_value - $h1 / $k1) > $decimal_value * $tolerance) || ( $k1 < 0 || $b < 0 ));
		if($k1 == 1)
		{
			$result = $h1;
			$checkResult = $h1;
		}
		else
		{
			$result = "$h1/$k1";
			$checkResult = ($h1/$k1);
		}
		if($is_negative)
		{
			$result =  '-'.$result;
			$checkResult = ($h1/$k1)*-1;
		}
		if($to_string == $checkResult.'' || $checkResult.'' == $result)
		{
			return $result;
		}
		else
		{
			return array($to_string,$result);
		}
	}
	
	public static function getGreatestCommonDivisor($a, $b)
	{
		if ($b > 0)
		{
			return self::getGreatestCommonDivisor($b, $a % $b);
		}
		else
		{
			return $a;
		}
	}
	
	
	public function getAvailableResultUnits($question_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('
			SELECT * FROM il_qpl_qst_fq_res_unit 
			WHERE question_fi = %s
			ORDER BY result',
			array('integer'), array($question_id));
		
	
		while ($row = $ilDB->fetchAssoc($res))
		{
			$this->available_units[$row['result']][] = $row['unit_fi'] ;
		}
		
		return $this->available_units;
	}
	
}

?>