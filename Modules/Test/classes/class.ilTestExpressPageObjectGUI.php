<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
include_once 'Modules/Test/classes/class.ilTestExpressPage.php';

/**
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assJavaAppletGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assNumericGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assTextSubsetGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assSingleChoiceGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assTextQuestionGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: ilRatingGUI, ilPublicUserProfileGUI, ilAssQuestionPageGUI, ilNoteGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: ilObjQuestionPoolGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assJavaAppletGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assNumericGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assTextSubsetGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assSingleChoiceGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assTextQuestionGUI, assFormulaQuestionGUI
 */
class ilTestExpressPageObjectGUI extends ilAssQuestionPageGUI
{
    public function nextQuestion()
    {
        $obj = new ilObjTest($_REQUEST['ref_id']);
        $questions = array_keys($obj->getQuestionTitlesAndIndexes());

        $pos = array_search($_REQUEST['q_id'], $questions);

        if ($pos !== false) {
            $next = $questions[$pos + 1];
        } else {
            $next = $questions[0];
        }

        $this->ctrl->setParameter($this, 'q_id', $next);
        $link = $this->ctrl->getLinkTarget($this, 'edit', '', '', false);

        ilUtil::redirect($link);
    }

    public function prevQuestion()
    {
        $obj = new ilObjTest($_REQUEST['ref_id']);
        $questions = array_keys($obj->getQuestionTitlesAndIndexes());

        $pos = array_search($_REQUEST['q_id'], $questions);

        if ($pos !== false) {
            $next = $questions[$pos - 1];
        } else {
            $next = $questions[0];
        }

        $this->ctrl->setParameter($this, 'q_id', $next);
        $link = $this->ctrl->getLinkTarget($this, 'edit', '', '', false);

        ilUtil::redirect($link);
    }
    
