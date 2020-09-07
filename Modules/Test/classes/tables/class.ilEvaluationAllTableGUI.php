<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for evaluation of all users
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
*
*/
class ilEvaluationAllTableGUI extends ilTable2GUI
{
    protected $anonymity;
    
    /**
     * flag for offering question hints
     *
     * @var boolean
     */
    protected $offeringQuestionHintsEnabled = null;

    public function __construct($a_parent_obj, $a_parent_cmd, $anonymity = false, $offeringQuestionHintsEnabled = false)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $this->setId("tst_eval_all");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->setFormName('evaluation_all');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($lng->txt("name"), "name", "");
        $this->addColumn($lng->txt("login"), "login", "");
        
        $this->anonymity = $anonymity;
        $this->offeringQuestionHintsEnabled = $offeringQuestionHintsEnabled;

        if (!$this->anonymity) {
            foreach ($this->getSelectedColumns() as $c) {
                if (strcmp($c, 'gender') == 0) {
                    $this->addColumn($this->lng->txt("gender"), 'gender', '');
                }
                if (strcmp($c, 'email') == 0) {
                    $this->addColumn($this->lng->txt("email"), 'email', '');
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
        
        $this->addColumn($lng->txt("tst_reached_points"), "reached", "");
        
        if ($this->offeringQuestionHintsEnabled) {
            $this->addColumn($lng->txt("tst_question_hints_requested_hint_count_header"), "hint_count", "");
        }
        
        $this->addColumn($lng->txt("tst_mark"), "mark", "");
        
        if ($this->parent_obj->object->getECTSOutput()) {
            foreach ($this->getSelectedColumns() as $c) {
                if (strcmp($c, 'ects_grade') == 0) {
                    $this->addColumn($this->lng->txt("ects_grade"), 'ects_grade', '');
                }
            }
        }
        $this->addColumn($lng->txt("tst_answered_questions"), "answered", "");
        $this->addColumn($lng->txt("working_time"), "working_time", "");
        $this->addColumn($lng->txt("detailed_evaluation"), "details", "");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.table_evaluation_all.html", "Modules/Test");
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
    *
    * @return	boolean		numeric ordering; default is false
    */
    public function numericOrdering($a_field)
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
            case 'answered':
                return true;
                break;
            default:
                return false;
                break;
        }
    }
    
    public function getSelectableColumns()
    {
        global $DIC;
        $lng = $DIC['lng'];
        if (!$this->anonymity) {
            $cols["gender"] = array(
                "txt" => $lng->txt("gender"),
                "default" => false
            );
            $cols["email"] = array(
                "txt" => $lng->txt("email"),
                "default" => false
            );
            $cols["institution"] = array(
                "txt" => $lng->txt("institution"),
                "default" => false
            );
            $cols["street"] = array(
                "txt" => $lng->txt("street"),
                "default" => false
            );
            $cols["city"] = array(
                "txt" => $lng->txt("city"),
                "default" => false
            );
            $cols["zipcode"] = array(
                "txt" => $lng->txt("zipcode"),
                "default" => false
            );
            if ($this->isFieldEnabledEnoughByAdministration('country')) {
                $cols["country"] = array(
                    "txt" => $lng->txt("country"),
                    "default" => false
                );
            }
            if ($this->isFieldEnabledEnoughByAdministration('sel_country')) {
                $cols["sel_country"] = array(
                    "txt" => $lng->txt("country"),
                    "default" => false
                );
            }
            $cols["department"] = array(
                "txt" => $lng->txt("department"),
                "default" => false
            );
            $cols["matriculation"] = array(
                "txt" => $lng->txt("matriculation"),
                "default" => false
            );
        }
        if ($this->parent_obj->object->getECTSOutput()) {
            $cols["ects_grade"] = array(
                "txt" => $lng->txt("ects_grade"),
                "default" => false
            );
        }
        return $cols;
    }

    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        
        // name
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("name"), "name");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]*$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["name"] = $ti->getValue();
        
        // group
        $ti = new ilTextInputGUI($lng->txt("grp"), "group");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]*$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["group"] = $ti->getValue();
        
        // course
        $ti = new ilTextInputGUI($lng->txt("course"), "course");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]*$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["course"] = $ti->getValue();
        
        // passed tests
        include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
        $si = new ilCheckboxInputGUI($this->lng->txt("passed_only"), "passed_only");
        //		$si->setOptionTitle();
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["passedonly"] = $si->getValue();
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($data)
    {
        $this->tpl->setVariable("NAME", $data['name']);
        $this->tpl->setVariable("LOGIN", $data['login']);
        foreach ($this->getSelectedColumns() as $c) {
            if (!$this->anonymity) {
                if (strcmp($c, 'gender') == 0) {
                    $this->tpl->setCurrentBlock('gender');
                    $this->tpl->setVariable("GENDER", $this->lng->txt('gender_' . $data['gender']));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'email') == 0) {
                    $this->tpl->setCurrentBlock('email');
                    $this->tpl->setVariable("EMAIL", strlen($data['email']) ? $data['email'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'institution') == 0) {
                    $this->tpl->setCurrentBlock('institution');
                    $this->tpl->setVariable("INSTITUTION", strlen($data['institution']) ? $data['institution'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'street') == 0) {
                    $this->tpl->setCurrentBlock('street');
                    $this->tpl->setVariable("STREET", strlen($data['street']) ? $data['street'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'city') == 0) {
                    $this->tpl->setCurrentBlock('city');
                    $this->tpl->setVariable("CITY", strlen($data['city']) ? $data['city'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'zipcode') == 0) {
                    $this->tpl->setCurrentBlock('zipcode');
                    $this->tpl->setVariable("ZIPCODE", strlen($data['zipcode']) ? $data['zipcode'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if ($this->isFieldEnabledEnoughByAdministration('country') && $c == 'country') {
                    $this->tpl->setCurrentBlock('country');
                    $this->tpl->setVariable("COUNTRY", strlen($data['country']) ? $data['country'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if ($this->isFieldEnabledEnoughByAdministration('sel_country') && $c == 'sel_country') {
                    $this->tpl->setCurrentBlock('country');
                    $this->tpl->setVariable("COUNTRY", strlen($data['sel_country']) ? $this->getCountryTranslation($data['sel_country']) : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'department') == 0) {
                    $this->tpl->setCurrentBlock('department');
                    $this->tpl->setVariable("DEPARTMENT", strlen($data['department']) ? $data['department'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'matriculation') == 0) {
                    $this->tpl->setCurrentBlock('matriculation');
                    $this->tpl->setVariable("MATRICULATION", strlen($data['matriculation']) ? $data['matriculation'] : '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
            }
            if ($this->parent_obj->object->getECTSOutput()) {
                if (strcmp($c, 'ects_grade') == 0) {
                    $this->tpl->setCurrentBlock('ects_grade');
                    $this->tpl->setVariable("ECTS_GRADE", $data['ects_grade']);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
        $reachedPercent = !$data['max'] ? 0 : $data['reached'] / $data['max'] * 100;
        $reached = $data['reached'] . " " . strtolower($this->lng->txt("of")) . " " . $data['max'] . " (" . sprintf("%2.2f", $reachedPercent) . " %)";
        $this->tpl->setVariable("REACHED", $reached);
        
        if ($this->offeringQuestionHintsEnabled) {
            $this->tpl->setVariable("HINT_COUNT", $data['hint_count']);
        }

        $data['answered'] = $data['questions_worked_through'] . " " . strtolower($this->lng->txt("of")) . " " . $data['number_of_questions'] . " (" . sprintf("%2.2f", $data['answered']) . " %" . ")";

        $this->tpl->setVariable("MARK", $data['mark']);
        $this->tpl->setVariable("ANSWERED", $data['answered']);
        $this->tpl->setVariable("WORKING_TIME", $data['working_time']);
        $this->tpl->setVariable("DETAILED", $data['details']);
    }
    
    public function getSelectedColumns()
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

    protected function isFieldEnabledEnoughByAdministration($fieldIdentifier)
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        
        if ($ilSetting->get("usr_settings_hide_" . $fieldIdentifier)) { // visible
            return false;
        }

        if (!$ilSetting->get('usr_settings_visib_reg_' . $fieldIdentifier)) { // visib_reg
            return false;
        }

        if (!$ilSetting->get('usr_settings_visib_lua_' . $fieldIdentifier)) { // visib_lua
            return false;
        }
        
        if ($ilSetting->get("usr_settings_disable_" . $fieldIdentifier)) { // changeable
            return false;
        }
        
        if (!$ilSetting->get('usr_settings_changeable_lua_' . $fieldIdentifier)) { // changeable_lua
            return false;
        }
        
        return true;
    }
}
