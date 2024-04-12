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
 * Formula Question Result
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestionResult.php 944 2009-11-09 16:11:30Z hschottm $
 * @ingroup       ModulesTestQuestionPool
 * */
class assFormulaQuestionResult
{
    public const RESULT_NO_SELECTION = 0;
    public const RESULT_DEC = 1;
    public const RESULT_FRAC = 2;
    public const RESULT_CO_FRAC = 3;

    private \ilGlobalTemplateInterface $main_tpl;

    private $available_units = [];
    private ?float $range_min = null;
    private ?float $range_max = null;

    public function __construct(
        private string $result,
        private ?string $range_min_txt,
        private ?string $range_max_txt,
        private float $tolerance,
        private ?assFormulaQuestionUnit $unit,
        private ?string $formula,
        private float $points,
        private int $precision,
        private bool $rating_simple = true,
        private ?float $rating_sign = null,
        private ?float $rating_value = null,
        private ?float $rating_unit = null,
        private float $result_type = 0
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->setRangeMin($range_min_txt);
        $this->setRangeMax($range_max_txt);

        if ($rating_sign === null) {
            $this->rating_sign = 33;
        }
        if ($rating_value === null) {
            $this->rating_value = 34;
        }
        if ($rating_unit === null) {
            $this->rating_unit = 33;
        }
    }

    public function substituteFormula($variables, $results)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $formula = $this->getFormula();

        if (preg_match_all("/(\\\$r\\d+)/ims", $formula, $matches)) {
            foreach ($matches[1] as $result) {
                if (strcmp($result, $this->getResult()) == 0) {
                    $this->main_tpl->setOnScreenMessage('failure', $lng->txt("errRecursionInResult"));
                    return false;
                }

                if (is_object($results[$result])) {
                    $formula = str_replace($result, $results[$result]->substituteFormula($variables, $results), $formula);
                } else {
                    $this->main_tpl->setOnScreenMessage('failure', $lng->txt("errFormulaQuestion"));
                    return false;
                }
            }
        }