    public function __construct($a_id = 0, $a_old_nr = 0)
    {
        parent::__construct($a_id, $a_old_nr);
    }

    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        switch ($next_class) {
            case 'ilobjquestionpoolgui':
                
                $nodeParts = explode(':', $_GET['cmdNode']);

                $params = array(
                    'ref_id' => $_GET['ref_id'],
                    'calling_test' => $_GET['ref_id'],
                    'q_id' => $_GET['q_id'],
                    'cmd' => $_GET['cmd'],
                    'cmdClass' => $_GET['cmdClass'],
                    'baseClass' => 'ilObjQuestionPoolGUI',
                    'test_express_mode' => '1'
                );

                ilUtil::redirect(
                    'ilias.php' . ilUtil::appendUrlParameterString(
                        '?' . http_build_query($params, null, '&'),
                        'cmdNode=' . ($nodeParts[count($nodeParts) - 2] . ':' . $nodeParts[count($nodeParts) - 1])
                    )
                );

                break;

            case "ilpageeditorgui":
                
                if (!$this->getEnableEditing()) {
                    ilUtil::sendFailure($lng->txt("permission_denied"), true);
                    $ilCtrl->redirect($this, "preview");
                }
                
                $page_editor = new ilPageEditorGUI($this->getPageObject(), $this);
                $page_editor->setLocator($this->locator);
                $page_editor->setHeader($this->getHeader());
                $page_editor->setPageBackTitle($this->page_back_title);
                $page_editor->setIntLinkReturn($this->int_link_return);

                $this->ctrl->saveParameterByClass('ilpageeditorgui', 'q_mode');

                $ret = &$this->ctrl->forwardCommand($page_editor);
                
                break;

            case '':
            case 'iltestexpresspageobjectgui':

                include_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';

                if ($cmd == 'view') {
                    $cmd = 'showPage';
                    $ilCtrl->setCmd($cmd);
                }

                $q_gui = assQuestionGUI::_getQuestionGUI('', (int) $_REQUEST["q_id"]);

                if ($q_gui->object) {
                    $obj = ilObjectFactory::getInstanceByRefId((int) $_REQUEST['ref_id']);
                    $q_gui->object->setObjId($obj->getId());
                }

                $cmds = array(
                    'handleToolbarCommand',
                    'addQuestion',
                    'questions',
                    'insertQuestions',
                    'browseForQuestions',
                    'filterAvailableQuestions',
                    'resetfilterAvailableQuestions'
                );
                
                if (in_array($cmd, $cmds)) {
                    return $this->$cmd();
                } elseif ($q_gui->object) {
                    $total = $this->test_object->evalTotalPersons();
            
                    $this->setOutputMode($total == 0 ? IL_PAGE_EDIT : IL_PAGE_PREVIEW);
            
                    if ($total != 0) {
                        $link = $DIC->ui()->factory()->link()->standard(
                            $DIC->language()->txt("test_has_datasets_warning_page_view_link"),
                            $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilParticipantsTestResultsGUI'))
                        );
                        
                        $message = $DIC->language()->txt("test_has_datasets_warning_page_view");
                        
                        $msgBox = $DIC->ui()->factory()->messageBox()->info($message)->withLinks(array($link));
                        
                        $DIC->ui()->mainTemplate()->setCurrentBlock('mess');
                        $DIC->ui()->mainTemplate()->setVariable(
                            'MESSAGE',
                            $DIC->ui()->renderer()->render($msgBox)
                        );
                        $DIC->ui()->mainTemplate()->parseCurrentBlock();
                    }
            
                    if ((in_array($cmd, array('view', 'showPage')) || $cmd == 'edit') && $this->test_object->evalTotalPersons()) {
                        return $this->showPage();
                    }
                    
                    return parent::executeCommand();
                }
                
                break;

            default:
                $type = ilObjQuestionPool::getQuestionTypeByTypeId(ilUtil::stripSlashes((string) $_REQUEST['qtype']));

                if (!$_GET['q_id']) {
                    $q_gui = $this->addPageOfQuestions(preg_replace('/(.*?)gui/i', '$1', $_GET['sel_question_types']));
                    $q_gui->setQuestionTabs();

                    $this->ctrl->forwardCommand($q_gui);
                    break;
                }

                $this->ctrl->setReturn($this, "questions");

                require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
                $q_gui = assQuestionGUI::_getQuestionGUI($type, (int) $_GET['q_id']);
                if ($q_gui->object) {
                    $obj = ilObjectFactory::getInstanceByRefId((int) $_GET['ref_id']);
                    $q_gui->object->setObjId($obj->getId());
                }

                $this->ctrl->saveParameterByClass('ilpageeditorgui', 'q_id');
                $this->ctrl->saveParameterByClass('ilpageeditorgui', 'q_mode');

                $q_gui->setQuestionTabs();
                $this->ctrl->forwardCommand($q_gui);
                break;
        }
    }

    public function addPageOfQuestions($type = '')
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        if (!$type) {
            $qtype = $_REQUEST['qtype'];
            $pool = new ilObjQuestionPool();
            $type = ilObjQuestionPool::getQuestionTypeByTypeId($qtype);
        }
        
        $this->ctrl->setReturn($this, "questions");
        
        include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        $q_gui = &assQuestionGUI::_getQuestionGUI($type);
        
        $obj = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
        
        $q_gui->object->setObjId($obj->getId());
        
        return $q_gui;
    }

    public function handleToolbarCommand()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        
        if ($_REQUEST['qtype']) {
            include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
            $questionType = ilObjQuestionPool::getQuestionTypeByTypeId($_REQUEST['qtype']);
        } elseif ($_REQUEST['sel_question_types']) {
            $questionType = $_REQUEST['sel_question_types'];
        }

        include_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $addContEditMode = $_REQUEST['add_quest_cont_edit_mode'];
        } else {
            $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT;
        }
        
        $q_gui = &assQuestionGUI::_getQuestionGUI($questionType);

        $q_gui->object->setObjId(ilObject::_lookupObjectId($_GET['ref_id']));
        $q_gui->object->setAdditionalContentEditingMode($addContEditMode);

        $q_gui->object->createNewQuestion();

        $previousQuestionId = $_REQUEST['position'];

        switch ($_REQUEST['usage']) {
            case 3: // existing pool
                
                $ilCtrl->setParameterByClass('ilobjtestgui', 'sel_qpl', $_REQUEST['sel_qpl']);
                $ilCtrl->setParameterByClass('ilobjtestgui', 'sel_question_types', $questionType);
                $ilCtrl->setParameterByClass('ilobjtestgui', 'q_id', $q_gui->object->getId());
                $ilCtrl->setParameterByClass('ilobjtestgui', 'prev_qid', $previousQuestionId);
                
                if ($_REQUEST['test_express_mode']) {
                    $ilCtrl->setParameterByClass('ilobjtestgui', 'test_express_mode', 1);
                }
                
                if (isset($_REQUEST['add_quest_cont_edit_mode'])) {
                    $ilCtrl->setParameterByClass(
                        'ilobjtestgui',
                        'add_quest_cont_edit_mode',
                        $_REQUEST['add_quest_cont_edit_mode']
                    );
                }
                
                $ilCtrl->setParameterByClass('ilobjtestgui', 'usage', 3);
                $ilCtrl->setParameterByClass('ilobjtestgui', 'calling_test', $this->test_object->getId());

                $link = $ilCtrl->getLinkTargetByClass('ilobjtestgui', 'executeCreateQuestion', false, false, false);
                
                ilUtil::redirect($link);
                
                break;
                
            case 2: // new pool
                
                $ilCtrl->setParameterByClass('ilobjtestgui', 'txt_qpl', $_REQUEST['txt_qpl']);
                $ilCtrl->setParameterByClass('ilobjtestgui', 'sel_question_types', $questionType);
                $ilCtrl->setParameterByClass('ilobjtestgui', 'q_id', $q_gui->object->getId());
                $ilCtrl->setParameterByClass('ilobjtestgui', 'prev_qid', $previousQuestionId);
                
                if ($_REQUEST['test_express_mode']) {
                    $ilCtrl->setParameterByClass('ilobjtestgui', 'test_express_mode', 1);
                }
                
                if (isset($_REQUEST['add_quest_cont_edit_mode'])) {
                    $ilCtrl->setParameterByClass(
                        'ilobjtestgui',
                        'add_quest_cont_edit_mode',
                        $_REQUEST['add_quest_cont_edit_mode']
                    );
                }
                
                $ilCtrl->setParameterByClass('ilobjtestgui', 'usage', 2);
                $ilCtrl->setParameterByClass('ilobjtestgui', 'calling_test', $this->test_object->getId());

                $link = $ilCtrl->getLinkTargetByClass('ilobjtestgui', 'executeCreateQuestion', false, false, false);
                ilUtil::redirect($link);
                
                break;
                
            case 1: // no pool
            default:
                
                $this->redirectToQuestionEditPage($questionType, $q_gui->object->getId(), $previousQuestionId);
                
                break;
        }
    }

    public function addQuestion()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */
        
        $subScreenId = array('createQuestion');

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

        $ilCtrl->setParameter($this, 'qtype', $_REQUEST['qtype']);

        $form = new ilPropertyFormGUI();

        $ilCtrl->setParameter($this, 'test_express_mode', 1);

        $form->setFormAction($ilCtrl->getFormAction($this, "handleToolbarCommand"));
        $form->setTitle($lng->txt("ass_create_question"));
        include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';

        $pool = new ilObjQuestionPool();
        $questionTypes = $pool->getQuestionTypes(false, true, false);
        $options = array();

        // question type
        foreach ($questionTypes as $label => $data) {
            $options[$data['question_type_id']] = $label;
        }

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("question_type"), "qtype");
        $si->setOptions($options);
        $form->addItem($si, true);

        // position
        $questions = $this->test_object->getQuestionTitlesAndIndexes();
        if ($questions) {
            $si = new ilSelectInputGUI($lng->txt("position"), "position");
            $options = array('0' => $lng->txt('first'));
            foreach ($questions as $key => $title) {
                $options[$key] = $lng->txt('behind') . ' ' . $title . ' [' . $this->lng->txt('question_id_short') . ': ' . $key . ']';
            }
            $si->setOptions($options);
            $si->setValue($_REQUEST['q_id']);
            $form->addItem($si, true);
        }
        
        // content editing mode
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $subScreenId[] = 'editMode';
            
            $ri = new ilRadioGroupInputGUI($lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");
            
            $ri->addOption(new ilRadioOption(
                $lng->txt('tst_add_quest_cont_edit_mode_default'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT
            ));

            $ri->addOption(new ilRadioOption(
                $lng->txt('tst_add_quest_cont_edit_mode_page_object'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
            ));
            
            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);

            $form->addItem($ri, true);
        } else {
            $hi = new ilHiddenInputGUI("question_content_editing_type");
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);
            $form->addItem($hi, true);
        }

        if ($this->test_object->getPoolUsage()) {
            $subScreenId[] = 'poolSelect';
            
            // use pool
            $usage = new ilRadioGroupInputGUI($this->lng->txt("assessment_pool_selection"), "usage");
            $usage->setRequired(true);
            $no_pool = new ilRadioOption($this->lng->txt("assessment_no_pool"), 1);
            $usage->addOption($no_pool);
            $existing_pool = new ilRadioOption($this->lng->txt("assessment_existing_pool"), 3);
            $usage->addOption($existing_pool);
            $new_pool = new ilRadioOption($this->lng->txt("assessment_new_pool"), 2);
            $usage->addOption($new_pool);
            $form->addItem($usage);

            $usage->setValue(1);

            $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, "write");
            $pools_data = array();
            foreach ($questionpools as $key => $p) {
                $pools_data[$key] = $p['title'];
            }
            $pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_qpl");
            $pools->setOptions($pools_data);
            $existing_pool->addSubItem($pools);

            $name = new ilTextInputGUI($this->lng->txt("name"), "txt_qpl");
            $name->setSize(50);
            $name->setMaxLength(50);
            $new_pool->addSubItem($name);
        }

        $form->addCommandButton("handleToolbarCommand", $lng->txt("create"));
        $form->addCommandButton("questions", $lng->txt("cancel"));
        
        $ilHelp->setSubScreenId(implode('_', $subScreenId));

        return $tpl->setContent($form->getHTML());
    }

    public function questions()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->saveParameterByClass('ilobjtestgui', 'q_id');
        
        $ilCtrl->redirectByClass('ilobjtestgui', 'showPage');
    }

    private function redirectToQuestionEditPage($questionType, $qid, $prev_qid)
    {
        $cmdClass = $questionType . 'GUI';
        
        $this->ctrl->setParameterByClass($cmdClass, 'ref_id', $_GET['ref_id']);
        $this->ctrl->setParameterByClass($cmdClass, 'sel_question_types', $questionType);
        $this->ctrl->setParameterByClass($cmdClass, 'test_ref_id', $_GET['ref_id']);
        $this->ctrl->setParameterByClass($cmdClass, 'calling_test', $_GET['ref_id']);
        $this->ctrl->setParameterByClass($cmdClass, 'q_id', $qid);
        $this->ctrl->setParameterByClass($cmdClass, 'prev_qid', $prev_qid);
        
        if ($_REQUEST['test_express_mode']) {
            $this->ctrl->setParameterByClass($cmdClass, 'test_express_mode', 1);
        }
        
        $this->ctrl->redirectByClass(
            array('ilRepositoryGUI', 'ilObjTestGUI', $questionType . "GUI"),
            'editQuestion'
        );
    }

    private function redirectToQuestionPoolSelectionPage($questionType, $qid, $prev_qid)
    {
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'ref_id', $_REQUEST['ref_id']);
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'q_id', $qid);
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'sel_question_types', $questionType);
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'prev_qid', $prev_qid);
        $redir = $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'createQuestion', '', false, false);

        ilUtil::redirect($redir);
    }

    public function insertQuestions()
    {
        $selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : array();
        if (!count($selected_array)) {
            ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"), true);
            $this->ctrl->redirect($this, "browseForQuestions");
        } else {
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            $manscoring = false;
                
            global $DIC;
            $tree = $DIC['tree'];
            $ilDB = $DIC['ilDB'];
            $ilPluginAdmin = $DIC['ilPluginAdmin'];

            require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
            $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $this->test_object);
            $testQuestionSetConfig = $testQuestionSetConfigFactory->getQuestionSetConfig();

            foreach ($selected_array as $key => $value) {
                $last_question_id = $this->test_object->insertQuestion($testQuestionSetConfig, $value);
                
                if (!$manscoring) {
                    $manscoring = $manscoring | assQuestion::_needsManualScoring($value);
                }
            }
            $this->test_object->saveCompleteStatus($testQuestionSetConfig);
            if ($manscoring) {
                ilUtil::sendInfo($this->lng->txt("manscoring_hint"), true);
            } else {
                ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), true);
            }
            
            $this->ctrl->setParameter($this, 'q_id', $last_question_id);
            $this->ctrl->redirect($this, "showPage");
            return;
        }
    }
}
