<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMarkSchemaGUI
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
class ilMarkSchemaGUI
{
    /**
     * @var ilMarkSchemaAware|ilEctsGradesEnabled
     */
    protected $object;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @param ilMarkSchemaAware|ilEctsGradesEnabled $object
     */
    public function __construct(ilMarkSchemaAware $object)
    {
        /**
         * @var $ilCtrl    ilCtrl
         * @var $lng       ilLanguage
         * @var $tpl       ilTemplate
         * @var $ilToolbar ilToolbarGUI
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];

        $this->ctrl    = $ilCtrl;
        $this->lng     = $lng;
        $this->tpl     = $tpl;
        $this->toolbar = $ilToolbar;

        $this->object = $object;
    }

    /**
     * Controller method
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);
        
        $cmd = $this->ctrl->getCmd('showMarkSchema');
        $this->$cmd();
    }

    /**
     *
     */
    protected function ensureMarkSchemaCanBeEdited()
    {
        if (!$this->object->canEditMarks()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'showMarkSchema');
        }
    }

    /**
     *
     */
    protected function ensureEctsGradesCanBeEdited()
    {
        if (!$this->object->canEditEctsGrades()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'showMarkSchema');
        }
    }

    /**
     * Add a new mark step to the tests marks
     */
    protected function addMarkStep()
    {
        $this->ensureMarkSchemaCanBeEdited();

        $this->saveMarkSchemaFormData();
        $this->object->getMarkSchema()->addMarkStep();
        $this->showMarkSchema();
    }

    /**
     * Save the mark schema POST data when the form was submitted
     */
    protected function saveMarkSchemaFormData()
    {
        $this->object->getMarkSchema()->flush();
        foreach ($_POST as $key => $value) {
            if (preg_match('/mark_short_(\d+)/', $key, $matches)) {
                $this->object->getMarkSchema()->addMarkStep(
                    ilUtil::stripSlashes($_POST["mark_short_$matches[1]"]),
                    ilUtil::stripSlashes($_POST["mark_official_$matches[1]"]),
                    ilUtil::stripSlashes($_POST["mark_percentage_$matches[1]"]),
                    ilUtil::stripSlashes($_POST["passed_$matches[1]"])
                );
            }
        }
    }

    /**
     * Add a simple mark schema to the tests marks
     */
    protected function addSimpleMarkSchema()
    {
        $this->ensureMarkSchemaCanBeEdited();

        $this->object->getMarkSchema()->createSimpleSchema(
            $this->lng->txt('failed_short'),
            $this->lng->txt('failed_official'),
            0,
            0,
            $this->lng->txt('passed_short'),
            $this->lng->txt('passed_official'),
            50,
            1
        );
        $this->showMarkSchema();
    }

    /**
     * Delete selected mark steps
     */
    protected function deleteMarkSteps()
    {
        $this->ensureMarkSchemaCanBeEdited();

        if (!isset($_POST['marks']) || !is_array($_POST['marks'])) {
            $this->showMarkSchema();
            return;
        }

        $this->saveMarkSchemaFormData();
        $delete_mark_steps = array();
        foreach ($_POST['marks'] as $mark_step_id) {
            $delete_mark_steps[] = $mark_step_id;
        }

        if (count($delete_mark_steps)) {
            $this->object->getMarkSchema()->deleteMarkSteps($delete_mark_steps);
        } else {
            ilUtil::sendInfo($this->lng->txt('tst_delete_missing_mark'));
        }

        $this->showMarkSchema();
    }

    /**
     * Save the mark schema
     */
    protected function saveMarks()
    {
        $this->ensureMarkSchemaCanBeEdited();

        try {
            $this->saveMarkSchemaFormData();
            $result = $this->object->checkMarks();
        } catch (Exception $e) {
            $result = $this->lng->txt('mark_schema_invalid');
        }

        if (is_string($result)) {
            ilUtil::sendFailure($this->lng->txt($result), true);
        } else {
            $this->object->getMarkSchema()->saveToDb($this->object->getMarkSchemaForeignId());
            $this->object->onMarkSchemaSaved();
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        }

        $this->ctrl->redirect($this);
    }

    /**
     * @return boolean
     */
    private function objectSupportsEctsGrades()
    {
        require_once 'Modules/Test/interfaces/interface.ilEctsGradesEnabled.php';
        return $this->object instanceof ilEctsGradesEnabled;
    }

    /**
     * Display mark schema
     * @param ilPropertyFormGUI $ects_form
     */
    protected function showMarkSchema(ilPropertyFormGUI $ects_form = null)
    {
        if (!$this->object->canEditMarks()) {
            ilUtil::sendInfo($this->lng->txt('cannot_edit_marks'));
        }

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'showMarkSchema'));

        require_once 'Modules/Test/classes/tables/class.ilMarkSchemaTableGUI.php';
        $mark_schema_table = new ilMarkSchemaTableGUI($this, 'showMarkSchema', '', $this->object);

        if ($this->object->canEditMarks()) {
            require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
            $create_simple_mark_schema_button = ilSubmitButton::getInstance();
            $create_simple_mark_schema_button->setCaption($this->lng->txt('tst_mark_create_simple_mark_schema'), false);
            $create_simple_mark_schema_button->setCommand('addSimpleMarkSchema');
            $this->toolbar->addButtonInstance($create_simple_mark_schema_button);

            require_once 'Services/UIComponent/Button/classes/class.ilButton.php';
            $create_new_mark_step_button = ilButton::getInstance();
            $create_new_mark_step_button->setCaption($this->lng->txt('tst_mark_create_new_mark_step'), false);
            $create_new_mark_step_button->setButtonType(ilButton::BUTTON_TYPE_SUBMIT);
            $create_new_mark_step_button->setForm('form_' . $mark_schema_table->getId());
            $create_new_mark_step_button->setName('addMarkStep');
            $this->toolbar->addButtonInstance($create_new_mark_step_button);
        }


        $content_parts = array($mark_schema_table->getHTML());

        if ($this->objectSupportsEctsGrades() && $this->object->canShowEctsGrades()) {
            if (!($ects_form instanceof ilPropertyFormGUI)) {
                $ects_form = $this->getEctsForm();
                $this->populateEctsForm($ects_form);
            }
            $content_parts[] = $ects_form->getHTML();
        }

        $this->tpl->setContent(implode('<br />', $content_parts));
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function populateEctsForm(ilPropertyFormGUI $form)
    {
        $data = array();

        $data['ectcs_status']      = $this->object->getECTSOutput();
        $data['use_ects_fx']       = preg_match('/\d+/', $this->object->getECTSFX());
        $data['ects_fx_threshold'] = $this->object->getECTSFX();

        $ects_grades = $this->object->getECTSGrades();
        for ($i = ord('a'); $i <= ord('e'); $i++) {
            $mark = chr($i);
            $data['ects_grade_' . $mark] = $ects_grades[chr($i - 32)];
        }

        $form->setValuesByArray($data);
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getEctsForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

        $disabled = !$this->object->canEditEctsGrades();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveEctsForm'));
        $form->setTitle($this->lng->txt('ects_output_of_ects_grades'));

        $allow_ects_marks = new ilCheckboxInputGUI($this->lng->txt('ects_allow_ects_grades'), 'ectcs_status');
        $allow_ects_marks->setDisabled($disabled);
        for ($i = ord('a'); $i <= ord('e'); $i++) {
            $mark = chr($i);

            $mark_step = new ilNumberInputGUI(chr($i - 32), 'ects_grade_' . $mark);
            $mark_step->setInfo(
                $this->lng->txt('ects_grade_desc_prefix') . ' ' . $this->lng->txt('ects_grade_' . $mark . '_desc')
            );
            $mark_step->setSize(5);
            $mark_step->allowDecimals(true);
            $mark_step->setMinValue(0, true);
            $mark_step->setMaxValue(100, true);
            $mark_step->setSuffix($this->lng->txt('percentile'));
            $mark_step->setRequired(true);
            $mark_step->setDisabled($disabled);
            $allow_ects_marks->addSubItem($mark_step);
        }
        
        $mark_step = new ilNonEditableValueGUI('F', 'ects_grade_f');
        $mark_step->setInfo(
            $this->lng->txt('ects_grade_desc_prefix') . ' ' . $this->lng->txt('ects_grade_f_desc')
        );
        $allow_ects_marks->addSubItem($mark_step);

        $use_ects_fx = new ilCheckboxInputGUI($this->lng->txt('use_ects_fx'), 'use_ects_fx');
        $use_ects_fx->setDisabled($disabled);
        $allow_ects_marks->addSubItem($use_ects_fx);
        
        $mark_step = new ilNonEditableValueGUI('FX', 'ects_grade_fx');
        $mark_step->setInfo(
            $this->lng->txt('ects_grade_desc_prefix') . ' ' . $this->lng->txt('ects_grade_fx_desc')
        );
        $use_ects_fx->addSubItem($mark_step);
        
        $threshold = new ilNumberInputGUI($this->lng->txt('ects_fx_threshold'), 'ects_fx_threshold');
        $threshold->setInfo($this->lng->txt('ects_fx_threshold_info'));
        $threshold->setSuffix($this->lng->txt('percentile'));
        $threshold->allowDecimals(true);
        $threshold->setSize(5);
        $threshold->setRequired(true);
        $threshold->setDisabled($disabled);
        $use_ects_fx->addSubItem($threshold);
        

        $form->addItem($allow_ects_marks);

        if (!$disabled) {
            $form->addCommandButton('saveEctsForm', $this->lng->txt('save'));
        }

        return $form;
    }

    /**
     *
     */
    protected function saveEctsForm()
    {
        $this->ensureEctsGradesCanBeEdited();

        $ects_form = $this->getEctsForm();
        if (!$ects_form->checkInput()) {
            $ects_form->setValuesByPost();
            $this->showMarkSchema($ects_form);
            return;
        }

        $grades = array();
        for ($i = ord('a'); $i <= ord('e'); $i++) {
            $mark = chr($i);
            $grades[chr($i - 32)] = $ects_form->getInput('ects_grade_' . $mark);
        }

        $this->object->setECTSGrades($grades);
        $this->object->setECTSOutput((int) $ects_form->getInput('ectcs_status'));
        $this->object->setECTSFX(
            $ects_form->getInput('use_ects_fx') && preg_match('/\d+/', $ects_form->getInput('ects_fx_threshold')) ?
            $ects_form->getInput('ects_fx_threshold'):
            null
        );

        $this->object->saveECTSStatus();

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $ects_form->setValuesByPost();
        $this->showMarkSchema($ects_form);
    }
}