        return "(" . $formula . ")";
    }

    public function calculateFormula($variables, $results, $question_id = 0, $use_precision = true)
    {
        $resultunits = array();
        if ($question_id > 0) {
            $resultunits = $this->getAvailableResultUnits($question_id);
        }

        $formula = $this->substituteFormula($variables, $results);
        if (preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches)) {
            foreach ($matches[1] as $variable) {
                $varObj = $variables[$variable];
                if (!is_object($varObj)) {
                    continue;
                }
                $value = $varObj->getBaseValue();
                $formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(" . $value . ")" . "\\1", $formula);
            }
        }
        $math = new EvalMath();
        $math->suppress_errors = true;

        $formula = str_replace(",", ".", $formula);
        $result = $math->evaluate($formula);
        if ($this->getUnit() !== null) {
            $result = ilMath::_div($result, $this->getUnit()->getFactor(), 100);
        }

        // @todo DON'T USE ilMath::_mul() ... bcmul() returns wrong result !!!!

        if ($use_precision == true) {
            $res = $result * 1;
            if (is_numeric($this->getPrecision())) {
                if ($this->getResultType() == self::RESULT_DEC || $this->getResultType() == self::RESULT_NO_SELECTION) {
                    $result = ilMath::_round($res, $this->getPrecision());
                }
            }
        }
        return $result;
    }

    public function findValidRandomVariables($variables, $results): void
    {
        $i = 0;
        $inRange = false;
        while ($i < 1000 && !$inRange) {
            $formula = $this->substituteFormula($variables, $results);
            if (preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches)) {
                foreach ($matches[1] as $variable) {
                    $varObj = $variables[$variable];
                    if (!is_object($varObj)) {
                        continue;
                    }
                    $varObj->setRandomValue();
                    $formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(" . $varObj->getBaseValue() . ")" . "\\1", $formula);
                }
            }
            $math = new EvalMath();
            $math->suppress_errors = true;
            $result = $math->evaluate($formula);
            $inRange = (is_numeric($result)) ? true : false;
            if ($inRange) {
                if (is_numeric($this->getRangeMin())) {
                    if ($result < $this->getRangeMinBase()) {
                        $inRange = false;
                    }
                }
                if (is_numeric($this->getRangeMax())) {
                    if ($result > $this->getRangeMaxBase()) {
                        $inRange = false;
                    }
                }
            }
            $i++;
        }
    }

    public function suggestRange($variables, $results): void
    {
        //		@todo Check this
        $range_min = null;
        $range_max = null;
        for ($i = 0; $i < 1000; $i++) {
            $formula = $this->substituteFormula($variables, $results);
            if (preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches)) {
                foreach ($matches[1] as $variable) {
                    $varObj = $variables[$variable];
                    if (!is_object($varObj)) {
                        continue;
                    }
                    $varObj->setRandomValue();
                    $formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(" . $varObj->getBaseValue() . ")" . "\\1", $formula);
                }
            }
            $math = new EvalMath();
            $math->suppress_errors = true;
            $result = $math->evaluate($formula);
            if (($range_min == null) || ($result < $range_min)) {
                $range_min = $result;
            }
            if (($range_max == null) || ($result > $range_max)) {
                $range_max = $result;
            }
        }
        if ($this->getUnit() !== null) {
            $range_min = ilMath::_div($range_min, $this->getUnit()->getFactor());
            $range_max = ilMath::_div($range_max, $this->getUnit()->getFactor());
        }
        $this->setRangeMin(ilMath::_mul($range_min, 1, $this->getPrecision()));
        $this->setRangeMax(ilMath::_mul($range_max, 1, $this->getPrecision()));
    }

    /**
     * @param      $variables      array formula variables containing units
     * @param      $results        array formula results containing units
     * @param      $value          string user input value
     * @param null $unit           user input unit
     * @return bool
     */
    public function isCorrect($variables, $results, $value, $unit = null): bool
    {
        // The user did not answer the question ....
        if ($value === null || 0 == strlen($value)) {
            return false;
        }
        $value = str_replace(' ', '', $value);

        $formula = $this->substituteFormula($variables, $results);

        $check_valid_chars = true;

        if (preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches)) {
            foreach ($matches[1] as $variable) {
                $varObj = $variables[$variable];
                if (!is_object($varObj)) {
                    continue;
                }

                if ($varObj->getUnit() != null) {
                    //convert unit and value to baseunit.... because vars could have different units
                    if ($varObj->getUnit()->getBaseUnit() != -1) {
                        $tmp_value = $varObj->getValue() * $varObj->getUnit()->getFactor();
                    } else {
                        $tmp_value = $varObj->getValue();
                    }
                } else {
                    $tmp_value = $varObj->getValue();
                }

                $formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(" . $tmp_value . ")" . "\\1", $formula);
            }
        }

        $math = new EvalMath();
        $math->suppress_errors = true;
        $result = $math->evaluate($formula); // baseunit-result!!
        $resultWithRespectedUnit = $result;

        if ($this->getUnit() !== null) {
            //there is a "fix" result_unit defined!

            // if expected resultunit != baseunit convert to "fix" result_unit
            if ($this->getUnit()->getBaseUnit() != -1) {
                $resultWithRespectedUnit = ilMath::_div($result, $this->getUnit()->getFactor());
            }
        } elseif ($this->getUnit() == null && $unit != null) {
            // there is no "fix" result_unit defined, but the user has selected a unit ...
            // so .... there are "available resultunits" in multi-selectbox selected
            // -> check if selected user-unit is baseunit
            if ($unit->getFactor() != 1 && strlen(trim($unit->getFactor())) != 1) {
                // result is already calculated to baseunit.... -> get correct precision..
                $resultWithRespectedUnit = ilMath::_div($result, $unit->getFactor());
            }
        }

        $result = substr($result, 0, strlen($resultWithRespectedUnit));

        //	check for valid chars ("0-9",",|.|/","0-9","e|E","+|-","0-9")
        $has_valid_chars = preg_match("/^-?([0-9]*)(,|\\.|\\/){0,1}([0-9]*)([eE][\\+|-]([0-9])+)?$/", $value, $matches);
        if (!$has_valid_chars) {
            $check_valid_chars = false;
        } elseif (
            (isset($matches[2]) && $matches[2] == '/') &&
            (isset($matches[4]) && strtolower($matches[4]) == "e") &&
            (!isset($matches[1]) || !strlen($matches[1]) || !isset($matches[3]) || !strlen($matches[3]) || $matches[3] == 0)) {
            $check_valid_chars = false;
        }

        // result_type extension
        switch ($this->getResultType()) {
            case assFormulaQuestionResult::RESULT_DEC:
                if (substr_count($value, '.') == 1 || substr_count($value, ',') == 1) {
                    $exp_val = $value;
                    $frac_value = str_replace(',', '.', $exp_val);
                } else {
                    $frac_value = $value;
                }

                if (substr_count($value, '/') >= 1) {
                    $check_fraction = false;
                } else {
                    $check_fraction = true;
                }
                break;

            case assFormulaQuestionResult::RESULT_FRAC:
            case assFormulaQuestionResult::RESULT_CO_FRAC:
                $exp_val = explode('/', $value);
                if (count($exp_val) == 1) {
                    $frac_value = ilMath::_div($exp_val[0], 1);

                    if (ilMath::_equals($frac_value, $resultWithRespectedUnit, $this->getPrecision())) {
                        $check_fraction = true;
                    } else {
                        $check_fraction = false;
                    }
                } else {
                    try {
                        $frac_value = ilMath::_div($exp_val[0], $exp_val[1]);
                    } catch (ilMathDivisionByZeroException $ex) {
                        if ($result) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                    $frac_value = str_replace(',', '.', $frac_value);

                    if (ilMath::_equals($frac_value, $resultWithRespectedUnit, $this->getPrecision())) {
                        $check_fraction = true;
                    }

                    if ($this->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC) {
                        if (!self::isCoprimeFraction($exp_val[0], $exp_val[1])) {
                            $check_fraction = false;
                        }
                    }
                }

                if (substr_count($value, '.') >= 1 || substr_count($value, ',') >= 1) {
                    $check_fraction = false;
                }
                break;

            case assFormulaQuestionResult::RESULT_NO_SELECTION:
            default:
                if (substr_count($value, '.') == 1 || substr_count($value, ',') == 1) {
                    $frac_value = str_replace(',', '.', $value);
                } elseif (substr_count($value, '/') == 1) {
                    $exp_val = explode('/', $value);
                    try {
                        $frac_value = ilMath::_div($exp_val[0], $exp_val[1]);
                    } catch (ilMathDivisionByZeroException $ex) {
                        if ($result) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                } else {
                    $frac_value = $value;
                }

                $check_fraction = true;
                break;
        }

        if (is_object($unit)) {
            if (isset($frac_value)) {
                $value = ilMath::_mul($frac_value, $unit->getFactor(), 100);
            }
        }

        $frac_value = ilMath::_round($frac_value, $this->getPrecision());
        $resultWithRespectedUnit = ilMath::_round($resultWithRespectedUnit, $this->getPrecision());

        $checkvalue = false;
        if (isset($frac_value)) {
            if ($this->isInTolerance($frac_value, $resultWithRespectedUnit, $this->getTolerance())) {
                $checkvalue = true;
            }
        } else {
            if ($this->isInTolerance($value, $resultWithRespectedUnit, $this->getTolerance())) {
                $checkvalue = true;
            }
        }

        $checkunit = true;
        if ($this->getUnit() !== null) {
            if (is_object($unit)) {
                if ($unit->getId() != $this->getUnit()->getId()) {
                    $checkunit = false;
                }
            }
        }
        return $checkvalue && $checkunit && $check_fraction && $check_valid_chars;
    }

    protected function isInTolerance($user_answer, $expected, $tolerated_percentage): bool
    {
        $user_answer = ilMath::_mul($user_answer, 1, $this->getPrecision());
        $tolerance_abs = abs(ilMath::_div(ilMath::_mul($tolerated_percentage, $expected, 100), 100));
        $lower_boundary = ilMath::_sub($expected, $tolerance_abs);
        $upper_boundary = ilMath::_add($expected, $tolerance_abs);

        return $lower_boundary <= $user_answer
            && $user_answer <= $upper_boundary;
    }

    protected function checkSign($v1, $v2): bool
    {
        if ((($v1 >= 0) && ($v2 >= 0)) || (($v1 <= 0) && ($v2 <= 0))) {
            return true;
        } else {
            return false;
        }
    }

    public function getReachedPoints($variables, $results, $value, $unit, $units)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        if ($this->getRatingSimple()) {
            if ($this->isCorrect($variables, $results, $value, $units[$unit] ?? null)) {
                return $this->getPoints();
            } else {
                return 0;
            }
        } else {
            $points = 0;
            $formula = $this->substituteFormula($variables, $results);

            if (preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches)) {
                foreach ($matches[1] as $variable) {
                    $varObj = $variables[$variable];
                    if (!is_object($varObj)) {
                        continue;
                    }
                    if ($varObj->getUnit() != null) {
                        //convert unit and value to baseunit
                        if ($varObj->getUnit()->getBaseUnit() != -1) {
                            $tmp_value = $varObj->getValue() * $varObj->getUnit()->getFactor();
                        } else {
                            $tmp_value = $varObj->getValue();
                        }
                    } else {
                        $tmp_value = $varObj->getValue();
                    }
                    $formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(" . $tmp_value . ")" . "\\1", $formula);
                }
            }

            $math = new EvalMath();
            $math->suppress_errors = true;
            $result = $math->evaluate($formula);

            // result_type extension
            switch ($this->getResultType()) {
                case assFormulaQuestionResult::RESULT_DEC:
                    if ((substr_count($value, '.') == 1) || (substr_count($value, ',') == 1)) {
                        $exp_val = $value;
                        $frac_value = str_replace(',', '.', $exp_val);
                    } else {
                        $frac_value = $value;
                    }
                    $check_fraction = true;
                    break;
                case assFormulaQuestionResult::RESULT_FRAC:
                    $exp_val = explode('/', $value);
                    if (count($exp_val) == 1) {
                        $frac_value = ilMath::_div($exp_val[0], 1, $this->getPrecision());
                        if (ilMath::_equals(abs($frac_value), abs($result), $this->getPrecision())) {
                            $check_fraction = true;
                        } else {
                            $check_fraction = false;
                        }
                    } else {
                        $frac_value = ilMath::_div($exp_val[0], $exp_val[1], $this->getPrecision());
                        if (ilMath::_equals(abs($frac_value), abs($result), $this->getPrecision())) {
                            $check_fraction = true;
                        }
                    }
                    break;
                case assFormulaQuestionResult::RESULT_CO_FRAC:
                    $exp_val = explode('/', $value);
                    if (count($exp_val) == 1) {
                        $check_fraction = false;
                    } else {
                        $frac_value = ilMath::_div($exp_val[0], $exp_val[1], $this->getPrecision());
                        if (self::isCoprimeFraction($exp_val[0], $exp_val[1])) {
                            $check_fraction = true;
                        }
                    }
                    break;
                case assFormulaQuestionResult::RESULT_NO_SELECTION:
                default:
                    $check_fraction = true;
                    break;
            }

            // result unit!!
            if ($this->getUnit() !== null) {
                // if expected resultunit != baseunit convert to resultunit
                if ($this->getUnit()->getBaseUnit() != -1) {
                    $result = ilMath::_div($result, $this->getUnit()->getFactor(), $this->getPrecision());
                } else {
                    //if resultunit == baseunit calculate to get correct precision
                    $result = ilMath::_mul($result, $this->getUnit()->getFactor(), $this->getPrecision());
                }
            }

            if (is_object($unit)) {
                if (isset($frac_value)) {
                    $value = ilMath::_mul($frac_value, $unit->getFactor(), 100);
                }
            }

            if ($this->checkSign($result, $value)) {
                $points += ilMath::_mul($this->getPoints(), ilMath::_div($this->getRatingSign(), 100));
            }

            if ($this->isInTolerance(abs($value), abs($result), $this->getTolerance())) {
                $points += ilMath::_mul($this->getPoints(), ilMath::_div($this->getRatingValue(), 100));
            }
            if ($this->getUnit() !== null) {
                $base1 = $units[$unit] ?? null;
                if (is_object($base1)) {
                    $base1 = $units[$base1->getBaseUnit()];
                }
                $base2 = $units[$this->getUnit()->getBaseUnit()];
                if (is_object($base1) && is_object($base2) && $base1->getId() == $base2->getId()) {
                    $points += ilMath::_mul($this->getPoints(), ilMath::_div($this->getRatingUnit(), 100));
                }
            }
            return $points;
        }
    }

    public function getResultInfo($variables, $results, $value, $unit, $units): array
    {
        if ($this->getRatingSimple()) {
            if ($this->isCorrect($variables, $results, $value, $units[$unit] ?? null)) {
                return array("points" => $this->getPoints());
            } else {
                return array("points" => 0);
            }
        } else {
            $totalpoints = 0;
            $formula = $this->substituteFormula($variables, $results);
            if (preg_match_all("/(\\\$v\\d+)/ims", $formula, $matches)) {
                foreach ($matches[1] as $variable) {
                    $varObj = $variables[$variable];
                    $formula = preg_replace("/\\\$" . substr($variable, 1) . "(?![0-9]+)/", "(" . $varObj->getBaseValue() . ")" . "\\1", $formula);
                }
            }
            $math = new EvalMath();
            $math->suppress_errors = true;
            $result = $math->evaluate($formula);
            if ($this->getUnit() !== null) {
                $result = ilMath::_mul($result, $this->getUnit()->getFactor(), 100);
            }
            if (is_object($unit)) {
                $value = ilMath::_mul($value, $unit->getFactor(), 100);
            } else {
            }
            $details = array();
            if ($this->checkSign($result, $value)) {
                $points = ilMath::_mul($this->getPoints(), $this->getRatingSign() / 100);
                $totalpoints += $points;
                $details['sign'] = $points;
            }
            if ($this->isInTolerance(abs($value), abs($result), $this->getTolerance())) {
                $points = ilMath::_mul($this->getPoints(), $this->getRatingValue() / 100);
                $totalpoints += $points;
                $details['value'] = $points;
            }
            if ($this->getUnit() !== null) {
                $base1 = $units[$unit];
                if (is_object($base1)) {
                    $base1 = $units[$base1->getBaseUnit()];
                }
                $base2 = $units[$this->getUnit()->getBaseUnit()];
                if (is_object($base1) && is_object($base2) && $base1->getId() == $base2->getId()) {
                    $points = ilMath::_mul($this->getPoints(), $this->getRatingUnit() / 100);
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

    public function setResult($result): void
    {
        $this->result = $result;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setRangeMin(?string $range_min): void
    {
        if ($range_min === null) {
            return;
        }

        $this->range_min = (float) $range_min;
    }

    public function getRangeMin(): float
    {
        return $this->range_min;
    }

    public function getRangeMinBase()
    {
        if ($this->getUnit() !== null) {
            return ilMath::_mul($this->getRangeMin(), $this->getUnit()->getFactor(), 100);
        }

        return $this->getRangeMin();
    }

    public function setRangeMax(?string $range_max): void
    {
        if ($range_max === null) {
            return;
        }
        $this->range_max = (float) $range_max;
    }

    public function getRangeMax(): float
    {
        return $this->range_max;
    }

    public function getRangeMaxBase()
    {
        if ($this->getUnit() !== null) {
            return ilMath::_mul($this->getRangeMax(), $this->getUnit()->getFactor(), 100);
        }

        return $this->getRangeMax();
    }

    public function setTolerance($tolerance): void
    {
        $this->tolerance = $tolerance;
    }

    public function getTolerance(): float
    {
        return $this->tolerance;
    }

    public function setUnit(?assFormulaQuestionUnit $unit): void
    {
        $this->unit = $unit;
    }

    public function getUnit(): ?assFormulaQuestionUnit
    {
        return $this->unit;
    }

    public function setFormula(?string $formula): void
    {
        $this->formula = $formula;
    }

    public function getFormula(): ?string
    {
        return $this->formula;
    }

    public function setPoints(float $points): void
    {
        $this->points = $points;
    }

    public function getPoints(): float
    {
        return $this->points;
    }

    public function setRatingSimple(bool $rating_simple): void
    {
        $this->rating_simple = $rating_simple;
    }

    public function getRatingSimple(): bool
    {
        return $this->rating_simple;
    }

    public function setRatingSign(float $rating_sign): void
    {
        $this->rating_sign = $rating_sign;
    }

    public function getRatingSign(): float
    {
        return $this->rating_sign;
    }

    public function setRatingValue(float $rating_value): void
    {
        $this->rating_value = $rating_value;
    }

    public function getRatingValue(): float
    {
        return $this->rating_value;
    }

    public function setRatingUnit(float $rating_unit): void
    {
        $this->rating_unit = $rating_unit;
    }

    public function getRatingUnit(): float
    {
        return $this->rating_unit;
    }

    public function setPrecision(float $precision): void
    {
        $this->precision = $precision;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function setResultType(int $a_result_type): void
    {
        $this->result_type = $a_result_type;
    }

    public function getResultType(): int
    {
        return (int) $this->result_type;
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

    public static function getResultTypeByQstId($a_qst_id, $a_result)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            '
			SELECT result_type
			FROM il_qpl_qst_fq_res
			WHERE question_fi = %s
			AND result = %s',
            array('integer', 'text'),
            array($a_qst_id, $a_result)
        );

        $row = $ilDB->fetchAssoc($res);

        return $row['result_type'];
    }

    public static function isCoprimeFraction($numerator, $denominator): bool
    {
        $gcd = self::getGreatestCommonDivisor(abs($numerator), abs($denominator));

        return $gcd == 1 ? true : false;
    }

    public static function convertDecimalToCoprimeFraction($decimal_value, $tolerance = 1.e-9)
    {
        $to_string = (string) $decimal_value;
        $is_negative = strpos($to_string, '-') === 0;
        if ($is_negative) {
            $decimal_value = substr($decimal_value, 1);
        }
        $h1 = 1;
        $h2 = 0;
        $k1 = 0;
        $k2 = 1;
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
        } while ((abs($decimal_value - $h1 / $k1) > $decimal_value * $tolerance) || ($k1 < 0 || $b < 0));
        if ($k1 == 1) {
            $result = $h1;
            $checkResult = $h1;
        } else {
            $result = "$h1/$k1";
            $checkResult = ($h1 / $k1);
        }
        if ($is_negative) {
            $result = '-' . $result;
            $checkResult = ($h1 / $k1) * -1;
        }
        if ($to_string == $checkResult . '' || $checkResult . '' == $result) {
            return $result;
        } else {
            return array($to_string,$result);
        }
    }

    public static function getGreatestCommonDivisor($a, $b)
    {
        if ($b > 0) {
            return self::getGreatestCommonDivisor($b, $a % $b);
        } else {
            return $a;
        }
    }


    public function getAvailableResultUnits($question_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            '
			SELECT * FROM il_qpl_qst_fq_res_unit
			WHERE question_fi = %s
			ORDER BY result',
            array('integer'),
            array($question_id)
        );


        while ($row = $ilDB->fetchAssoc($res)) {
            $this->available_units[$row['result']][] = $row['unit_fi'] ;
        }

        return $this->available_units;
    }
}
