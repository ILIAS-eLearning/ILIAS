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

include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionResult.php";
include_once "./Modules/TestQuestionPool/classes/class.assFormulaQuestionVariable.php";
include_once "./Modules/TestQuestionPool/classes/class.ilUnitConfigurationRepository.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php";
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for single choice questions
 * assFormulaQuestion is a class for single choice questions.
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id: class.assFormulaQuestion.php 1236 2010-02-15 15:44:16Z hschottm $
 * @ingroup       ModulesTestQuestionPool
 */
class assFormulaQuestion extends assQuestion implements iQuestionCondition
{
    private array $variables;
    private array $results;
    private array $resultunits;
    private ilUnitConfigurationRepository $unitrepository;

    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->variables = array();
        $this->results = array();
        $this->resultunits = array();
        $this->unitrepository = new ilUnitConfigurationRepository(0);
    }

    public function clearVariables(): void
    {
        $this->variables = array();
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getVariable($variable)
    {
        if (array_key_exists($variable, $this->variables)) {
            return $this->variables[$variable];
        }
        return null;
    }

    public function addVariable($variable): void
    {
        $this->variables[$variable->getVariable()] = $variable;
    }

    public function clearResults(): void
    {
        $this->results = array();
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getResult($result)
    {
        if (array_key_exists($result, $this->results)) {
            return $this->results[$result];
        }
        return null;
    }

    public function addResult($result): void
    {
        $this->results[$result->getResult()] = $result;
    }

    public function addResultUnits($result, $unit_ids): void
    {
        $this->resultunits[$result->getResult()] = array();
        if ((!is_object($result)) || (!is_array($unit_ids))) {
            return;
        }
        foreach ($unit_ids as $id) {
            if (is_numeric($id) && ($id > 0)) {
                $this->resultunits[$result->getResult()][$id] = $this->getUnitrepository()->getUnit($id);
            }
        }
    }

    public function addResultUnit($result, $unit): void
    {
        if (is_object($result) && is_object($unit)) {
            if (!array_key_exists($result->getResult(), $this->resultunits) ||
                !is_array($this->resultunits[$result->getResult()])) {
                $this->resultunits[$result->getResult()] = array();
            }
            $this->resultunits[$result->getResult()][$unit->getId()] = $unit;
        }
    }

    public function getResultUnits($result)
    {
        if (array_key_exists($result->getResult(), $this->resultunits)) {
            return $this->resultunits[$result->getResult()];
        } else {
            return array();
        }
    }

    public function getAllResultUnits(): array
    {
        return $this->resultunits;
    }

    public function hasResultUnit($result, $unit_id): bool
    {
        if (array_key_exists($result->getResult(), $this->resultunits)) {
            if (array_key_exists($unit_id, $this->resultunits[$result->getResult()])) {
                return true;
            }
        }

        return false;
    }

    public function parseQuestionText(): void
    {
        $this->clearResults();
        $this->clearVariables();
        if (preg_match_all("/(\\\$v\\d+)/ims", $this->getQuestion(), $matches)) {
            foreach ($matches[1] as $variable) {
                $varObj = new assFormulaQuestionVariable($variable, 0, 0, null, 0);
                $this->addVariable($varObj);
            }
        }

        if (preg_match_all("/(\\\$r\\d+)/ims", $this->getQuestion(), $rmatches)) {
            foreach ($rmatches[1] as $result) {
                $resObj = new assFormulaQuestionResult($result, null, null, 0, -1, null, 1, 1, true);
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
        $resObjects = array();
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
    public function fetchAllVariables($questionText): array
    {
        $varObjects = array();
        $matches = null;

        if (preg_match_all("/(\\\$v\\d+)/ims", $questionText, $matches)) {
            foreach ($matches[1] as $variableKey) {
                $varObjects[] = $this->getVariable($variableKey);
            }
        }

        return $varObjects;
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

            if (!strlen($userSolution[$varObj->getVariable()])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array $initialVariableSolutionValues
     */
    public function getInitialVariableSolutionValues(): array
    {
        foreach ($this->fetchAllResults($this->getQuestion()) as $resObj) {
            $resObj->findValidRandomVariables($this->getVariables(), $this->getResults());
        }

        $variableSolutionValues = array();

        foreach ($this->fetchAllVariables($this->getQuestion()) as $varObj) {
            $variableSolutionValues[$varObj->getVariable()] = $varObj->getValue();
        }

        return $variableSolutionValues;
    }

    /**
     * @param array $userdata
     * @param bool $graphicalOutput
     * @param bool $forsolution
     * @param bool $result_output
     * @param ilAssQuestionPreviewSession|null $previewSession
     * @return bool|mixed|string
     */
    public function substituteVariables(array $userdata, $graphicalOutput = false, $forsolution = false, $result_output = false)
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
            $val = (strlen($varObj->getValue()) > 8) ? strtoupper(sprintf("%e", $varObj->getValue())) : $varObj->getValue();

            $text = preg_replace("/\\$" . substr($varObj->getVariable(), 1) . "(?![0-9]+)/", $val . " " . $unit . "\\1", $text);
        }

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
                if ($forsolution) {
                    $value = $resObj->calculateFormula($this->getVariables(), $this->getResults(), parent::getId());
                    $value = sprintf("%." . $resObj->getPrecision() . "f", $value);

                    if ($is_frac) {
                        $value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($value);
                        if (is_array($value)) {
                            $frac_helper = $value[1];
                            $value = $value[0];
                        }
                        $value = ' value="' . $value . '"';
                    }

                    $input = '<span class="ilc_qinput_TextInput solutionbox">' . ilLegacyFormElementsUtil::prepareFormOutput(
                        $value
                    ) . '</span>';
                } elseif (is_array($userdata) &&
                    isset($userdata[$result]) &&
                    isset($userdata[$result]["value"])) {
                    $input = $this->generateResultInputHtml($result, $userdata[$result]["value"]);
                } else {
                    $input = $this->generateResultInputHTML($result, '');
                }

                $units = "";
                if (count($this->getResultUnits($resObj)) > 0) {
                    if ($forsolution) {
                        if (is_array($userdata)) {
                            foreach ($this->getResultUnits($resObj) as $unit) {
                                if ($userdata[$result]["unit"] == $unit->getId()) {
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
                        foreach ($this->getResultUnits($resObj) as $unit) {
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
                        if (strlen($frac_helper)) {
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
                        if (strlen($frac_helper)) {
                            $units .= ' &asymp; ' . $frac_helper . ', ';
                        } elseif (is_array($userdata) && isset($userdata[$result]) && strlen($userdata[$result]["frac_helper"])) {
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
                        if ($userdata[$result]["unit"] > 0) {
                            $resunit = $this->getUnitrepository()->getUnit($userdata[$result]["unit"]);
                        }

                        if (isset($userdata[$result]["value"])) {
                            $user_value = $userdata[$result]["value"];
                        }
                    }

                    $template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution_image.html", true, true, 'Modules/TestQuestionPool');

                    if ($resObj->isCorrect($this->getVariables(), $this->getResults(), $user_value, $resunit)) {
                        $template->setCurrentBlock("icon_ok");
                        $template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                        $template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                        $template->parseCurrentBlock();
                    } else {
                        $template->setCurrentBlock("icon_not_ok");
                        $template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                        $template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                        $template->parseCurrentBlock();
                    }
                    $checkSign = $template->get();
                }
                $resultOutput = "";
                if ($result_output) {
                    $template = new ilTemplate("tpl.il_as_qpl_formulaquestion_output_solution_result.html", true, true, 'Modules/TestQuestionPool');

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

    protected function generateResultInputHTML(string $result_key, string $result_value): string
    {
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

    /**
     * Saves a assFormulaQuestion object to a database
     * @access public
     */
    public function saveToDb($original_id = ""): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($original_id == "") {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb($original_id);
        }

        // save variables
        $affectedRows = $ilDB->manipulateF(
            "
		DELETE FROM il_qpl_qst_fq_var
		WHERE question_fi = %s",
            array("integer"),
            array($this->getId())
        );

        foreach ($this->variables as $variable) {
            $next_id = $ilDB->nextId('il_qpl_qst_fq_var');
            $ilDB->insert(
                'il_qpl_qst_fq_var',
                array(
                'variable_id' => array('integer', $next_id),
                'question_fi' => array('integer', $this->getId()),
                'variable' => array('text', $variable->getVariable()),
                'range_min' => array('float', ((strlen($variable->getRangeMin())) ? $variable->getRangeMin() : 0.0)),
                'range_max' => array('float', ((strlen($variable->getRangeMax())) ? $variable->getRangeMax() : 0.0)),
                'unit_fi' => array('integer', (is_object($variable->getUnit()) ? (int) $variable->getUnit()->getId() : 0)),
                'varprecision' => array('integer', (int) $variable->getPrecision()),
                'intprecision' => array('integer', (int) $variable->getIntprecision()),
                'range_min_txt' => array('text', $variable->getRangeMinTxt()),
                'range_max_txt' => array('text', $variable->getRangeMaxTxt())
            )
            );
        }
        // save results
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res WHERE question_fi = %s",
            array("integer"),
            array($this->getId())
        );

        foreach ($this->results as $result) {
            $next_id = $ilDB->nextId('il_qpl_qst_fq_res');
            if (is_object($result->getUnit())) {
                $tmp_result_unit = $result->getUnit()->getId();
            } else {
                $tmp_result_unit = null;
            }

            $formula = str_replace(",", ".", $result->getFormula());

            $ilDB->insert("il_qpl_qst_fq_res", array(
                "result_id" => array("integer", $next_id),
                "question_fi" => array("integer", $this->getId()),
                "result" => array("text", $result->getResult()),
                "range_min" => array("float", ((strlen($result->getRangeMin())) ? $result->getRangeMin() : 0)),
                "range_max" => array("float", ((strlen($result->getRangeMax())) ? $result->getRangeMax() : 0)),
                "tolerance" => array("float", ((strlen($result->getTolerance())) ? $result->getTolerance() : 0)),
                "unit_fi" => array("integer", (int) $tmp_result_unit),
                "formula" => array("clob", $formula),
                "resprecision" => array("integer", $result->getPrecision()),
                "rating_simple" => array("integer", ($result->getRatingSimple()) ? 1 : 0),
                "rating_sign" => array("float", ($result->getRatingSimple()) ? 0 : $result->getRatingSign()),
                "rating_value" => array("float", ($result->getRatingSimple()) ? 0 : $result->getRatingValue()),
                "rating_unit" => array("float", ($result->getRatingSimple()) ? 0 : $result->getRatingUnit()),
                "points" => array("float", $result->getPoints()),
                "result_type" => array('integer', (int) $result->getResultType()),
                "range_min_txt" => array("text", $result->getRangeMinTxt()),
                "range_max_txt" => array("text", $result->getRangeMaxTxt())

            ));
        }
        // save result units
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
            array("integer"),
            array($this->getId())
        );
        foreach ($this->results as $result) {
            foreach ($this->getResultUnits($result) as $unit) {
                $next_id = $ilDB->nextId('il_qpl_qst_fq_res_unit');
                $affectedRows = $ilDB->manipulateF(
                    "INSERT INTO il_qpl_qst_fq_res_unit (result_unit_id, question_fi, result, unit_fi) VALUES (%s, %s, %s, %s)",
                    array('integer', 'integer', 'text', 'integer'),
                    array(
                        $next_id,
                        $this->getId(),
                        $result->getResult(),
                        $unit->getId()
                    )
                );
            }
        }

        parent::saveToDb();
    }

    /**
     * Loads a assFormulaQuestion object from a database
     * @param integer $question_id A unique key which defines the question in the database
     */
    public function loadFromDb($question_id): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT qpl_questions.* FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
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

            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));

            // load variables
            $result = $ilDB->queryF(
                "SELECT * FROM il_qpl_qst_fq_var WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $varObj = new assFormulaQuestionVariable($data["variable"], $data["range_min"], $data["range_max"], $this->getUnitrepository()->getUnit($data["unit_fi"]), $data["varprecision"], $data["intprecision"]);
                    $varObj->setRangeMinTxt($data['range_min_txt']);
                    $varObj->setRangeMaxTxt($data['range_max_txt']);
                    $this->addVariable($varObj);
                }
            }
            // load results
            $result = $ilDB->queryF(
                "SELECT * FROM il_qpl_qst_fq_res WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $resObj = new assFormulaQuestionResult($data["result"], $data["range_min"], $data["range_max"], $data["tolerance"], $this->getUnitrepository()->getUnit($data["unit_fi"]), $data["formula"], $data["points"], $data["resprecision"], $data["rating_simple"], $data["rating_sign"], $data["rating_value"], $data["rating_unit"]);
                    $resObj->setResultType($data['result_type']);
                    $resObj->setRangeMinTxt($data['range_min_txt']);
                    $resObj->setRangeMaxTxt($data['range_max_txt']);
                    $this->addResult($resObj);
                }
            }

            // load result units
            $result = $ilDB->queryF(
                "SELECT * FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $unit = $this->getUnitrepository()->getUnit($data["unit_fi"]);
                    $resObj = $this->getResult($data["result"]);
                    $this->addResultUnit($resObj, $unit);
                }
            }
        }
        parent::loadFromDb($question_id);
    }

    /**
     * Duplicates an assFormulaQuestion
     * @access public
     */
    public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null): int
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
        }
        // duplicate the question in database
        $this_id = $this->getId();
        $thisObjId = $this->getObjId();

        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;

        if ((int) $testObjId > 0) {
            $clone->setObjId($testObjId);
        }

        if ($title) {
            $clone->setTitle($title);
        }

        if ($author) {
            $clone->setAuthor($author);
        }
        if ($owner) {
            $clone->setOwner($owner);
        }

        if ($for_test) {
            $clone->saveToDb($original_id);
        } else {
            $clone->saveToDb();
        }

        $clone->unitrepository->cloneUnits($this_id, $clone->getId());

        // copy question page content
        $clone->copyPageOfQuestion($this_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);
        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
     * Copies an assFormulaQuestion object
     * @access public
     */
    public function copyObject($target_questionpool_id, $title = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }
        // duplicate the question in database
        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        $source_questionpool_id = $this->getObjId();
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb();

        $clone->unitrepository->cloneUnits($original_id, $clone->getId());

        // copy question page content
        $clone->copyPageOfQuestion($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }

        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");

        $sourceQuestionId = $this->id;
        $sourceParentId = $this->getObjId();

        // duplicate the question in database
        $clone = $this;
        $clone->id = -1;

        $clone->setObjId($targetParentId);

        if ($targetQuestionTitle) {
            $clone->setTitle($targetQuestionTitle);
        }

        $clone->saveToDb();
        // copy question page content
        $clone->copyPageOfQuestion($sourceQuestionId);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
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

    /**
     * Returns the points, a learner has reached answering the question
     * The points are calculated from the given answers.
     *
     * @param integer $user_id The database ID of the learner
     * @param integer $test_id The database Id of the test containing the question
     * @access public
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false): int
    {
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $solutions = $this->getSolutionValues($active_id, $pass, $authorizedSolution);
        $user_solution = array();
        foreach ($solutions as $idx => $solution_value) {
            if (preg_match("/^(\\\$v\\d+)$/", $solution_value["value1"], $matches)) {
                $user_solution[$matches[1]] = $solution_value["value2"];
                $varObj = $this->getVariable($solution_value["value1"]);
                $varObj->setValue($solution_value["value2"]);
            } elseif (preg_match("/^(\\\$r\\d+)$/", $solution_value["value1"], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = array();
                }
                $user_solution[$matches[1]]["value"] = $solution_value["value2"];
            } elseif (preg_match("/^(\\\$r\\d+)_unit$/", $solution_value["value1"], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = array();
                }
                $user_solution[$matches[1]]["unit"] = $solution_value["value2"];
            }
        }
        //vd($this->getResults());
        $points = 0;
        foreach ($this->getResults() as $result) {
            //vd($user_solution[$result->getResult()]["value"]);
            $points += $result->getReachedPoints(
                $this->getVariables(),
                $this->getResults(),
                $user_solution[$result->getResult()]["value"] ?? '',
                $user_solution[$result->getResult()]["unit"] ?? '',
                $this->unitrepository->getUnits()
            );
        }

        return $points;
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

    protected function isValidSolutionResultValue($submittedValue): bool
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

    /**
     * Saves the learners input of the question to the database
     * @param integer $test_id The database id of the test containing this question
     * @return boolean Indicates the save status (true if saved successful, false otherwise)
     * @access public
     * @see    $answers
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $entered_values = false;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $ilDB, $active_id, $pass, $authorized) {
            $solutionSubmit = $this->getSolutionSubmit();
            foreach ($solutionSubmit as $key => $value) {
                $matches = null;
                if (preg_match("/^result_(\\\$r\\d+)$/", $key, $matches)) {
                    if (strlen($value)) {
                        $entered_values = true;
                    }

                    $queryResult = "SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND authorized = %s  AND " . $ilDB->like('value1', 'clob', $matches[1]);

                    if ($this->getStep() !== null) {
                        $queryResult .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
                    }

                    $result = $ilDB->queryF(
                        $queryResult,
                        array('integer', 'integer', 'integer', 'integer'),
                        array($active_id, $pass, $this->getId(), (int) $authorized)
                    );
                    if ($result->numRows()) {
                        while ($row = $ilDB->fetchAssoc($result)) {
                            $ilDB->manipulateF(
                                "DELETE FROM tst_solutions WHERE solution_id = %s AND authorized = %s",
                                array('integer', 'integer'),
                                array($row['solution_id'], (int) $authorized)
                            );
                        }
                    }

                    $this->saveCurrentSolution($active_id, $pass, $matches[1], str_replace(",", ".", $value), $authorized);
                } elseif (preg_match("/^result_(\\\$r\\d+)_unit$/", $key, $matches)) {
                    $queryResultUnit = "SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND authorized = %s AND " . $ilDB->like('value1', 'clob', $matches[1] . "_unit");

                    if ($this->getStep() !== null) {
                        $queryResultUnit .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
                    }

                    $result = $ilDB->queryF(
                        $queryResultUnit,
                        array('integer', 'integer', 'integer', 'integer'),
                        array($active_id, $pass, $this->getId(), (int) $authorized)
                    );
                    if ($result->numRows()) {
                        while ($row = $ilDB->fetchAssoc($result)) {
                            $ilDB->manipulateF(
                                "DELETE FROM tst_solutions WHERE solution_id = %s AND authorized = %s",
                                array('integer', 'integer'),
                                array($row['solution_id'], (int) $authorized)
                            );
                        }
                    }

                    $this->saveCurrentSolution($active_id, $pass, $matches[1] . "_unit", $value, $authorized);
                }
            }
        });

        if ($entered_values) {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        } else {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_not_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        }

        return true;
    }

    // fau: testNav - overridden function lookupForExistingSolutions (specific for formula question: don't lookup variables)
    /**
     * Lookup if an authorized or intermediate solution exists
     * @param 	int 		$activeId
     * @param 	int 		$pass
     * @return 	array		['authorized' => bool, 'intermediate' => bool]
     */
    public function lookupForExistingSolutions(int $activeId, int $pass): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $return = array(
            'authorized' => false,
            'intermediate' => false
        );

        $query = "
			SELECT authorized, COUNT(*) cnt
			FROM tst_solutions
			WHERE active_fi = " . $ilDB->quote($activeId, 'integer') . "
			AND question_fi = " . $ilDB->quote($this->getId(), 'integer') . "
			AND pass = " . $ilDB->quote($pass, 'integer') . "
			AND value1 like '\$r%'
			AND value2 is not null
			AND value2 <> ''
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
        }

        $query .= "
			GROUP BY authorized
		";

        $result = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['authorized']) {
                $return['authorized'] = $row['cnt'] > 0;
            } else {
                $return['intermediate'] = $row['cnt'] > 0;
            }
        }
        return $return;
    }
    // fau.

    // fau: testNav - Remove an existing solution (specific for formula question: don't delete variables)
    /**
     * Remove an existing solution without removing the variables
     * @param 	int 		$activeId
     * @param 	int 		$pass
     * @return int
     */
    public function removeExistingSolutions(int $activeId, int $pass): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			DELETE FROM tst_solutions
			WHERE active_fi = " . $ilDB->quote($activeId, 'integer') . "
			AND question_fi = " . $ilDB->quote($this->getId(), 'integer') . "
			AND pass = " . $ilDB->quote($pass, 'integer') . "
			AND value1 like '\$r%'
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
        }

        return $ilDB->manipulate($query);
    }
    // fau.

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

    /**
     * Returns the question type of the question
     * @return string The question type of the question
     */
    public function getQuestionType(): string
    {
        return "assFormulaQuestion";
    }

    /**
     * Returns the name of the additional question data table in the database
     * @return string The additional table name
     */
    public function getAdditionalTableName(): string
    {
        return "";
    }

    /**
     * Returns the name of the answer table in the database
     * @return string The answer table name
     */
    public function getAnswerTableName(): string
    {
        return "";
    }

    /**
     * Deletes datasets from answers tables
     * @param integer $question_id The question id which should be deleted in the answers table
     * @access public
     */
    public function deleteAnswers($question_id): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_var WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_res_unit WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_ucat WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM il_qpl_qst_fq_unit WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
    }

    /**
     * Collects all text in the question which could contain media objects
     * which were created with the Rich Text Editor
     */
    public function getRTETextWithMediaObjects(): string
    {
        $text = parent::getRTETextWithMediaObjects();
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($solution as $solutionvalue) {
            $worksheet->setCell($startrow + $i, 0, $solutionvalue["value1"]);
            $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
            if (strpos($solutionvalue["value1"], "_unit")) {
                $unit = $this->getUnitrepository()->getUnit($solutionvalue["value2"]);
                if (is_object($unit)) {
                    $worksheet->setCell($startrow + $i, 1, $unit->getUnit());
                }
            } else {
                $worksheet->setCell($startrow + $i, 1, $solutionvalue["value2"]);
            }
            if (preg_match("/(\\\$v\\d+)/", $solutionvalue["value1"], $matches)) {
                $var = $this->getVariable($solutionvalue["value1"]);
                if (is_object($var) && (is_object($var->getUnit()))) {
                    $worksheet->setCell($startrow + $i, 2, $var->getUnit()->getUnit());
                }
            }
            $i++;
        }

        return $startrow + $i + 1;
    }

    /**
     * Returns the best solution for a given pass of a participant
     * @return array An associated array containing the best solution
     * @access public
     */
    public function getBestSolution($solutions): array
    {
        $user_solution = array();

        foreach ($solutions as $idx => $solution_value) {
            if (preg_match("/^(\\\$v\\d+)$/", $solution_value["value1"], $matches)) {
                $user_solution[$matches[1]] = $solution_value["value2"];
                $varObj = $this->getVariable($matches[1]);
                $varObj->setValue($solution_value["value2"]);
            } elseif (preg_match("/^(\\\$r\\d+)$/", $solution_value["value1"], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = array();
                }
                $user_solution[$matches[1]]["value"] = $solution_value["value2"];
            } elseif (preg_match("/^(\\\$r\\d+)_unit$/", $solution_value["value1"], $matches)) {
                if (!array_key_exists($matches[1], $user_solution)) {
                    $user_solution[$matches[1]] = array();
                }
                $user_solution[$matches[1]]["unit"] = $solution_value["value2"];
            }
        }
        foreach ($this->getResults() as $result) {
            $resVal = $result->calculateFormula($this->getVariables(), $this->getResults(), parent::getId(), false);

            if (is_object($result->getUnit())) {
                $user_solution[$result->getResult()]["unit"] = $result->getUnit()->getId();
                $user_solution[$result->getResult()]["value"] = $resVal;
            } elseif ($result->getUnit() == null) {
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
                    $user_solution[$result->getResult()]["value"] = ilMath::_div($resVal, $unit_factor, 55);
                } catch (ilMathDivisionByZeroException $ex) {
                    $user_solution[$result->getResult()]["value"] = 0;
                }
            }
            if ($result->getResultType() == assFormulaQuestionResult::RESULT_CO_FRAC
                || $result->getResultType() == assFormulaQuestionResult::RESULT_FRAC) {
                $value = assFormulaQuestionResult::convertDecimalToCoprimeFraction($resVal);
                if (is_array($value)) {
                    $user_solution[$result->getResult()]["value"] = $value[0];
                    $user_solution[$result->getResult()]["frac_helper"] = $value[1];
                } else {
                    $user_solution[$result->getResult()]["value"] = $value;
                    $user_solution[$result->getResult()]["frac_helper"] = null;
                }
            } else {
                $user_solution[$result->getResult()]["value"] = round($user_solution[$result->getResult()]["value"], $result->getPrecision());
                /*
                $user_solution[$result->getResult()]["value"] = ilMath::_div(
                    $user_solution[$result->getResult()]["value"],
                    1,
                    $result->getPrecision()
                );
                */
            }
        }
        return $user_solution;
    }

    public function setId($id = -1): void
    {
        parent::setId($id);
        $this->unitrepository->setConsumerId($this->getId());
    }

    /**
     * @param \ilUnitConfigurationRepository $unitrepository
     */
    public function setUnitrepository($unitrepository): void
    {
        $this->unitrepository = $unitrepository;
    }

    /**
     * @return \ilUnitConfigurationRepository
     */
    public function getUnitrepository(): ilUnitConfigurationRepository
    {
        return $this->unitrepository;
    }

    /**
     * @return array
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
        foreach ($this->getSolutionSubmit() as $key => $value) {
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

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
     *
     * @internal param string $expression_type
     * @return array
     */
    public function getOperators($expression): array
    {
        require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    /**
     * Get all available expression types for a specific question
     * @return array
     */
    public function getExpressionTypes(): array
    {
        return array(
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
        );
    }

    /**
     * Get the user solution for a question by active_id and the test pass
     *
     * @param int $active_id
     * @param int $pass
     *
     * @return ilUserQuestionResult
     */
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);

        if ($maxStep !== null) {
            $data = $ilDB->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                array("integer", "integer", "integer",'integer'),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        while ($row = $ilDB->fetchAssoc($data)) {
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
}
