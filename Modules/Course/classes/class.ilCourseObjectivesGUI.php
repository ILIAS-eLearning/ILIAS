<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

// begin-patch lok
include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
// end-patch lok

/**
* class ilobjcourseobjectivesgui
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @extends Object
*/
class ilCourseObjectivesGUI
{
    const MODE_UNDEFINED = 0;
    const MODE_CREATE = 1;
    const MODE_UPDATE = 2;
    
    
    public $ctrl;
    public $ilias;
    public $ilErr;
    public $lng;
    public $tpl;

    public $course_obj;
    public $course_id;
    
    // begin-patch lok
    protected $settings;
    protected $test_type = 0;
    // end-patch lok
    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    /**
     * Constructor
     * @param int $a_course_id
     */
    public function __construct($a_course_id)
    {
        include_once './Modules/Course/classes/class.ilCourseObjective.php';

        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilErr = $DIC['ilErr'];
        $ilias = $DIC['ilias'];
        $tpl = $DIC['tpl'];
        $tree = $DIC['tree'];
        $ilTabs = $DIC['ilTabs'];

        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id"));

        $this->logger = $GLOBALS['DIC']->logger()->crs();
        $this->ilErr = $ilErr;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('crs');
        $this->tpl = $tpl;
        $this->tree = $tree;
        $this->tabs_gui = $ilTabs;
        
        $this->course_id = $a_course_id;
        $this->__initCourseObject();

        // begin-patch lok
        $this->settings = ilLOSettings::getInstanceByObjId($this->course_obj->getId());
        // end-patch lok
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        $ilTabs->setTabActive('crs_objectives');
        
        $cmd = $this->ctrl->getCmd();


        if (!$cmd = $this->ctrl->getCmd()) {
            $cmd = "list";
        }

        $this->$cmd();
    }
    
    // begin-patch lok
    /**
     * Get settings
     * @return ilLOSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    // end-patch lok
    
    
    /**
     * list objectives
     *
     * @access protected
     * @param
     * @return
     */
    protected function listObjectives()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilToolbar = $DIC['ilToolbar'];
        
        $_SESSION['objective_mode'] = self::MODE_UNDEFINED;
        if (!$ilAccess->checkAccess("write", '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilErr->MESSAGE);
        }
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.crs_objectives.html', 'Modules/Course');
        
        $ilToolbar->addButton(
            $this->lng->txt('crs_add_objective'),
            $this->ctrl->getLinkTarget($this, "'create")
        );
        
        include_once('./Modules/Course/classes/class.ilCourseObjectivesTableGUI.php');
        $table = new ilCourseObjectivesTableGUI($this, $this->course_obj);
        $table->setTitle($this->lng->txt('crs_objectives'), '', $this->lng->txt('crs_objectives'));
        $table->parse(ilCourseObjective::_getObjectiveIds($this->course_obj->getId(), false));
        
        $this->tpl->setVariable('OBJECTIVES_TABLE', $table->getHTML());
    }
    
    /**
     * save position
     *
     * @access protected
     * @return
     */
    protected function saveSorting()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        if (!$ilAccess->checkAccess("write", '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilErr->MESSAGE);
        }
        
        asort($_POST['position'], SORT_NUMERIC);
        
        $counter = 1;
        foreach ($_POST['position'] as $objective_id => $position) {
            $objective = new ilCourseObjective($this->course_obj, $objective_id);
            $objective->writePosition($counter++);
        }
        ilUtil::sendSuccess($this->lng->txt('crs_objective_saved_sorting'));
        $this->listObjectives();
    }

    public function askDeleteObjective()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        // MINIMUM ACCESS LEVEL = 'write'
        if (!$rbacsystem->checkAccess("write", $this->course_obj->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilErr->MESSAGE);
        }
        if (!count($_POST['objective'])) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'));
            $this->listObjectives();
            
