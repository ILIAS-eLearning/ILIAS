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
* TableGUI class for evaluation of all users
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup components\ILIASTest
*
*/
class ilEvaluationAllTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjTestGUI|ilTestEvaluationGUI $parent_obj,
        string $parent_cmd,
        private ilSetting $settings,
        private bool $anonymity = false,
        private bool $offeringQuestionHintsEnabled = false
    ) {
        $this->setId('tst_eval_all');

        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('evaluation_all');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("name"), "name", "");
        $this->addColumn($this->lng->txt("login"), "login", "");

        if (!$this->anonymity) {
            foreach ($this->getSelectedColumns() as $c) {
                if (strcmp($c, 'gender') == 0) {
                    $this->addColumn($this->lng->txt("gender"), 'gender', '');
                }
                if (strcmp($c, 'email') == 0) {
                    $this->addColumn($this->lng->txt("email"), 'email', '');
                }
                if (strcmp($c, 'exam_id') == 0 && $this->parent_obj->getTestObject()->isShowExamIdInTestResultsEnabled()) {
                    $this->addColumn($this->lng->txt("exam_id_label"), 'exam_id', '');
                }
                if (strcmp($c, 'institution') == 0) {
                    $this->addColumn($this->lng->txt("institution"), 'institution', '');
                }
                if (strcmp($c, 'street') == 0) {
                    $this->addColumn($this->lng->txt("street"), 'street', '');
                }
                if (strcmp($c, 'city') == 0) {
                    $this->addColumn($this->lng->txt("city"), 'city', '');
                }
                if (strcmp($c, 'zipcode') == 0) {
                    $this->addColumn($this->lng->txt("zipcode"), 'zipcode', '');
                }

                if ($this->isFieldEnabledEnoughByAdministration('country') && $c == 'country') {
                    $this->addColumn($this->lng->txt("country"), 'country', '');
                }

                if ($this->isFieldEnabledEnoughByAdministration('sel_country') && $c == 'sel_country') {
                    $this->addColumn($this->lng->txt("country"), 'sel_country', '');
                }

                if (strcmp($c, 'department') == 0) {
                    $this->addColumn($this->lng->txt("department"), 'department', '');
                }
                if (strcmp($c, 'matriculation') == 0) {
                    $this->addColumn($this->lng->txt("matriculation"), 'matriculation', '');
                }
            }
        }

        $this->addColumn($this->lng->txt("tst_reached_points"), "reached", "");

        if ($this->offeringQuestionHintsEnabled) {
            $this->addColumn($this->lng->txt("tst_question_hints_requested_hint_count_header"), "hint_count", "");
        }

        $this->addColumn($this->lng->txt("tst_mark"), "mark", "");

        $this->addColumn($this->lng->txt("tst_answered_questions"), "answered", "");
        $this->addColumn($this->lng->txt("working_time"), "working_time", "");
        $this->addColumn($this->lng->txt("detailed_evaluation"), "", "");

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));
        $this->setRowTemplate("tpl.table_evaluation_all.html", "components/ILIAS/Test");
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        $this->enable('sort');
        $this->enable('header');

        $this->setFilterCommand('filterEvaluation');
        $this->setResetCommand('resetfilterEvaluation');
        $this->initFilter();

        if ($this->isFieldEnabledEnoughByAdministration('sel_country')) {
            $this->lng->loadLanguageModule('meta');
        }
    }

    /**
    * Should this field be sorted numeric?
    * @return	boolean		numeric ordering; default is false
    */
    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'name':
                if ($this->anonymity) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'reached':
            case 'hint_count':
            case 'exam_id':
            case 'answered':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    public function getSelectableColumns(): array
    {
        $cols = [];
        if (!$this->anonymity) {
            $cols["gender"] = array(
                "txt" => $this->lng->txt("gender"),
                "default" => false
            );
            $cols["email"] = array(
                "txt" => $this->lng->txt("email"),
                "default" => false
            );
            $cols["institution"] = array(
                "txt" => $this->lng->txt("institution"),
                "default" => false
            );
            $cols["street"] = array(
                "txt" => $this->lng->txt("street"),
                "default" => false
            );
            $cols["city"] = array(
                "txt" => $this->lng->txt("city"),
                "default" => false
            );
            $cols["zipcode"] = array(
                "txt" => $this->lng->txt("zipcode"),
                "default" => false
            );
            if ($this->isFieldEnabledEnoughByAdministration('country')) {
                $cols["country"] = array(
                    "txt" => $this->lng->txt("country"),
                    "default" => false
                );
            }
            if ($this->isFieldEnabledEnoughByAdministration('sel_country')) {
                $cols["sel_country"] = array(
                    "txt" => $this->lng->txt("country"),
                    "default" => false
                );
            }
            $cols["department"] = array(
                "txt" => $this->lng->txt("department"),
                "default" => false
            );
            $cols["matriculation"] = array(
                "txt" => $this->lng->txt("matriculation"),
                "default" => false
            );
            if ($this->parent_obj->getObject()->isShowExamIdInTestResultsEnabled()) {
                $cols["exam_id"] = array(
                    "txt" => $this->lng->txt("exam_id_label"),
                    "default" => false
                );
            }
        }
        return $cols;
    }

    /**
    * Init filter
    */
    public function initFilter(): void
    {
        $ti = new ilTextInputGUI($this->lng->txt("name"), "name");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]*$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();

        // group
        $ti = new ilTextInputGUI($this->lng->txt("grp"), "group");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]*$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();

        // course
        $ti = new ilTextInputGUI($this->lng->txt("course"), "course");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]*$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();

        $si = new ilCheckboxInputGUI($this->lng->txt("passed_only"), "passed_only");
        $this->addFilterItem($si);
        $si->readFromSession();
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("NAME", $a_set['name']);
        $this->tpl->setVariable("LOGIN", $a_set['login']);
        foreach ($this->getSelectedColumns() as $c) {
            if (!$this->anonymity) {
                if (strcmp($c, 'gender') == 0) {
                    $this->tpl->setCurrentBlock('gender');
                    $this->tpl->setVariable("GENDER", strlen($a_set['gender']) ? $this->lng->txt('gender_' . $a_set['gender']) : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'email') == 0) {
                    $this->tpl->setCurrentBlock('email');
                    $this->tpl->setVariable("EMAIL", strlen($a_set['email']) ? $a_set['email'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'institution') == 0) {
                    $this->tpl->setCurrentBlock('institution');
                    $this->tpl->setVariable("INSTITUTION", strlen($a_set['institution']) ? $a_set['institution'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'street') == 0) {
                    $this->tpl->setCurrentBlock('street');
                    $this->tpl->setVariable("STREET", strlen($a_set['street']) ? $a_set['street'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'city') == 0) {
                    $this->tpl->setCurrentBlock('city');
                    $this->tpl->setVariable("CITY", strlen($a_set['city']) ? $a_set['city'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'zipcode') == 0) {
                    $this->tpl->setCurrentBlock('zipcode');
                    $this->tpl->setVariable("ZIPCODE", strlen($a_set['zipcode']) ? $a_set['zipcode'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if ($this->isFieldEnabledEnoughByAdministration('country') && $c == 'country') {
                    $this->tpl->setCurrentBlock('country');
                    $this->tpl->setVariable("COUNTRY", strlen($a_set['country']) ? $a_set['country'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if ($this->isFieldEnabledEnoughByAdministration('sel_country') && $c == 'sel_country') {
                    $this->tpl->setCurrentBlock('country');
                    $this->tpl->setVariable("COUNTRY", strlen($a_set['sel_country']) ? $this->getCountryTranslation($a_set['sel_country']) : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'department') == 0) {
                    $this->tpl->setCurrentBlock('department');
                    $this->tpl->setVariable("DEPARTMENT", strlen($a_set['department']) ? $a_set['department'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'matriculation') == 0) {
                    $this->tpl->setCurrentBlock('matriculation');
                    $this->tpl->setVariable("MATRICULATION", strlen($a_set['matriculation']) ? $a_set['matriculation'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'exam_id') == 0 && $this->parent_obj->object->isShowExamIdInTestResultsEnabled()) {
                    $this->tpl->setCurrentBlock('exam_id');
                    $examId = is_string($a_set['exam_id']) && strlen($a_set['exam_id']) ? $a_set['exam_id'] : '&nbsp;';
                    $this->tpl->setVariable('EXAM_ID', $examId);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
        $reachedPercent = !$a_set['max'] ? 0 : $a_set['reached'] / $a_set['max'] * 100;
        $reached = $a_set['reached'] . " " . strtolower($this->lng->txt("of")) . " " . $a_set['max'] . " (" . sprintf("%2.2f", $reachedPercent) . " %)";
        $this->tpl->setVariable("REACHED", $reached);

        if ($this->offeringQuestionHintsEnabled) {
            $this->tpl->setVariable("HINT_COUNT", $a_set['hint_count']);
        }

        $a_set['answered'] = $a_set['questions_worked_through'] . " " . strtolower($this->lng->txt("of")) . " " . $a_set['number_of_questions'] . " (" . sprintf("%2.2f", $a_set['answered']) . " %" . ")";

        $this->tpl->setVariable("MARK", $a_set['mark']);
        $this->tpl->setVariable("ANSWERED", $a_set['answered']);
        $this->tpl->setVariable("WORKING_TIME", $a_set['working_time']);
        $this->tpl->setVariable("DETAILED", $a_set['details']);
    }

    public function getSelectedColumns(): array
    {
        $scol = parent::getSelectedColumns();

        $cols = $this->getSelectableColumns();
        if (!is_array($cols)) {
            $cols = array();
        }

        $fields_to_unset = array_diff(array_keys($scol), array_keys($cols));

        foreach ($fields_to_unset as $key) {
            unset($scol[$key]);
        }

        return $scol;
    }

    protected function getCountryTranslation($countryCode)
    {
        return $this->lng->txt('meta_c_' . $countryCode);
    }

    protected function isFieldEnabledEnoughByAdministration($fieldIdentifier): bool
    {
        if ($this->settings->get("usr_settings_hide_" . $fieldIdentifier)) { // visible
            return false;
        }

        if (!$this->settings->get('usr_settings_visib_reg_' . $fieldIdentifier)) { // visib_reg
            return false;
        }

        if (!$this->settings->get('usr_settings_visib_lua_' . $fieldIdentifier)) { // visib_lua
            return false;
        }

        if ($this->settings->get("usr_settings_disable_" . $fieldIdentifier)) { // changeable
            return false;
        }

        if (!$this->settings->get('usr_settings_changeable_lua_' . $fieldIdentifier)) { // changeable_lua
            return false;
        }

        return true;
    }
}
