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

use ILIAS\TestQuestionPool\Questions\QuestionAutosaveable;

use ILIAS\Test\Logging\AdditionalInformationGenerator;

/**
 * Class for single choice questions
 * assFormulaQuestion is a class for single choice questions.
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestion.php 1236 2010-02-15 15:44:16Z hschottm $
 * @ingroup components\ILIASTestQuestionPool
 */
class assFormulaQuestion extends assQuestion implements iQuestionCondition, QuestionAutosaveable
{
    private array $variables;
    private array $results;
    private array $resultunits;
    private ilUnitConfigurationRepository $unitrepository;
    protected PassPresentedVariablesRepo $pass_presented_variables_repo;

    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->variables = [];
        $this->results = [];
        $this->resultunits = [];
        $this->unitrepository = new ilUnitConfigurationRepository(0);
        $this->pass_presented_variables_repo = new PassPresentedVariablesRepo($this->db);
    }

    public function clearVariables(): void
    {
        $this->variables = [];
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getVariable(string $variable): ?assFormulaQuestionVariable
    {
        if (array_key_exists($variable, $this->variables)) {
            return $this->variables[$variable];
        }
        return null;
    }

    public function addVariable(assFormulaQuestionVariable $variable): void
    {
        $this->variables[$variable->getVariable()] = $variable;
    }

    public function clearResults(): void
    {
        $this->results = [];
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getResult(string $result): ?assFormulaQuestionResult
    {
        if (array_key_exists($result, $this->results)) {
            return $this->results[$result];
        }
        return null;
    }

    public function addResult(assFormulaQuestionResult $result): void
    {
        $this->results[$result->getResult()] = $result;
    }

    public function addResultUnits(
        ?assFormulaQuestionResult $result,
        ?array $unit_ids
    ): void {
        $this->resultunits[$result->getResult()] = [];
        if ($result === null || $unit_ids === null) {
            return;
        }
        foreach ($unit_ids as $id) {
            if (is_numeric($id) && ($id > 0)) {
                $this->resultunits[$result->getResult()][$id] = $this->getUnitrepository()->getUnit($id);
            }
        }
    }

    public function addResultUnit(
        ?assFormulaQuestionResult $result,
        ?assFormulaQuestionUnit $unit
    ): void {
        if ($result === null || $unit === null) {
            return;
        }

        if (!array_key_exists($result->getResult(), $this->resultunits) ||
            !is_array($this->resultunits[$result->getResult()])) {
            $this->resultunits[$result->getResult()] = [];
        }
        $this->resultunits[$result->getResult()][$unit->getId()] = $unit;
    }

    public function getResultUnits(assFormulaQuestionResult $result): array
    {
        if (!isset($this->resultunits[$result->getResult()])) {
            return [];
        }

        $result_units = $this->resultunits[$result->getResult()];

        usort(
            $result_units,
            static fn(assFormulaQuestionUnit $a, assFormulaQuestionUnit $b) =>
                $a->getSequence() <=> $b->getSequence()
        );

        return $result_units;
    }

    public function getAllResultUnits(): array
    {
        return $this->resultunits;
    }

    public function hasResultUnit(
        assFormulaQuestionResult $result,
        int $unit_id
    ): bool {
        if (array_key_exists($result->getResult(), $this->resultunits)
            && array_key_exists($unit_id, $this->resultunits[$result->getResult()])) {
            return true;
        }

        return false;
    }

    public function parseQuestionText(): void
    {
        $this->clearResults();
        $this->clearVariables();
        if (preg_match_all("/(\\\$v\\d+)/ims", $this->getQuestion(), $matches)) {
            foreach ($matches[1] as $variable) {
                $varObj = new assFormulaQuestionVariable($variable, '0.0', '0.0', null, 0);
                $this->addVariable($varObj);
            }
        }

        if (preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches)) {
            foreach ($rmatches[1] as $result) {
                $resObj = new assFormulaQuestionResult($result, null, null, 0, null, null, 1, 1, true);
                $this->addResult($resObj);
            }
        }
    }

    public function checkForDuplicateVariables(): bool
    {
        if (preg_match_all("/(\\\$v\\d+)/ims", $this->getQuestion(), $matches)) {
            if ((count(array_unique($matches[1]))) != count($matches[1])) {
                return false;
            }
        }
        return true;
    }

    public function checkForDuplicateResults(): bool
    {
        if (preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches)) {
            if ((count(array_unique($rmatches[1]))) != count($rmatches[1])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $questionText
     * @return assFormulaQuestionResult[] $resObjects
     */
    public function fetchAllResults($questionText): array
    {
        $resObjects = [];
        $matches = null;

        if (preg_match_all("/(\\\$r\\d+)/ims", $questionText, $matches)) {
            foreach ($matches[1] as $resultKey) {
                $resObjects[] = $this->getResult($resultKey);
            }
        }

        return $resObjects;
    }

    /**
     * @param string $questionText
     * @return assFormulaQuestionVariable[] $varObjects
     */
    public function fetchAllVariables(string $question_text): array
    {
        $var_objects = [];
        $matches = null;

        if (preg_match_all("/(\\\$v\\d+)/ims", $question_text, $matches)) {
            $var_objects = array_reduce(
                $matches[1],
                function (array $c, string $v): array {
                    $vo = $this->getVariable($v);
                    if ($vo !== null) {
                        $c[] = $vo;
                    }
                    return $c;
                },
                []
            );
        }

        return $var_objects;
    }

    /**
     * @param array $userSolution
     * @return bool
     */
    public function hasRequiredVariableSolutionValues(array $userSolution): bool
    {
        foreach ($this->fetchAllVariables($this->getQuestion()) as $varObj) {
            if (!isset($userSolution[$varObj->getVariable()])) {
                return false;
            }

            if ($userSolution[$varObj->getVariable()] === '') {
                return false;
            }
        }

        return true;
    }

    public function getVariableSolutionValuesForPass(
        int $active_id,
        int $pass
    ): array {
        $question_id = $this->getId();
        $values = $this->pass_presented_variables_repo->getFor(
            $question_id,
            $active_id,
            $pass
        );
        if(is_null($values)) {
            $values = $this->getInitialVariableSolutionValues();
            $this->pass_presented_variables_repo->store(
                $question_id,
                $active_id,
                $pass,
                $values
            );
        }
        return $values;
    }

    public function getInitialVariableSolutionValues(): array
    {
        foreach ($this->fetchAllResults($this->getQuestion()) as $resObj) {
            $resObj->findValidRandomVariables($this->getVariables(), $this->getResults());
        }

        $variableSolutionValues = [];

        foreach ($this->fetchAllVariables($this->getQuestion()) as $varObj) {
            $variableSolutionValues[$varObj->getVariable()] = $varObj->getValue();
        }

        return $variableSolutionValues;
    }

    public function saveCurrentSolution(int $active_id, int $pass, $value1, $value2, bool $authorized = true, $tstamp = 0): int
    {
        $init_solution_vars = $this->getVariableSolutionValuesForPass($active_id, $pass);
        foreach ($init_solution_vars as $val1 => $val2) {
            $this->db->manipulateF(
                "DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s AND value1 = %s",
                ['integer', 'integer','integer', 'text'],
                [$active_id, $this->getId(), $pass, $val1]
            );
            parent::saveCurrentSolution($active_id, $pass, $val1, $val2, $authorized);
        }
        return parent::saveCurrentSolution($active_id, $pass, $value1, $value2, $authorized, $tstamp);
    }

    /**
     * @param int[] $selections
     * @param string[] $correctness_icons
     * @return bool|mixed|string
     */
    public function substituteVariables(array $userdata, bool $graphicalOutput = false, bool $forsolution = false, bool $result_output = false, array $correctness_icons = [])
    {
        if ((count($this->results) == 0) && (count($this->variables) == 0)) {
            return false;
        }

        $text = $this->getQuestion();

        foreach ($this->fetchAllVariables($this->getQuestion()) as $varObj) {
            if (isset($userdata[$varObj->getVariable()]) && strlen($userdata[$varObj->getVariable()])) {
                $varObj->setValue($userdata[$varObj->getVariable()]);
            }

            $unit = (is_object($varObj->getUnit())) ? $varObj->getUnit()->getUnit() : "";

            $val = '';
            if ($varObj->getValue() !== null) {
                $val = (strlen($varObj->getValue()) > 8) ? strtoupper(sprintf("%e", $varObj->getValue())) : $varObj->getValue();
            }

            $text = preg_replace("/\\$" . substr($varObj->getVariable(), 1) . "(?![0-9]+)/", $val . " " . $unit . "\\1", $text);
        }

        $text = $this->purifyAndPrepareTextAreaOutput($text);

        if (preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches)) {
            foreach ($rmatches[1] as $result) {
                $resObj = $this->getResult($result);
                $value = "";
                $frac_helper = '';
                $userdata[$result]['result_type'] = $resObj->getResultType();
                $is_frac = false;
                if (
                    $resObj->getResultType() == assFormulaQuestionResult::RESULT_FRAC ||
                    $resObj->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC
                ) {
                    $is_frac = true;
                }
                if (is_array($userdata) &&
                    isset($userdata[$result]) &&
                    isset($userdata[$result]["value"])) {

                    $input = $this->generateResultInputHTML($result, (string) $userdata[$result]["value"], $forsolution);
                } elseif ($forsolution) {
                    $value = '';
                    if (!is_array($userdata)) {
                        $value = $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId());
                        $value = sprintf("%." . $resObj->getPrecision() . "f", $value);
                    }

                    if ($is_frac) {
                        $value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($value);
                        if (is_array($value)) {
                            $frac_helper = $value[1];
                            $value = $value[0];
                        }
                    }

                    $input = $this->generateResultInputHTML($result, $value, true);
                } else {
                    $input = $this->generateResultInputHTML($result, '', false);
                }

                $units = "";
                $result_units = $this->getResultUnits($resObj);
                if (count($result_units) > 0) {
                    if ($forsolution) {
                        if (is_array($userdata)) {
                            foreach ($result_units as $unit) {
                                if (isset($userdata[$result]["unit"]) && $userdata[$result]["unit"] == $unit->getId()) {
                                    $units = $unit->getUnit();
                                }
                            }
                        } else {
                            if ($resObj->getUnit()) {
                                $units = $resObj->getUnit()->getUnit();
                            }
                        }
                    } else {
                        $units = '<select name="result_' . $result . '_unit">';
                        $units .= '<option value="-1">' . $this->lng->txt("select_unit") . '</option>';
                        foreach ($result_units as $unit) {
                            $units .= '<option value="' . $unit->getId() . '"';
                            if (array_key_exists($result, $userdata) &&
                                is_array($userdata[$result]) &&
                                array_key_exists('unit', $userdata[$result])) {
                                if ($userdata[$result]["unit"] == $unit->getId()) {
                                    $units .= ' selected="selected"';
                                }
                            }
                            $units .= '>' . $unit->getUnit() . '</option>';
                        }
                        $units .= '</select>';
                    }
                } else {
                    $units = "";
                }
                switch ($resObj->getResultType()) {
                    case assFormulaQuestionResult::RESULT_DEC:
                        $units .= ' ' . $this->lng->txt('expected_result_type') . ': ' . $this->lng->txt('result_dec');
                        break;
                    case assFormulaQuestionResult::RESULT_FRAC:
                        if ($frac_helper !== '') {
                            $units .= ' &asymp; ' . $frac_helper . ', ';
                        } elseif (is_array($userdata) &&
                            array_key_exists($result, $userdata) &&
                            array_key_exists('frac_helper', $userdata[$result]) &&
                            is_string($userdata[$result]["frac_helper"])) {
                            if (!preg_match('-/-', $value)) {
                                $units .= ' &asymp; ' . $userdata[$result]["frac_helper"] . ', ';
                            }
                        }
                        $units .= ' ' . $this->lng->txt('expected_result_type') . ': ' . $this->lng->txt('result_frac');
                        break;
                    case assFormulaQuestionResult::RESULT_CO_FRAC:
                        if ($frac_helper !== '') {
                            $units .= ' &asymp; ' . $frac_helper . ', ';
                        } elseif (is_array($userdata) && isset($userdata[$result]) && isset($userdata[$result]["frac_helper"]) && $userdata[$result]["frac_helper"] !== '') {
                            if (!preg_match('-/-', $value)) {
                                $units .= ' &asymp; ' . $userdata[$result]["frac_helper"] . ', ';
                            }
                        }
                        $units .= ' ' . $this->lng->txt('expected_result_type') . ': ' . $this->lng->txt('result_co_frac');
                        break;
                    case assFormulaQuestionResult::RESULT_NO_SELECTION:
                        break;
                }
                $checkSign = "";
                if ($graphicalOutput) {
                    $resunit = null;
                    $user_value = '';
                    if (is_array($userdata) && is_array($userdata[$result])) {
                        if (isset($userdata[$result]["unit"]) && $userdata[$result]["unit"] > 0) {
                            $resunit = $this->getUnitrepository()->getUnit($userdata[$result]["unit"]);
                        }

                        if (isset($userdata[$result]["value"])) {
                            $user_value = $userdata[$result]["value"];
                        }
                    }

                    $template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution_image.html", true, true, 'components/ILIAS/TestQuestionPool');

                    $correctness_icon = $correctness_icons['not_correct'];
                    if ($resObj->isCorrect($this->getVariables(), $this->getResults(), $user_value, $resunit)) {
                        $correctness_icon = $correctness_icons['correct'];
                    }
                    $template->setCurrentBlock("icon_ok");
                    $template->setVariable("ICON_OK", $correctness_icon);
                    $template->parseCurrentBlock();

                    $checkSign = $template->get();
                }
                $resultOutput = "";
                if ($result_output) {
                    $template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution_result.html", true, true, 'components/ILIAS/TestQuestionPool');

                    if (is_array($userdata) &&
                        array_key_exists($resObj->getResult(), $userdata) &&
                        array_key_exists('value', $userdata[$resObj->getResult()])) {
                        $found = $resObj->getResultInfo(
                            $this->getVariables(),
                            $this->getResults(),
                            $userdata[$resObj->getResult()]["value"],
                            $userdata[$resObj->getResult()]["unit"] ?? null,
                            $this->getUnitrepository()->getUnits()
                        );
                    } else {
                        $found = $resObj->getResultInfo(
                            $this->getVariables(),
                            $this->getResults(),
                            $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId()),
                            is_object($resObj->getUnit()) ? $resObj->getUnit()->getId() : null,
                            $this->getUnitrepository()->getUnits()
                        );
                    }
                    $resulttext = "(";
                    if ($resObj->getRatingSimple()) {
                        if ($frac_helper) {
                            $resulttext .= "n/a";
                        } else {
                            $resulttext .= $found['points'] . " " . (($found['points'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points'));
                        }
                    } else {
                        $resulttext .= $this->lng->txt("rated_sign") . " " . (($found['sign']) ? $found['sign'] : 0) . " " . (($found['sign'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points')) . ", ";
                        $resulttext .= $this->lng->txt("rated_value") . " " . (($found['value']) ? $found['value'] : 0) . " " . (($found['value'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points')) . ", ";
                        $resulttext .= $this->lng->txt("rated_unit") . " " . (($found['unit']) ? $found['unit'] : 0) . " " . (($found['unit'] == 1) ? $this->lng->txt('point') : $this->lng->txt('points'));
                    }

                    $resulttext .= ")";
                    $template->setVariable("RESULT_OUTPUT", $resulttext);

                    $resultOutput = $template->get();
                }
                $text = preg_replace("/\\\$" . substr($result, 1) . "(?![0-9]+)/", $input . " " . $units . " " . $checkSign . " " . $resultOutput . " " . "\\1", $text);
            }
        }
        return $text;
    }

    protected function generateResultInputHTML(string $result_key, string $result_value, bool $forsolution): string
    {
        if ($forsolution) {
            return  '<span class="ilc_qinput_TextInput solutionbox">'
                . ilLegacyFormElementsUtil::prepareFormOutput($result_value)
                . '</span>';
        }
        $input = '<input class="ilc_qinput_TextInput" type="text"';
        $input .= 'spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off"';
        $input .= 'name="result_' . $result_key . '"';
        $input .= ' value="' . $result_value . '"/>';
        return $input;
    }

    /**
     * Check if advanced rating can be used for a result. This is only possible if there is exactly
     * one possible correct unit for the result, otherwise it is impossible to determine wheather the
     * unit is correct or the value.
     * @return boolean True if advanced rating could be used, false otherwise
     */
    public function canUseAdvancedRating($result): bool
    {
        $result_units = $this->getResultUnits($result);
        $resultunit = $result->getUnit();
        $similar_units = 0;
        foreach ($result_units as $unit) {
            if (is_object($resultunit)) {
                if ($resultunit->getId() != $unit->getId()) {
                    if ($resultunit->getBaseUnit() && $unit->getBaseUnit()) {
                        if ($resultunit->getBaseUnit() == $unit->getBaseUnit()) {
                            return false;
                        }
                    }
                    if ($resultunit->getBaseUnit()) {
                        if ($resultunit->getBaseUnit() == $unit->getId()) {
                            return false;
                        }
                    }
                    if ($unit->getBaseUnit()) {
                        if ($unit->getBaseUnit() == $resultunit->getId()) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Returns true, if the question is complete for use
     * @return boolean True, if the single choice question is complete for use, otherwise false
     */
    public function isComplete(): bool
    {
        if (($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0)) {
            return true;
        } else {
            return false;
        }
    }

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        // save variables
        $affectedRows = $this->db->manipulateF(
            "
		DELETE FROM il_qpl_qst_fq_var
		WHERE question_fi = %s",
            ["integer"],
            [$this->getId()]
        );

        foreach ($this->variables as $variable) {
            $next_id = $this->db->nextId('il_qpl_qst_fq_var');
            $this->db->insert(
                'il_qpl_qst_fq_var',
                [
                'variable_id' => ['integer', $next_id],
                'question_fi' => ['integer', $this->getId()],
                'variable' => ['text', $variable->getVariable()],
                'range_min' => ['float', $variable->getRangeMin()],
                'range_max' => ['float', $variable->getRangeMax()],
                'unit_fi' => ['integer', (is_object($variable->getUnit()) ? (int) $variable->getUnit()->getId() : 0)],
                'varprecision' => ['integer', (int) $variable->getPrecision()],
                'intprecision' => ['integer', (int) $variable->getIntprecision()],
                'range_min_txt' => ['text', $variable->getRangeMinTxt()],
                'range_max_txt' => ['text', $variable->getRangeMaxTxt()]
            ]
            );
        }
        // save results
        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res WHERE question_fi = %s",
            ["integer"],
            [$this->getId()]
        );

        foreach ($this->results as $result) {
            $next_id = $this->db->nextId('il_qpl_qst_fq_res');
            if (is_object($result->getUnit())) {
                $tmp_result_unit = $result->getUnit()->getId();
            } else {
                $tmp_result_unit = null;
            }

            $formula = null;
            if ($result->getFormula() !== null) {
                $formula = str_replace(",", ".", $result->getFormula());
            }

            $this->db->insert("il_qpl_qst_fq_res", [
                "result_id" => ["integer", $next_id],
                "question_fi" => ["integer", $this->getId()],
                "result" => ["text", $result->getResult()],
                "range_min" => ["float", $result->getRangeMin()],
                "range_max" => ["float", $result->getRangeMax()],
                "tolerance" => ["float", $result->getTolerance()],
                "unit_fi" => ["integer", (int) $tmp_result_unit],
                "formula" => ["clob", $formula],
                "resprecision" => ["integer", $result->getPrecision()],
                "rating_simple" => ["integer", ($result->getRatingSimple()) ? 1 : 0],
                "rating_sign" => ["float", ($result->getRatingSimple()) ? 0 : $result->getRatingSign()],
                "rating_value" => ["float", ($result->getRatingSimple()) ? 0 : $result->getRatingValue()],
                "rating_unit" => ["float", ($result->getRatingSimple()) ? 0 : $result->getRatingUnit()],
                "points" => ["float", $result->getPoints()],
                "result_type" => ['integer', (int) $result->getResultType()],
                "range_min_txt" => ["text", $result->getRangeMinTxt()],
                "range_max_txt" => ["text", $result->getRangeMaxTxt()]

            ]);
        }
        // save result units
        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
            ["integer"],
            [$this->getId()]
        );
        foreach ($this->results as $result) {
            foreach ($this->getResultUnits($result) as $unit) {
                $next_id = $this->db->nextId('il_qpl_qst_fq_res_unit');
                $affectedRows = $this->db->manipulateF(
                    "INSERT INTO il_qpl_qst_fq_res_unit (result_unit_id, question_fi, result, unit_fi) VALUES (%s, %s, %s, %s)",
                    ['integer', 'integer', 'text', 'integer'],
                    [
                        $next_id,
                        $this->getId(),
                        $result->getResult(),
                        $unit->getId()
                    ]
                );
            }
        }

        parent::saveToDb();
    }

    public function loadFromDb(int $question_id): void
    {
        $result = $this->db->queryF(
            "SELECT qpl_questions.* FROM qpl_questions WHERE question_id = %s",
            ['integer'],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            //$this->setSuggestedSolution($data["solution_hint"]);
            $this->setPoints($data['points']);
            $this->setOriginalId($data["original_id"]);
            $this->setObjId($data["obj_fi"]);
            $this->setAuthor($data["author"]);
            $this->setOwner($data["owner"]);

            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }

            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }

            $this->unitrepository = new ilUnitConfigurationRepository($question_id);

            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));

            // load variables
            $result = $this->db->queryF(
                "SELECT * FROM il_qpl_qst_fq_var WHERE question_fi = %s",
                ['integer'],
                [$question_id]
            );
            if ($result->numRows() > 0) {
                while ($data = $this->db->fetchAssoc($result)) {
                    $varObj = new assFormulaQuestionVariable(
                        $data['variable'],
                        $data['range_min_txt'],
                        $data['range_max_txt'],
                        $this->getUnitrepository()->getUnit($data['unit_fi']),
                        $data['varprecision'],
                        $data['intprecision']
                    );
                    $this->addVariable($varObj);
                }
            }
            // load results
            $result = $this->db->queryF(
                "SELECT * FROM il_qpl_qst_fq_res WHERE question_fi = %s",
                ['integer'],
                [$question_id]
            );
            if ($result->numRows() > 0) {
                while ($data = $this->db->fetchAssoc($result)) {
                    $resObj = new assFormulaQuestionResult(
                        $data['result'],
                        $data['range_min_txt'],
                        $data['range_max_txt'],
                        $data['tolerance'],
                        $this->getUnitrepository()->getUnit($data['unit_fi']),
                        $data['formula'],
                        $data['points'],
                        $data['resprecision'],
                        $data['rating_simple'] === 1,
                        $data['rating_sign'],
                        $data['rating_value'],
                        $data['rating_unit']
                    );
                    $resObj->setResultType($data['result_type']);
                    $this->addResult($resObj);
                }
            }

            // load result units
            $result = $this->db->queryF(
                "SELECT * FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
                ['integer'],
                [$question_id]
            );
            if ($result->numRows() > 0) {
                while ($data = $this->db->fetchAssoc($result)) {
                    $unit = $this->getUnitrepository()->getUnit($data["unit_fi"]);
                    $resObj = $this->getResult($data["result"]);
                    $this->addResultUnit($resObj, $unit);
                }
            }
        }
        parent::loadFromDb($question_id);
    }

    protected function cloneQuestionTypeSpecificProperties(
        \assQuestion $target
    ): \assQuestion {
        $this->unitrepository->cloneUnits($this->getId(), $target->getId());
        return $target;
    }

    /**
     * Returns the maximum points, a learner can reach answering the question
     * @see $points
     */
    public function getMaximumPoints(): float
    {
        $points = 0;
        foreach ($this->results as $result) {
            $points += $result->getPoints();
        }
        return $points;
    }

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        if ($pass === null) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $solutions = $this->getSolutionValues($active_id, $pass, $authorized_solution);
        $user_solution = [];
        foreach ($solutions as $solution_value) {
            if (preg_match('/^(\\\$v\\d+)$/', $solution_value['value1'], $matches)) {
                $user_solution[$matches[1]] = $solution_value['value2'];
                $var_obj = $this->getVariable($solution_value['value1']);
                $var_obj->setValue($solution_value['value2']);
                continue;
            }

            if (preg_match('/^(\\\$r\\d+)$/', $solution_value['value1'], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = [];
                }
                $user_solution[$matches[1]]['value'] = $solution_value['value2'];
                continue;
            }

            if (preg_match('/^(\\\$r\\d+)_unit$/', $solution_value['value1'], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = [];
                }
                $user_solution[$matches[1]]['unit'] = $solution_value['value2'];
            }
        }

        $points = 0;
        foreach ($this->getResults() as $result) {
            $points += $result->getReachedPoints(
                $this->getVariables(),
                $this->getResults(),
                $user_solution[$result->getResult()]['value'] ?? '',
                $user_solution[$result->getResult()]['unit'] ?? '',
                $this->unitrepository->getUnits()
            );
        }

        return (float) $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $user_solution = $previewSession->getParticipantsSolution();

        $points = 0;
        foreach ($this->getResults() as $result) {
            $v = isset($user_solution[$result->getResult()]) ? $user_solution[$result->getResult()] : null;
            $u = isset($user_solution[$result->getResult() . '_unit']) ? $user_solution[$result->getResult() . '_unit'] : null;

            $points += $result->getReachedPoints(
                $this->getVariables(),
                $this->getResults(),
                $v,
                $u,
                $this->unitrepository->getUnits()
            );
        }

        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $points);

        return $this->ensureNonNegativePoints($reachedPoints);
    }

    protected function isValidSolutionResultValue(string $submittedValue): bool
    {
        $submittedValue = str_replace(',', '.', $submittedValue);

        if (is_numeric($submittedValue)) {
            return true;
        }

        if (preg_match('/^[-+]{0,1}\d+\/\d+$/', $submittedValue)) {
            return true;
        }

        return false;
    }

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $answer = $this->getSolutionSubmit();
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($answer, $active_id, $pass, $authorized) {
                foreach ($answer as $key => $value) {
                    $matches = null;
                    if (preg_match("/^result_(\\\$r\\d+)$/", $key, $matches) === false) {
                        $queryResult = "SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND authorized = %s  AND " . $this->db->like('value1', 'clob', $matches[1]);

                        if ($this->getStep() !== null) {
                            $queryResult .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
                        }

                        $result = $this->db->queryF(
                            $queryResult,
                            ['integer', 'integer', 'integer', 'integer'],
                            [$active_id, $pass, $this->getId(), (int) $authorized]
                        );
                        if ($result->numRows()) {
                            while ($row = $this->db->fetchAssoc($result)) {
                                $this->db->manipulateF(
                                    "DELETE FROM tst_solutions WHERE solution_id = %s AND authorized = %s",
                                    ['integer', 'integer'],
                                    [$row['solution_id'], (int) $authorized]
                                );
                            }
                        }

                        $this->saveCurrentSolution($active_id, $pass, $matches[1], str_replace(",", ".", $value), $authorized);
                        continue;
                    }

                    if (preg_match("/^result_(\\\$r\\d+)_unit$/", $key, $matches)) {
                        $queryResultUnit = "SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND authorized = %s AND " . $this->db->like('value1', 'clob', $matches[1] . "_unit");

                        if ($this->getStep() !== null) {
                            $queryResultUnit .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
                        }

                        $result = $this->db->queryF(
                            $queryResultUnit,
                            ['integer', 'integer', 'integer', 'integer'],
                            [$active_id, $pass, $this->getId(), (int) $authorized]
                        );
                        if ($result->numRows()) {
                            while ($row = $this->db->fetchAssoc($result)) {
                                $this->db->manipulateF(
                                    "DELETE FROM tst_solutions WHERE solution_id = %s AND authorized = %s",
                                    ['integer', 'integer'],
                                    [$row['solution_id'], (int) $authorized]
                                );
                            }
                        }

                        $this->saveCurrentSolution($active_id, $pass, $matches[1] . "_unit", $value, $authorized);
                    }
                }
            }
        );

        return true;
    }

    /**
     * @return 	array<'authorized' => bool, 'intermediate' => bool>
     */
    public function lookupForExistingSolutions(int $active_id, int $pass): array
    {
        $return = [
            'authorized' => false,
            'intermediate' => false
        ];

        $query = "
			SELECT authorized, COUNT(*) cnt
			FROM tst_solutions
			WHERE active_fi = " . $this->db->quote($active_id, 'integer') . "
			AND question_fi = " . $this->db->quote($this->getId(), 'integer') . "
			AND pass = " . $this->db->quote($pass, 'integer') . "
			AND value1 like '\$r%'
			AND value2 is not null
			AND value2 <> ''
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
        }

        $query .= "
			GROUP BY authorized
		";

        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            if ($row['authorized']) {
                $return['authorized'] = $row['cnt'] > 0;
            } else {
                $return['intermediate'] = $row['cnt'] > 0;
            }
        }
        return $return;
    }

    public function removeExistingSolutions(int $active_id, int $pass): int
    {
        $query = "
			DELETE FROM tst_solutions
			WHERE active_fi = " . $this->db->quote($active_id, 'integer') . "
			AND question_fi = " . $this->db->quote($this->getId(), 'integer') . "
			AND pass = " . $this->db->quote($pass, 'integer') . "
			AND value1 like '\$r%'
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
        }

        return $this->db->manipulate($query);
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $userSolution = $previewSession->getParticipantsSolution();

        foreach ($this->getSolutionSubmit() as $key => $val) {
            $matches = null;

            if (preg_match("/^result_(\\\$r\\d+)$/", $key, $matches)) {
                $userSolution[$matches[1]] = $val;
            } elseif (preg_match("/^result_(\\\$r\\d+)_unit$/", $key, $matches)) {
                $userSolution[$matches[1] . "_unit"] = $val;
            }
        }

        $previewSession->setParticipantsSolution($userSolution);
    }

    public function getQuestionType(): string
    {
        return "assFormulaQuestion";
    }

    public function getAdditionalTableName(): string
    {
        return "";
    }

    public function getAnswerTableName(): string
    {
        return "";
    }

    public function deleteAnswers(int $question_id): void
    {
        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_var WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_ucat WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM il_qpl_qst_fq_unit WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );
    }

    public function getRTETextWithMediaObjects(): string
    {
        $text = parent::getRTETextWithMediaObjects();
        return $text;
    }

    public function getBestSolution(array $solutions): array
    {
        $user_solution = [];

        foreach ($solutions as $solution_value) {
            if (preg_match('/^(\\\$v\\d+)$/', $solution_value['value1'], $matches)) {
                $user_solution[$matches[1]] = $solution_value['value2'];
                $varObj = $this->getVariable($matches[1]);
                $varObj->setValue($solution_value['value2']);
            } elseif (preg_match('/^(\\\$r\\d+)$/', $solution_value['value1'], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = [];
                }
                $user_solution[$matches[1]]['value'] = $solution_value['value2'];
            } elseif (preg_match('/^(\\\$r\\d+)_unit$/', $solution_value['value1'], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = [];
                }
                $user_solution[$matches[1]]['unit'] = $solution_value['value2'];
            }
        }
        foreach ($this->getResults() as $result) {
            $resVal = $result->calculateFormula($this->getVariables(), $this->getResults(), $this->getId(), false);

            if (is_object($result->getUnit())) {
                $user_solution[$result->getResult()]['unit'] = $result->getUnit()->getId();
                $user_solution[$result->getResult()]['value'] = $resVal;
            } elseif ($result->getUnit() === null) {
                $unit_factor = 1;
                // there is no fix result_unit, any "available unit" is accepted

                $available_units = $result->getAvailableResultUnits(parent::getId());
                $result_name = $result->getResult();

                $check_unit = false;
                if (array_key_exists($result_name, $available_units) &&
                    $available_units[$result_name] !== null) {
                    $check_unit = in_array($user_solution[$result_name]['unit'], $available_units[$result_name]);
                }

                if ($check_unit == true) {
                    //get unit-factor
                    $unit_factor = assFormulaQuestionUnit::lookupUnitFactor($user_solution[$result_name]['unit']);
                }

                try {
                    $user_solution[$result->getResult()]['value'] = ilMath::_div($resVal, $unit_factor, 55);
                } catch (ilMathDivisionByZeroException $ex) {
                    $user_solution[$result->getResult()]['value'] = 0;
                }
            }
            if ($result->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC
                || $result->getResultType() == assFormulaQuestionResult::RESULT_FRAC) {
                $value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($resVal);
                if (is_array($value)) {
                    $user_solution[$result->getResult()]['value'] = $value[0];
                    $user_solution[$result->getResult()]['frac_helper'] = $value[1];
                } else {
                    $user_solution[$result->getResult()]['value'] = $value;
                    $user_solution[$result->getResult()]['frac_helper'] = null;
                }
            } else {
                $user_solution[$result->getResult()]['value'] = round((float) $user_solution[$result->getResult()]['value'], $result->getPrecision());
            }
        }
        return $user_solution;
    }

    public function setId(int $id = -1): void
    {
        parent::setId($id);
        $this->unitrepository->setConsumerId($this->getId());
    }

    public function setUnitrepository(\ilUnitConfigurationRepository $unitrepository): void
    {
        $this->unitrepository = $unitrepository;
    }

    public function getUnitrepository(): ilUnitConfigurationRepository
    {
        return $this->unitrepository;
    }

    /**
     * @return array<string>
     */
    protected function getSolutionSubmit(): array
    {
        $solutionSubmit = [];

        $post = $this->dic->http()->wrapper()->post();

        foreach ($this->getResults() as $index => $a) {
            $key = "result_$index";
            if ($post->has($key)) {
                $value = $post->retrieve(
                    $key,
                    $this->dic->refinery()->kindlyTo()->string()
                );

                $solutionSubmit[$key] = $value;
            }
            if ($post->has($key . "_unit")) {
                $value = $post->retrieve(
                    $key . "_unit",
                    $this->dic->refinery()->kindlyTo()->string()
                );
                $solutionSubmit[$key . "_unit"] = $value;
            }
        }
        return $solutionSubmit;
    }

    public function validateSolutionSubmit(): bool
    {
        foreach ($this->getSolutionSubmit() as $value) {
            if ($value && !$this->isValidSolutionResultValue($value)) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt("err_no_numeric_value"),
                    true
                );
                return false;
            }
        }

        return true;
    }

    public function getOperators(string $expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    public function getExpressionTypes(): array
    {
        return [
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
        ];
    }

    public function getUserQuestionResult(
        int $active_id,
        int $pass
    ): ilUserQuestionResult {
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);
        if ($maxStep > 0) {
            $data = $this->db->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                ["integer", "integer", "integer",'integer'],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                ["integer", "integer", "integer"],
                [$active_id, $pass, $this->getId()]
            );
        }

        while ($row = $this->db->fetchAssoc($data)) {
            if (strstr($row["value1"], '$r') && $row["value2"] != null) {
                $result->addKeyValue(str_replace('$r', "", $row["value1"]), $row["value2"]);
            }
        }

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     *
     * @return array|ASS_AnswerSimple
     */
    public function getAvailableAnswerOptions($index = null)
    {
        if ($index !== null) {
            return $this->getResult('$r' . ($index + 1));
        } else {
            return $this->getResults();
        }
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        return [
            AdditionalInformationGenerator::KEY_QUESTION_TYPE => (string) $this->getQuestionType(),
            AdditionalInformationGenerator::KEY_QUESTION_TITLE => $this->getTitle(),
            AdditionalInformationGenerator::KEY_QUESTION_TEXT => $this->formatSAQuestion($this->getQuestion()),
            AdditionalInformationGenerator::KEY_QUESTION_FORMULA_VARIABLES => $this->buildVariablesForLog(
                $this->getVariables(),
                $additional_info->getNoneTag()
            ),
            AdditionalInformationGenerator::KEY_QUESTION_FORMULA_RESULTS => $this->buildResultsForLog(
                $this->getResults(),
                $additional_info->getNoneTag()
            ),
            AdditionalInformationGenerator::KEY_FEEDBACK => [
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_INCOMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_COMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];
    }

    /**
     *
     * @param array<assFormulaQuestionVariable> $variables
     */
    private function buildVariablesForLog(array $variables, string $none_tag): array
    {
        return array_reduce(
            $variables,
            function (array $c, assFormulaQuestionVariable $v) use ($none_tag): array {
                $c[$v->getVariable()] = [
                    AdditionalInformationGenerator::KEY_QUESTION_LOWER_LIMIT => $v->getRangeMinTxt(),
                    AdditionalInformationGenerator::KEY_QUESTION_UPPER_LIMIT => $v->getRangeMaxTxt(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_PRECISION => $v->getPrecision(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_INTPRECISION => $v->getIntprecision(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_UNIT => $v->getUnit() ?? $none_tag
                ];
                return $c;
            },
            []
        );
    }

    /**
     *
     * @param array<assFormulaQuestionResult> $variables
     */
    private function buildResultsForLog(array $results, string $none_tag): array
    {
        return array_reduce(
            $results,
            function (array $c, assFormulaQuestionResult $r) use ($none_tag): array {
                $c[$r->getResult()] = [
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_RESULT_TYPE => $r->getResultType(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_FORMULA => $r->getFormula(),
                    AdditionalInformationGenerator::KEY_QUESTION_REACHABLE_POINTS => $r->getPoints(),
                    AdditionalInformationGenerator::KEY_QUESTION_LOWER_LIMIT => $r->getRangeMinTxt(),
                    AdditionalInformationGenerator::KEY_QUESTION_UPPER_LIMIT => $r->getRangeMaxTxt(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_TOLERANCE => $r->getTolerance(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_PRECISION => $r->getPrecision(),
                    AdditionalInformationGenerator::KEY_QUESTION_FORMULA_UNIT => $r->getUnit() ?? $none_tag
                ];
                return $c;
            },
            []
        );
    }

    protected function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): array {
        return array_reduce(
            $solution_values,
            function (array $c, array $v) use ($additional_info): array {
                if (str_starts_with($v['value1'], '$v')) {
                    $var = $this->getVariable($v['value1']);
                    if ($var === null) {
                        $c[$v['value1']] = $additional_info->getNoneTag();
                        return $c;
                    }
                    if ($var->getUnit() !== null) {
                        $c[$v['value1']] = $v['value2'] . $var->getUnit()->getUnit();
                        return $c;
                    }
                }

                if (strpos($v['value1'], '_unit')) {
                    $unit = $this->getUnitrepository()->getUnit($v['value2']);
                    $c[$v['value1']] = $unit->getUnit() ?? $additional_info->getNoneTag();
                    return $c;
                }

                $c[$v['value1']] = $v['value2'];
                return $c;
            },
            []
        );
    }

    public function solutionValuesToText(array $solution_values): array
    {
        ksort($solution_values);
        return array_reduce(
            $solution_values,
            function (array $c, array $v): array {
                if (!str_starts_with($v['value1'], '$r')) {
                    return $c;
                }
                if (!strpos($v['value1'], '_unit')) {
                    $c[$v['value1']] = "{$v['value1']} = {$v['value2']}";
                    return $c;
                }
                $k = substr($v['value1'], 0, -5);
                if (array_key_exists($k, $c)) {
                    $c[$k] .= $v['value2'];
                }
                return $c;
            },
            []
        );
    }

    public function getCorrectSolutionForTextOutput(int $active_id, int $pass): array
    {
        $best_solution = $this->getBestSolution($this->getSolutionValues($active_id, $pass));
        return array_map(
            function (string $v) use ($best_solution): string {
                $solution = "{$v} = {$best_solution[$v]['value']}";
                if (isset($best_solution['unit'])) {
                    $solution .= "{$this->unitrepository->getUnit($best_solution['unit'])->getUnit()}";
                }
                return $solution;
            },
            array_keys($best_solution)
        );
    }

    public function getVariablesAsTextArray(int $active_id, int $pass): array
    {
        $variables = $this->getVariableSolutionValuesForPass($active_id, $pass);
        return array_map(
            function (string $v) use ($variables): string {
                $variable = "{$v} = {$variables[$v]}";
                if ($this->getVariable($v)->getUnit() !== null) {
                    $variable .= $this->getVariable($v)->getUnit()->getUnit();
                }
                return $variable;
            },
            array_keys($variables)
        );
    }
}