            return true;
        }

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives.html", 'Modules/Course');

        ilUtil::sendQuestion($this->lng->txt('crs_delete_objectve_sure'));

        $tpl = new ilTemplate("tpl.table.html", true, true);
        $tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_objectives_delete_row.html", 'Modules/Course');

        $counter = 0;
        foreach ($_POST['objective'] as $objective_id) {
            $objective_obj = $this->__initObjectivesObject($objective_id);
            
            $tpl->setCurrentBlock("tbl_content");
            $tpl->setVariable("ROWCOL", ilUtil::switchColor(++$counter, "tblrow2", "tblrow1"));
            $tpl->setVariable("TITLE", $objective_obj->getTitle());
            $tpl->setVariable("DESCRIPTION", $objective_obj->getDescription());
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        // Show action row
        $tpl->setCurrentBlock("tbl_action_btn");
        $tpl->setVariable("BTN_NAME", 'deleteObjectives');
        $tpl->setVariable("BTN_VALUE", $this->lng->txt('delete'));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("tbl_action_btn");
        $tpl->setVariable("BTN_NAME", 'listObjectives');
        $tpl->setVariable("BTN_VALUE", $this->lng->txt('cancel'));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("tbl_action_row");
        $tpl->setVariable("COLUMN_COUNTS", 1);
        $tpl->setVariable("IMG_ARROW", ilUtil::getImagePath('arrow_downright.svg'));
        $tpl->parseCurrentBlock();


        // create table
        $tbl = new ilTableGUI();
        $tbl->setStyle('table', 'std');

        // title & header columns
        $tbl->setTitle($this->lng->txt("crs_objectives"), "", $this->lng->txt("crs_objectives"));

        $tbl->setHeaderNames(array($this->lng->txt("title")));
        $tbl->setHeaderVars(
            array("title"),
            array("ref_id" => $this->course_obj->getRefId(),
                                  "cmdClass" => "ilcourseobjectivesgui",
                                  "cmdNode" => $_GET["cmdNode"])
        );
        $tbl->setColumnWidth(array("50%"));

        $tbl->setLimit($_GET["limit"]);
        $tbl->setOffset($_GET["offset"]);
        $tbl->setMaxCount(count($_POST['objective']));

        // footer
        $tbl->disable("footer");
        $tbl->disable('sort');

        // render table
        $tbl->setTemplate($tpl);
        $tbl->render();

        $this->tpl->setVariable("OBJECTIVES_TABLE", $tpl->get());
        

        // Save marked objectives
        $_SESSION['crs_delete_objectives'] = $_POST['objective'];

        return true;
    }

    public function deleteObjectives()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        // MINIMUM ACCESS LEVEL = 'write'
        if (!$rbacsystem->checkAccess("write", $this->course_obj->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilErr->MESSAGE);
        }
        if (!count($_SESSION['crs_delete_objectives'])) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'));
            $this->listObjectives();
            
            return true;
        }

        foreach ($_SESSION['crs_delete_objectives'] as $objective_id) {
            $objective_obj = &$this->__initObjectivesObject($objective_id);
            $objective_obj->delete();
        }

        ilUtil::sendSuccess($this->lng->txt('crs_objectives_deleted'));
        $this->listObjectives();

        return true;
    }
    
    /**
     * question overiew
     *
     * @access protected
     * @return
     */
    protected function questionOverview()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC['ilTabs'];
        
        $ilTabs->setSubTabActive('crs_objective_overview_question_assignment');
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }

        include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestionsTableGUI.php');
        $table = new ilCourseObjectiveQuestionsTableGUI($this, $this->course_obj);
        $table->setTitle($this->lng->txt('crs_objectives_edit_question_assignments'), '', $this->lng->txt('crs_objectives'));
        // begin-patch lok
        $table->parse(ilCourseObjective::_getObjectiveIds($this->course_obj->getId(), false));
        // end-patch lok
        
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * update question overview
     *
     * @access protected
     * @return
     */
    protected function saveQuestionOverview()
    {
        include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
        
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        $error = false;
        
        $_POST['self'] = $_POST['self'] ? $_POST['self'] : array();
        $_POST['final'] = $_POST['final'] ? $_POST['final'] : array();
        
        foreach ($_POST['self'] as $objective_id => $limit) {
            $qst = new ilCourseObjectiveQuestion($objective_id);
            $max_points = $qst->getSelfAssessmentPoints();
            
            if ($limit < 0 or $limit > $max_points) {
                ilUtil::sendFailure($this->lng->txt('crs_objective_limit_err'));
                $this->questionOverview();
                return false;
            }
        }
        foreach ($_POST['final'] as $objective_id => $limit) {
            $qst = new ilCourseObjectiveQuestion($objective_id);
            $max_points = $qst->getFinalTestPoints();
            
            if ($limit < 0 or $limit > $max_points) {
                ilUtil::sendFailure($this->lng->txt('crs_objective_limit_err'));
                $this->questionOverview();
                return false;
            }
        }
        
        foreach ($_POST['self'] as $objective_id => $limit) {
            ilCourseObjectiveQuestion::_updateTestLimits($objective_id, ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT, $limit);
        }
        
        foreach ($_POST['final'] as $objective_id => $limit) {
            ilCourseObjectiveQuestion::_updateTestLimits($objective_id, ilCourseObjectiveQuestion::TYPE_FINAL_TEST, $limit);
        }
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->questionOverview();
        return true;
    }

    // PRIVATE
    public function __initCourseObject()
    {
        if (!$this->course_obj = &ilObjectFactory::getInstanceByRefId($this->course_id, false)) {
            $this->ilErr->raiseError("ilCourseObjectivesGUI: cannot create course object", $this->ilErr->MESSAGE);
            exit;
        }
        return true;
    }

    public function __initObjectivesObject($a_id = 0)
    {
        return $this->objectives_obj = new ilCourseObjective($this->course_obj, $a_id);
    }

    public function __initLMObject($a_objective_id = 0)
    {
        include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
        $this->objectives_lm_obj = new ilCourseObjectiveMaterials($a_objective_id);

        return true;
    }

    // begin-patch lok
    /**
     *
     * @param type $a_objective_id
     * @return ilCourseObjectiveQuestion
     */
    public function __initQuestionObject($a_objective_id = 0)
    {
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
        $this->objectives_qst_obj = new ilCourseObjectiveQuestion($a_objective_id);

        return $this->objectives_qst_obj;
    }
    // end-patch lok

    /**
    * set sub tabs
    */
    public function setSubTabs($a_active = "")
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $ilHelp = $DIC['ilHelp'];

        if ($a_active != "") {
            $ilHelp->setScreenIdComponent("crs");
            $ilHelp->setScreenId("crs_objective");
            $ilHelp->setSubScreenId($a_active);
        }


        // begin-patch lok
        // no subtabs here
        return true;
        // end-patch lok
        
        
        $ilTabs->addSubTabTarget(
            "crs_objective_overview_objectives",
            $this->ctrl->getLinkTarget($this, "listObjectives"),
            array("listObjectives", "moveObjectiveUp", "moveObjectiveDown", "listAssignedLM"),
            array(),
            '',
            true
        );
        include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
        
        if (ilCourseObjectiveQuestion::_hasTests($this->course_obj->getId())) {
            $ilTabs->addSubTabTarget(
                "crs_objective_overview_question_assignment",
                $this->ctrl->getLinkTarget($this, "questionOverview"),
                "editQuestionAssignment",
                array(),
                '',
                false
            );
        }
    }
    
    
    /**
     * create objective
     *
     * @access public
     * @param
     * @return
     */
    public function create()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        $this->setSubTabs("create_obj");
        
        $_SESSION['objective_mode'] = self::MODE_CREATE;
        
        $this->ctrl->saveParameter($this, 'objective_id');
        
        if (!is_object($this->objective)) {
            $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        }
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(1);
        
        $this->initFormTitle('create', 1);
        $GLOBALS['DIC']['tpl']->setContent($this->form->getHtml());
        #$w_tpl->setVariable('WIZ_CONTENT',$this->form->getHtml());
        #$tpl->setContent($w_tpl->get());
    }

    /**
     * edit objective
     *
     * @access protected
     * @return
     */
    protected function edit()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        $_SESSION['objective_mode'] = self::MODE_UPDATE;

        $this->setSubTabs("edit_obj");
        
        $this->ctrl->setParameter($this, 'objective_id', (int) $_REQUEST['objective_id']);
        
        if (!$_REQUEST['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }
        
        if (!is_object($this->objective)) {
            $this->objective = new ilCourseObjective($this->course_obj, (int) $_REQUEST['objective_id']);
        }
        
        $this->__initQuestionObject((int) $_REQUEST['objective_id']);
        $this->initWizard(1);
        $this->initFormTitle('create', 1);
        $GLOBALS['DIC']['tpl']->setContent($this->form->getHtml());
        #$w_tpl->setVariable('WIZ_CONTENT',$this->form->getHtml());
        #$tpl->setContent($w_tpl->get());
    }

    /**
     * save
     *
     * @access protected
     * @return
     */
    protected function save()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }

        $this->ctrl->saveParameter($this, 'objective_id');
        
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_REQUEST['objective_id']);
        $this->initFormTitle('create', 1);
        if ($this->form->checkInput()) {
            $this->objective->setTitle($this->form->getInput('title'));
            $this->objective->setDescription($this->form->getInput('description'));
            $this->objective->setPasses(0);
            
            if (!$_GET['objective_id']) {
                $objective_id = $this->objective->add();
                ilUtil::sendSuccess($this->lng->txt('crs_added_objective'), true);
            } else {
                $this->objective->update();
                ilUtil::sendSuccess($this->lng->txt('crs_objective_modified'), true);
                $objective_id = $_GET['objective_id'];
            }
        } else {
            if ((int) $_GET['objective_id']) {
                $this->form->setValuesByPost();
                return $this->edit();
            } else {
                $this->form->setValuesByPost();
                return $this->create();
            }
        }
        
        if ($_SESSION['objective_mode'] != self::MODE_CREATE) {
            $this->ctrl->returnToParent($this);
        }
        
        $this->ctrl->setParameter($this, 'objective_id', $objective_id);
        $this->ctrl->redirect($this, 'materialAssignment');
        return true;
    }
    
    /**
     * material assignment
     *
     * @access protected
     * @return
     */
    protected function materialAssignment()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("materials");

        $this->ctrl->saveParameter($this, 'objective_id');

        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        
        include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterialAssignmentTableGUI.php');
        $table = new ilCourseObjectiveMaterialAssignmentTableGUI($this, $this->course_obj, (int) $_GET['objective_id']);
        $table->setTitle(
            $this->lng->txt('crs_objective_wiz_materials'),
            '',
            $this->lng->txt('crs_objectives')
        );

        include_once('Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
        $table->parse(ilCourseObjectiveMaterials::_getAssignableMaterials($this->course_obj->getRefId()));
        
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(2);
        #$w_tpl->setVariable('WIZ_CONTENT',$table->getHTML());
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
        #$tpl->setContent($w_tpl->get());
    }
    
    /**
     * update material assignment
     *
     * @access protected
     * @param
     * @return
     */
    protected function updateMaterialAssignment()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initLMObject((int) $_GET['objective_id']);
        $this->objectives_lm_obj->deleteAll();
        
        if (is_array($_POST['materials'])) {
            foreach ($_POST['materials'] as $node_id) {
                $obj_id = $ilObjDataCache->lookupObjId($node_id);
                $type = $ilObjDataCache->lookupType($obj_id);
                
                $this->objectives_lm_obj->setLMRefId($node_id);
                $this->objectives_lm_obj->setLMObjId($obj_id);
                $this->objectives_lm_obj->setType($type);
                $this->objectives_lm_obj->add();
            }
        }
        if (is_array($_POST['chapters'])) {
            foreach ($_POST['chapters'] as $chapter) {
                include_once('./Modules/LearningModule/classes/class.ilLMObject.php');
                
                list($ref_id, $chapter_id) = explode('_', $chapter);
                
                $this->objectives_lm_obj->setLMRefId($ref_id);
                $this->objectives_lm_obj->setLMObjId($chapter_id);
                $this->objectives_lm_obj->setType(ilLMObject::_lookupType($chapter_id));
                $this->objectives_lm_obj->add();
            }
        }
        ilUtil::sendSuccess($this->lng->txt('crs_objectives_assigned_lm'));
        
        
        if ($_SESSION['objective_mode'] != self::MODE_CREATE) {
            ilUtil::sendSuccess($this->lng->txt('crs_objectives_assigned_lm'), true);
            $this->ctrl->returnToParent($this);
        }
        
        // begin-patch lok
        if ($this->getSettings()->worksWithInitialTest()) {
            $this->selfAssessmentAssignment();
        } else {
            $this->finalTestAssignment();
        }
        // end-patch lok
    }

    /**
     * self assessment assignemnt
     *
     * @access protected
     * @return
     */
    protected function selfAssessmentAssignment()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("self_ass_assign");

        $this->ctrl->saveParameter($this, 'objective_id');

        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);

        // begin-patch lok
        $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_INITIAL);
        $this->test_type = $_REQUEST['tt'] = ilLOSettings::TYPE_TEST_INITIAL;
        if ($this->isRandomTestType(ilLOSettings::TYPE_TEST_INITIAL)) {
            return $this->showRandomTestAssignment();
        }
        // end-patch lok
        
        include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestionAssignmentTableGUI.php');
        $table = new ilCourseObjectiveQuestionAssignmentTableGUI(
            $this,
            $this->course_obj,
            (int) $_GET['objective_id'],
            ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT
        );
        $table->setTitle(
            $this->lng->txt('crs_objective_wiz_self'),
            '',
            $this->lng->txt('crs_objective')
        );
        $table->parse(ilCourseObjectiveQuestion::_getAssignableTests($this->course_obj->getRefId()));
        
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(3);
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
        #$w_tpl->setVariable('WIZ_CONTENT',$table->getHTML());
        #$tpl->setContent($w_tpl->get());
    }
    
    /**
     * update self assessment assignment
     *
     * @access protected
     * @param
     * @return
     */
    protected function updateSelfAssessmentAssignment()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $checked_questions = $_POST['questions'] ? $_POST['questions'] : array();
        
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initQuestionObject((int) $_GET['objective_id']);

        // Delete unchecked
        foreach ($this->objectives_qst_obj->getSelfAssessmentQuestions() as $question) {
            $id = $question['ref_id'] . '_' . $question['question_id'];
            if (!in_array($id, $checked_questions)) {
                $this->objectives_qst_obj->delete($question['qst_ass_id']);
            }
        }
        // Add checked
        foreach ($checked_questions as $question_id) {
            list($test_ref_id, $qst_id) = explode('_', $question_id);
            $test_obj_id = $ilObjDataCache->lookupObjId($test_ref_id);
    
            if ($this->objectives_qst_obj->isSelfAssessmentQuestion($qst_id)) {
                continue;
            }
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT);
            $this->objectives_qst_obj->setTestRefId($test_ref_id);
            $this->objectives_qst_obj->setTestObjId($test_obj_id);
            $this->objectives_qst_obj->setQuestionId($qst_id);
            $this->objectives_qst_obj->add();
        }
        
        // TODO: not nice
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
        $this->questions = new ilCourseObjectiveQuestion((int) $_GET['objective_id']);
        // not required due to percentages
        //$this->questions->updateLimits();
        
        if ($checked_questions) {
            ilUtil::sendSuccess($this->lng->txt('crs_objectives_assigned_lm'));
            $this->selfAssessmentLimits();
            return true;
        } else {
            switch ($_SESSION['objective_mode']) {
                case self::MODE_CREATE:
                    $this->finalTestAssignment();
                    return true;

                case self::MODE_UPDATE:
                    $this->selfAssessmentAssignment();
                    ilUtil::sendSuccess($this->lng->txt('crs_objectives_assigned_lm'));
                    return true;
            }
        }
    }
    
    /**
     * self assessment limits
     *
     * @access protected
     * @param
     * @return
     */
    protected function selfAssessmentLimits()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("self_ass_limits");

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(4);
        
        $this->initFormLimits('selfAssessment');
        $GLOBALS['DIC']['tpl']->setContent($this->form->getHtml());
        #$w_tpl->setVariable('WIZ_CONTENT',$this->form->getHtml());
        #$tpl->setContent($w_tpl->get());
    }
    
    /**
     * update self assessment limits
     *
     * @access protected
     * @param
     * @return
     */
    protected function updateSelfAssessmentLimits()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initQuestionObject((int) $_GET['objective_id']);

        if ((int) $_POST['limit'] < 1 or (int) $_POST['limit'] > 100) {
            ilUtil::sendFailure($this->lng->txt('crs_objective_err_limit'));
            $this->selfAssessmentLimits();
            return false;
        }
        
        foreach ($this->objectives_qst_obj->getSelfAssessmentTests() as $test) {
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT);
            $this->objectives_qst_obj->setTestSuggestedLimit((int) $_POST['limit']);
            $this->objectives_qst_obj->updateTest($test['test_objective_id']);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->returnToParent($this);
    }
    
    
    /**
     * final test assignment
     *
     * @access protected
     * @param
     * @return
     */
    protected function finalTestAssignment()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("final_test_assign");

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        
        // begin-patch lok
        $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
        $this->test_type = $_REQUEST['tt'] = ilLOSettings::TYPE_TEST_QUALIFIED;
        if ($this->isRandomTestType(ilLOSettings::TYPE_TEST_QUALIFIED)) {
            return $this->showRandomTestAssignment();
        }
        // end-patch lok
        
        include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestionAssignmentTableGUI.php');
        $table = new ilCourseObjectiveQuestionAssignmentTableGUI(
            $this,
            $this->course_obj,
            (int) $_GET['objective_id'],
            ilCourseObjectiveQuestion::TYPE_FINAL_TEST
        );

        $table->setTitle(
            $this->lng->txt('crs_objective_wiz_final'),
            '',
            $this->lng->txt('crs_objective')
        );
        $table->parse(ilCourseObjectiveQuestion::_getAssignableTests($this->course_obj->getRefId()));
        
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(5);
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
        #$w_tpl->setVariable('WIZ_CONTENT',$table->getHTML());
        #$tpl->setContent($w_tpl->get());
    }
    
    // begin-patch lok
    protected function isRandomTestType($a_tst_type = 0)
    {
        if (!$a_tst_type) {
            $a_tst_type = $this->test_type;
        }
        
        $tst_ref_id = $this->getSettings()->getTestByType($a_tst_type);
        if (!$tst_ref_id) {
            return false;
        }
        include_once './Modules/Test/classes/class.ilObjTest.php';
        return ilObjTest::_lookupRandomTest(ilObject::_lookupObjId($tst_ref_id));
    }
    
    /**
     *
     * @param ilPropertyFormGUI $form
     */
    protected function showRandomTestAssignment(ilPropertyFormGUI $form = null)
    {
        $this->ctrl->saveParameter($this, 'objective_id');
        $this->ctrl->setParameter($this, 'tt', (int) $_REQUEST['tt']);
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        $this->test_type = (int) $_REQUEST['tt'];


        $this->setSubTabs("rand_test_assign");

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormRandom();
        }
        
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(5);
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
        #$w_tpl->setVariable('WIZ_CONTENT',$form->getHTML());
        
        #$GLOBALS['DIC']['tpl']->setContent($w_tpl->get());
    }
    
    /**
     * show random test
     */
    protected function initFormRandom()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        if ($this->test_type == ilLOSettings::TYPE_TEST_INITIAL) {
            $form->setTitle($this->lng->txt('crs_loc_form_random_limits_it'));
        } else {
            $form->setTitle($this->lng->txt('crs_loc_form_random_limits_qt'));
        }
        
        $form->addCommandButton('saveRandom', $this->lng->txt('save'));
        
        $options = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_rand_assign_qpl'), 'type');
        $options->setValue(1);
        $options->setRequired(true);
        
        $ass_qpl = new ilRadioOption($this->lng->txt('crs_loc_rand_assign_qpl'), 1);
        $options->addOption($ass_qpl);
        
        $qpl = new ilSelectInputGUI($this->lng->txt('crs_loc_rand_qpl'), 'qpl');
        $qpl->setRequired(true);
        $qpl->setMulti(true, false);
        $qpl->setOptions($this->getRandomTestQplOptions());
        
        $sequences = ilLORandomTestQuestionPools::lookupSequencesByType(
            $this->course_obj->getId(),
            (int) $_REQUEST['objective_id'],
            ilObject::_lookupObjId($this->getSettings()->getTestByType($this->test_type)),
            $this->test_type
        );
        
        $qpl->setValue($sequences[0]);
        $qpl->setMultiValues($sequences);
        $ass_qpl->addSubItem($qpl);
        
        // points
        $per = new ilNumberInputGUI($this->lng->txt('crs_loc_perc'), 'per');
        $per->setValue(
            ilLORandomTestQuestionPools::lookupLimit(
                $this->course_obj->getId(),
                (int) $_REQUEST['objective_id'],
                $this->test_type
            )
        );
        $per->setSize(3);
        $per->setMinValue(1);
        $per->setMaxValue(100);
        $per->setRequired(true);
        $ass_qpl->addSubItem($per);
        
        $form->addItem($options);
        return $form;
    }
    
    
    protected function getRandomTestQplOptions()
    {
        include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
        include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
        
        $tst_ref_id = $this->getSettings()->getTestByType($this->test_type);
        if ($tst_ref_id) {
            $tst = ilObjectFactory::getInstanceByRefId($tst_ref_id, false);
        }
        if (!$tst instanceof ilObjTest) {
            return array();
        }
        $list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $GLOBALS['DIC']['ilDB'],
            $tst,
            new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                $GLOBALS['DIC']['ilDB'],
                $tst
            )
        );
                
        $list->loadDefinitions();

        include_once './Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
        $translater = new ilTestTaxonomyFilterLabelTranslater($GLOBALS['DIC']['ilDB']);
        $translater->loadLabels($list);
        
        $options[0] = $this->lng->txt('select_one');
        foreach ($list as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $title = $definition->getPoolTitle();
            // fau: taxFilter/typeFilter - get title for extended filter conditions
            $filterTitle = array();
            $filterTitle[] = $translater->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter());
            $filterTitle[] = $translater->getTypeFilterLabel($definition->getTypeFilter());
            if (!empty($filterTitle)) {
                $title .= ' -> ' . implode(' / ', $filterTitle);
            }
            #$tax_id = $definition->getMappedFilterTaxId();
            #if($tax_id)
            #{
            #	$title .= (' -> '. $translater->getTaxonomyTreeLabel($tax_id));
            #}
            #$tax_node = $definition->getMappedFilterTaxNodeId();
            #if($tax_node)
            #{
            #	$title .= (' -> ' .$translater->getTaxonomyNodeLabel($tax_node));
            #}
            // fau.
            $options[$definition->getId()] = $title;
        }
        return $options;
    }
    
    /**
     * Save random test settings
     */
    protected function saveRandom()
    {
        $this->ctrl->saveParameter($this, 'objective_id');
        $this->ctrl->setParameter($this, 'tt', (int) $_REQUEST['tt']);
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        $this->test_type = (int) $_REQUEST['tt'];
        
        $form = $this->initFormRandom();
        
        
        
        
        if ($form->checkInput()) {
            ilLORandomTestQuestionPools::deleteForObjectiveAndTestType(
                $this->course_obj->getId(),
                (int) $_REQUEST['objective_id'],
                $this->test_type
            );

            $qst = $this->__initQuestionObject((int) $_GET['objective_id']);
            $qst->deleteByTestType(
                ($this->test_type == ilLOSettings::TYPE_TEST_INITIAL) ?
                    ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT :
                    ilCourseObjectiveQuestion::TYPE_FINAL_TEST
            );
            $ref_id = $this->getSettings()->getTestByType($this->test_type);
            foreach (array_unique((array) $form->getInput('qpl')) as $qpl_id) {
                include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
                $rnd = new ilLORandomTestQuestionPools(
                    $this->course_obj->getId(),
                    (int) $_REQUEST['objective_id'],
                    $this->test_type,
                    $qpl_id
                );
                $rnd->setLimit($form->getInput('per'));
                $rnd->setTestId(ilObject::_lookupObjId($ref_id));
                $rnd->create();
            }
        } else {
            $form->setValuesByPost();
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            return $this->showRandomTestAssignment();
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        if ($this->test_type == ilLOSettings::TYPE_TEST_QUALIFIED) {
            $this->ctrl->returnToParent($this);
        } else {
            $this->ctrl->redirect($this, 'finalTestAssignment');
        }
    }

    /**
     * update self assessment assignment
     *
     * @access protected
     * @param
     * @return
     */
    protected function updateFinalTestAssignment()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $checked_questions = $_POST['questions'] ? $_POST['questions'] : array();
        
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initQuestionObject((int) $_GET['objective_id']);

        // Delete unchecked
        foreach ($this->objectives_qst_obj->getFinalTestQuestions() as $question) {
            $id = $question['ref_id'] . '_' . $question['question_id'];
            if (!in_array($id, $checked_questions)) {
                $this->objectives_qst_obj->delete($question['qst_ass_id']);
            }
        }
        // Add checked
        foreach ($checked_questions as $question_id) {
            list($test_ref_id, $qst_id) = explode('_', $question_id);
            $test_obj_id = $ilObjDataCache->lookupObjId($test_ref_id);
    
            if ($this->objectives_qst_obj->isFinalTestQuestion($qst_id)) {
                continue;
            }
            
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_FINAL_TEST);
            $this->objectives_qst_obj->setTestRefId($test_ref_id);
            $this->objectives_qst_obj->setTestObjId($test_obj_id);
            $this->objectives_qst_obj->setQuestionId($qst_id);
            $this->objectives_qst_obj->add();
        }
        
        // TODO: not nice
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
        $this->questions = new ilCourseObjectiveQuestion((int) $_GET['objective_id']);
        // not required due to percentages
        //$this->questions->updateLimits();
        
        ilUtil::sendSuccess($this->lng->txt('crs_objectives_assigned_lm'));
        $this->finalTestLimits();
    }
    
    /**
     * Show test assignment form
     * @param ilPropertyFormGUI $form
     */
    protected function finalSeparatedTestAssignment(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->returnToParent($this);
        }
        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        
        $this->initWizard(6);
        $form = $this->initFormTestAssignment();
        $GLOBALS['DIC']['tpl']->setContent($form->getHtml());
    }
    
    /**
     * self assessment limits
     *
     * @access protected
     * @param
     * @return
     */
    protected function finalTestLimits()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->setSubTabs("final_test_limits");

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, (int) $_GET['objective_id']);
        
        $this->__initQuestionObject((int) $_GET['objective_id']);
        $this->initWizard(6);
        
        $this->initFormLimits('final');
        $GLOBALS['DIC']['tpl']->setContent($this->form->getHtml());

        #$w_tpl->setVariable('WIZ_CONTENT',$this->form->getHtml());
        #$tpl->setContent($w_tpl->get());
    }
    
    /**
     * update self assessment limits
     *
     * @access protected
     * @param
     * @return
     */
    protected function updateFinalTestLimits()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        if (!$ilAccess->checkAccess('write', '', $this->course_obj->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        if (!$_GET['objective_id']) {
            ilUtil::sendFailure($this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initQuestionObject((int) $_GET['objective_id']);

        if ((int) $_POST['limit'] < 1 or (int) $_POST['limit'] > 100) {
            ilUtil::sendFailure($this->lng->txt('crs_objective_err_limit'));
            $this->finalTestLimits();
            return false;
        }
        
        foreach ($this->objectives_qst_obj->getFinalTests() as $test) {
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_FINAL_TEST);
            $this->objectives_qst_obj->setTestSuggestedLimit((int) $_POST['limit']);
            $this->objectives_qst_obj->updateTest($test['test_objective_id']);
        }
        
        if ($_SESSION['objective_mode'] != self::MODE_CREATE) {
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt('crs_added_objective'), true);
        }
        $this->ctrl->returnToParent($this);
    }
    
    /**
     * init limit form
     *
     * @access protected
     * @param string mode selfAssessment or final
     * @return
     */
    protected function initFormLimits($a_mode)
    {
        if (!is_object($this->form)) {
            include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
            $this->form = new ilPropertyFormGUI();
        }
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTableWidth('100%');
        //$this->form->setTitleIcon(ilUtil::getImagePath('icon_lobj.svg'),$this->lng->txt('crs_objective'));
        
        switch ($a_mode) {
            case 'selfAssessment':
                $this->form->setTitle($this->lng->txt('crs_objective_wiz_self_limit'));
                $this->form->addCommandButton('updateSelfAssessmentLimits', $this->lng->txt('crs_wiz_next'));
                $this->form->addCommandButton('selfAssessmentAssignment', $this->lng->txt('crs_wiz_back'));

                $tests = $this->objectives_qst_obj->getSelfAssessmentTests();
                $max_points = $this->objectives_qst_obj->getSelfAssessmentPoints();

                break;
            
            case 'final':
                $this->form->setTitle($this->lng->txt('crs_objective_wiz_final_limit'));
                $this->form->addCommandButton('updateFinalTestLimits', $this->lng->txt('crs_wiz_next'));
                $this->form->addCommandButton('finalTestAssignment', $this->lng->txt('crs_wiz_back'));

                $tests = $this->objectives_qst_obj->getFinalTests();
                $max_points = $this->objectives_qst_obj->getFinalTestPoints();

                break;
        }
        
        $over = new ilCustomInputGUI($this->lng->txt('crs_objective_qst_summary'), '');
        
        $tpl = new ilTemplate('tpl.crs_objective_qst_summary.html', true, true, 'Modules/Course');
        
        
        $limit = 0;
        
        foreach ($tests as $test) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($test, true));
            
            $limit = $test['limit'];

            foreach ($this->objectives_qst_obj->getQuestionsOfTest($test['obj_id']) as $question) {
                $tpl->setCurrentBlock('qst');
                $tpl->setVariable('QST_TITLE', $question['title']);
                if (strlen($question['description'])) {
                    $tpl->setVariable('QST_DESCRIPTION', $question['description']);
                }
                $tpl->setVariable('QST_POINTS', $question['points'] . ' ' .
                    $this->lng->txt('crs_objective_points'));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('tst');
            $tpl->setVariable('TST_TITLE', ilObject::_lookupTitle($test['obj_id']));
            if ($desc = ilObject::_lookupDescription($test['obj_id'])) {
                $tpl->setVariable('TST_DESC', $desc);
            }
            $tpl->setVariable('TST_TYPE_IMG', ilUtil::getTypeIconPath('tst', $test['obj_id'], 'tiny'));
            $tpl->setVariable('TST_ALT_IMG', $this->lng->txt('obj_tst'));
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable('TXT_ALL_POINTS', $this->lng->txt('crs_objective_all_points'));
        $tpl->setVariable('TXT_POINTS', $this->lng->txt('crs_objective_points'));
        $tpl->setVariable('POINTS', $max_points);
        
        $over->setHtml($tpl->get());
        $this->form->addItem($over);
        
        // points
        $req = new ilNumberInputGUI($this->lng->txt('crs_loc_perc'), 'limit');
        $req->setValue($limit);
        $req->setSize(3);
        $req->setMinValue(1);
        $req->setMaxValue(100);
        $req->setRequired(true);
        switch ($a_mode) {
            case 'selfAssessment':
                $req->setInfo($this->lng->txt('crs_obj_initial_req_info'));
                break;
                
            case 'final':
                $req->setInfo($this->lng->txt('crs_obj_final_req_info'));
                break;
        }
        $this->form->addItem($req);
    }

    
    /**
     * init form title
     *
     * @access protected
     * @return
     */
    protected function initFormTitle($a_mode, $a_step_number)
    {
        include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        if ($this->form instanceof ilPropertyFormGUI) {
            return;
        }
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        //$this->form->setTitleIcon(ilUtil::getImagePath('icon_lobj.svg'),$this->lng->txt('crs_objective'));
        
        switch ($a_mode) {
            case 'create':
                $this->form->setTitle($this->lng->txt('crs_objective_wiz_title'));
                $this->form->addCommandButton('save', $this->lng->txt('crs_wiz_next'));
                // begin-patch lok
                #$this->form->addCommandButton('listObjectives',$this->lng->txt('cancel'));
                // end-patch lok
                break;
            
            case 'update':
                break;
        }
        
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->objective->getTitle());
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(70);
        $this->form->addItem($title);
        
        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $desc->setValue($this->objective->getDescription());
        $desc->setCols(40);
        $desc->setRows(5);
        $this->form->addItem($desc);
    }
    
    
    /**
     * init wizard

     * @access protected
     * @param string mode 'create' or 'edit'
     * @return
     */
    protected function initWizard($a_step_number)
    {
        $options = array(
            1 => $this->lng->txt('crs_objective_wiz_title'),
            2 => $this->lng->txt('crs_objective_wiz_materials'),
            3 => $this->lng->txt('crs_objective_wiz_self'),
            4 => $this->lng->txt('crs_objective_wiz_self_limit'),
            5 => $this->lng->txt('crs_objective_wiz_final'),
            6 => $this->lng->txt('crs_objective_wiz_final_limit'));
            
        $info = array(
            1 => $this->lng->txt('crs_objective_wiz_title_info'),
            2 => $this->lng->txt('crs_objective_wiz_materials_info'),
            3 => $this->lng->txt('crs_objective_wiz_self_info'),
            4 => $this->lng->txt('crs_objective_wiz_self_limit_info'),
            5 => $this->lng->txt('crs_objective_wiz_final_info'),
            6 => $this->lng->txt('crs_objective_wiz_final_limit_info'));

        $links = array(
            1 => $this->ctrl->getLinkTarget($this, 'edit'),
            2 => $this->ctrl->getLinkTarget($this, 'materialAssignment'),
            3 => $this->ctrl->getLinkTarget($this, 'selfAssessmentAssignment'),
            4 => $this->ctrl->getLinkTarget($this, 'selfAssessmentLimits'),
            5 => $this->ctrl->getLinkTarget($this, 'finalTestAssignment'),
            6 => $this->ctrl->getLinkTarget($this, 'finalTestLimits'));
        
        
        

        // checklist gui start
        include_once("./Services/UIComponent/Checklist/classes/class.ilChecklistGUI.php");
        $check_list = new ilChecklistGUI();
        // checklist gui end
        
        if ($_SESSION['objective_mode'] == self::MODE_CREATE) {
            // checklist gui start
            $check_list->setHeading($this->lng->txt('crs_checklist_objective'));
        // checklist gui end
        } else {
            // checklist gui start
            $check_list->setHeading($this->lng->txt('crs_checklist_objective'));
            // checklist gui end
        }
        
        // end-patch lok
        $num = 0;
        foreach ($options as $step => $title) {
            // checklist gui start
            $item_link = "";
            // checklist gui end

            // begin-patch lok
            if ($step == 3 and (!$this->getSettings()->worksWithInitialTest() or $this->getSettings()->hasSeparateInitialTests())) {
                continue;
            }
            if ($step == 4 and (!$this->getSettings()->worksWithInitialTest() or $this->getSettings()->hasSeparateInitialTests())) {
                continue;
            }
            if ($step == 5 and $this->getSettings()->hasSeparateQualifiedTests()) {
                continue;
            }
            if ($step == 6 and $this->getSettings()->hasSeparateQualifiedTests()) {
                continue;
            }
            if ($step == 4 and $this->isRandomTestType(ilLOSettings::TYPE_TEST_INITIAL)) {
                continue;
            }
            if ($step == 6 and $this->isRandomTestType(ilLOSettings::TYPE_TEST_QUALIFIED)) {
                continue;
            }
            $num++;
            // end-patch lok
            
            if ($_SESSION['objective_mode'] == self::MODE_UPDATE) {
                $hide_link = false;
                if ($step == 4 and !count($this->objectives_qst_obj->getSelfAssessmentQuestions())) {
                    $hide_link = true;
                }
                if ($step == 6 and !count($this->objectives_qst_obj->getFinalTestQuestions())) {
                    $hide_link = true;
                }
                // begin-patch lok
                if ($step == 3 and !$this->getSettings()->worksWithInitialTest()) {
                    $hide_link = true;
                }
                if ($step == 4 and !$this->getSettings()->worksWithInitialTest()) {
                    $hide_link = true;
                }
                if (!$hide_link) {
                    // checklist gui start
                    $item_link = $links[$step];
                    // checklist gui end
                }
            }
            
            // checklist gui start
            $check_list->addEntry($title, $item_link, ilChecklistGUI::STATUS_NO_STATUS, ($step == $a_step_number));
            // checklist gui end
        }

        // checklist gui start
        $GLOBALS['DIC']["tpl"]->setRightContent($check_list->getHTML());
        // checklist gui end
    }
}
