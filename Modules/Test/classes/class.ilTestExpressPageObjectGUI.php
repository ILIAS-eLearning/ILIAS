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
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assNumericGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assTextSubsetGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assSingleChoiceGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: assTextQuestionGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: ilRatingGUI, ilPublicUserProfileGUI, ilAssQuestionPageGUI, ilNoteGUI
 * @ilCtrl_Calls ilTestExpressPageObjectGUI: ilObjQuestionPoolGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assNumericGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assTextSubsetGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assSingleChoiceGUI
 * @ilCtrl_IsCalledBy ilTestExpressPageObjectGUI: assTextQuestionGUI, assFormulaQuestionGUI
 */
class ilTestExpressPageObjectGUI extends ilAssQuestionPageGUI
{
    protected $test_object;

    private ilTree $tree;
    private ilDBInterface $db;
    private ilComponentRepository $component_repository;

    public function nextQuestion()
    {
        $obj = new ilObjTest($this->testrequest->getRefId());
        $questions = array_keys($obj->getQuestionTitlesAndIndexes());

        $pos = array_search($this->testrequest->raw('q_id'), $questions);

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
        $obj = new ilObjTest($this->testrequest->getRefId());
        $questions = array_keys($obj->getQuestionTitlesAndIndexes());

        $pos = array_search($this->testrequest->raw('q_id'), $questions);

        if ($pos !== false) {
            $next = $questions[$pos - 1];
        } else {
            $next = $questions[0];
        }

        $this->ctrl->setParameter($this, 'q_id', $next);
        $link = $this->ctrl->getLinkTarget($this, 'edit', '', '', false);

        ilUtil::redirect($link);
    }

    public function __construct($a_id = 0, $a_old_nr = 0, $test_object = null)
    {
        global $DIC;
        $this->tree = $DIC['tree'];
        $this->db = $DIC['ilDB'];
        $this->component_repository = $DIC['component.repository'];
        $this->test_object = $test_object;

        parent::__construct($a_id, $a_old_nr);
    }

    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilobjquestionpoolgui':

                $nodeParts = explode(':', $this->testrequest->raw('cmdNode'));

                $params = [
                    'ref_id' => $this->testrequest->getRefId(),
                    'calling_test' => $this->testrequest->getRefId(),
                    'q_id' => $this->testrequest->getQuestionId(),
                    'cmd' => $this->testrequest->raw('cmd'),
                    'cmdClass' => $this->testrequest->raw('cmdClass'),
                    'baseClass' => 'ilObjQuestionPoolGUI',
                    'test_express_mode' => '1'
                ];

                ilUtil::redirect(
                    'ilias.php' . ilUtil::appendUrlParameterString(
                        '?' . http_build_query($params, null, '&'),
                        'cmdNode=' . ($nodeParts[count($nodeParts) - 2] . ':' . $nodeParts[count($nodeParts) - 1])
                    )
                );

                break;

            case 'ilpageeditorgui':

                if (!$this->getEnableEditing()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                    $this->ctrl->redirect($this, 'preview');
                }

                $page_editor = new ilPageEditorGUI($this->getPageObject(), $this);
                //$page_editor->setLocator($this->locator);
                $page_editor->setHeader($this->getHeader());
                $page_editor->setPageBackTitle($this->page_back_title);
                $page_editor->setIntLinkReturn($this->int_link_return);

                $this->ctrl->saveParameterByClass('ilpageeditorgui', 'q_mode');

                $ret = $this->ctrl->forwardCommand($page_editor);
                if ($ret != '') {
                    $this->tpl->setContent($ret);
                }
                break;

            case '':
            case 'iltestexpresspageobjectgui':
                if ($cmd == 'view') {
                    $cmd = 'showPage';
                    $this->ctrl->setCmd($cmd);
                }

                $q_gui = assQuestionGUI::_getQuestionGUI('', (int) $this->testrequest->raw('q_id'));

                if ($q_gui->object) {
                    $obj = ilObjectFactory::getInstanceByRefId((int) $this->testrequest->getRefId());
                    $q_gui->object->setObjId($obj->getId());
                }

                $cmds = [
                    'handleToolbarCommand',
                    'addQuestion',
                    'questions',
                    'insertQuestions',
                    'browseForQuestions',
                    'filterAvailableQuestions',
                    'resetfilterAvailableQuestions'
                ];

                if (in_array($cmd, $cmds)) {
                    return $this->$cmd();
                } elseif ($q_gui->object) {
                    $total = $this->test_object->evalTotalPersons();

                    $this->setOutputMode($total == 0 ? ilPageObjectGUI::EDIT : ilPageObjectGUI::PREVIEW);

                    if ($total != 0) {
                        $link = $DIC->ui()->factory()->link()->standard(
                            $DIC->language()->txt('test_has_datasets_warning_page_view_link'),
                            $DIC->ctrl()->getLinkTargetByClass(['ilTestResultsGUI', 'ilParticipantsTestResultsGUI'])
                        );

                        $message = $DIC->language()->txt('test_has_datasets_warning_page_view');

                        $msgBox = $DIC->ui()->factory()->messageBox()->info($message)->withLinks([$link]);

                        $DIC->ui()->mainTemplate()->setCurrentBlock('mess');
                        $DIC->ui()->mainTemplate()->setVariable(
                            'MESSAGE',
                            $DIC->ui()->renderer()->render($msgBox)
                        );
                        $DIC->ui()->mainTemplate()->parseCurrentBlock();
                    }

                    if ((in_array($cmd, ['view', 'showPage']) || $cmd == 'edit') && $this->test_object->evalTotalPersons()) {
                        return $this->showPage();
                    }

                    return parent::executeCommand();
                }

                break;

            default:
                $type = ilObjQuestionPool::getQuestionTypeByTypeId(ilUtil::stripSlashes((string) $this->testrequest->raw('qtype')));

                if (!$this->testrequest->raw('q_id')) {
                    $q_gui = $this->addPageOfQuestions(preg_replace('/(.*?)gui/i', '$1', $this->testrequest->raw('sel_question_types')));
                    $q_gui->setQuestionTabs();

                    $this->ctrl->forwardCommand($q_gui);
                    break;
                }

                $this->ctrl->setReturn($this, 'questions');

                $q_gui = assQuestionGUI::_getQuestionGUI($type, (int) $this->testrequest->raw('q_id'));
                if ($q_gui->object) {
                    $obj = ilObjectFactory::getInstanceByRefId((int) $this->testrequest->getRefId());
                    $q_gui->object->setObjId($obj->getId());
                }

                $this->ctrl->saveParameterByClass('ilpageeditorgui', 'q_id');
                $this->ctrl->saveParameterByClass('ilpageeditorgui', 'q_mode');

                $q_gui->setQuestionTabs();
                $this->ctrl->forwardCommand($q_gui);
                break;
        }
        return '';
    }

    public function addPageOfQuestions($type = ''): assQuestionGUI
    {
        if (!$type) {
            $qtype = $this->testrequest->raw('qtype');
            $pool = new ilObjQuestionPool();
            $type = ilObjQuestionPool::getQuestionTypeByTypeId($qtype);
        }

        $this->ctrl->setReturn($this, 'questions');

        $q_gui = assQuestionGUI::_getQuestionGUI($type);

        $obj = ilObjectFactory::getInstanceByRefId($this->testrequest->getRefId());

        $q_gui->object->setObjId($obj->getId());

        return $q_gui;
    }

    public function handleToolbarCommand()
    {
        if ($this->testrequest->raw('qtype')) {
            $questionType = ilObjQuestionPool::getQuestionTypeByTypeId($this->testrequest->raw('qtype'));
        } elseif ($this->testrequest->raw('sel_question_types')) {
            $questionType = $this->testrequest->raw('sel_question_types');
        }

        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $addContEditMode = $this->testrequest->raw('add_quest_cont_edit_mode');
        } else {
            $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
        }

        $q_gui = assQuestionGUI::_getQuestionGUI($questionType);

        $q_gui->object->setObjId(ilObject::_lookupObjectId($this->testrequest->getRefId()));
        $q_gui->object->setAdditionalContentEditingMode($addContEditMode);

        $q_gui->object->createNewQuestion();

        $previousQuestionId = $this->testrequest->raw('position');

        switch ($this->testrequest->raw('usage')) {
            case 3: // existing pool

                $this->ctrl->setParameterByClass('ilobjtestgui', 'sel_qpl', $this->testrequest->raw('sel_qpl'));
                $this->ctrl->setParameterByClass('ilobjtestgui', 'sel_question_types', $questionType);
                $this->ctrl->setParameterByClass('ilobjtestgui', 'q_id', $q_gui->object->getId());
                $this->ctrl->setParameterByClass('ilobjtestgui', 'prev_qid', $previousQuestionId);

                if ($this->testrequest->raw('test_express_mode')) {
                    $this->ctrl->setParameterByClass('ilobjtestgui', 'test_express_mode', 1);
                }

                if ($this->testrequest->isset('add_quest_cont_edit_mode')) {
                    $this->ctrl->setParameterByClass(
                        'ilobjtestgui',
                        'add_quest_cont_edit_mode',
                        $this->testrequest->raw('add_quest_cont_edit_mode')
                    );
                }

                $this->ctrl->setParameterByClass('ilobjtestgui', 'usage', 3);
                $this->ctrl->setParameterByClass('ilobjtestgui', 'calling_test', $this->test_object->getId());

                $link = $this->ctrl->getLinkTargetByClass('ilobjtestgui', 'executeCreateQuestion', false, false, false);

                ilUtil::redirect($link);

                break;

            case 2: // new pool

                $this->ctrl->setParameterByClass('ilobjtestgui', 'txt_qpl', $this->testrequest->raw('txt_qpl'));
                $this->ctrl->setParameterByClass('ilobjtestgui', 'sel_question_types', $questionType);
                $this->ctrl->setParameterByClass('ilobjtestgui', 'q_id', $q_gui->object->getId());
                $this->ctrl->setParameterByClass('ilobjtestgui', 'prev_qid', $previousQuestionId);

                if ($this->testrequest->raw('test_express_mode')) {
                    $this->ctrl->setParameterByClass('ilobjtestgui', 'test_express_mode', 1);
                }

                if ($this->testrequest->isset('add_quest_cont_edit_mode')) {
                    $this->ctrl->setParameterByClass(
                        'ilobjtestgui',
                        'add_quest_cont_edit_mode',
                        $this->testrequest->raw('add_quest_cont_edit_mode')
                    );
                }

                $this->ctrl->setParameterByClass('ilobjtestgui', 'usage', 2);
                $this->ctrl->setParameterByClass('ilobjtestgui', 'calling_test', $this->test_object->getId());

                $link = $this->ctrl->getLinkTargetByClass('ilobjtestgui', 'executeCreateQuestion', false, false, false);
                ilUtil::redirect($link);

                break;

            case 1: // no pool
            default:

                $this->redirectToQuestionEditPage($questionType, $q_gui->object->getId(), $previousQuestionId);

                break;
        }
    }

    public function addQuestion(): string
    {
        $subScreenId = ['createQuestion'];

        $this->ctrl->setParameter($this, 'qtype', $this->testrequest->raw('qtype'));

        $form = new ilPropertyFormGUI();

        $this->ctrl->setParameter($this, 'test_express_mode', 1);

        $form->setFormAction($this->ctrl->getFormAction($this, 'handleToolbarCommand'));
        $form->setTitle($this->lng->txt('ass_create_question'));

        $pool = new ilObjQuestionPool();
        $questionTypes = $pool->getQuestionTypes(false, true, false);
        $options = [];

        // question type
        foreach ($questionTypes as $label => $data) {
            $options[$data['question_type_id']] = $label;
        }

        $si = new ilSelectInputGUI($this->lng->txt('question_type'), 'qtype');
        $si->setOptions($options);
        $form->addItem($si, true);

        // position
        $questions = $this->test_object->getQuestionTitlesAndIndexes();
        if ($questions) {
            $si = new ilSelectInputGUI($this->lng->txt('position'), 'position');
            $options = ['0' => $this->lng->txt('first')];
            foreach ($questions as $key => $title) {
                $options[$key] = $this->lng->txt('behind') . ' ' . $title . ' [' . $this->lng->txt('question_id_short') . ': ' . $key . ']';
            }
            $si->setOptions($options);
            $si->setValue($this->testrequest->raw('q_id'));
            $form->addItem($si, true);
        }

        // content editing mode
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $subScreenId[] = 'editMode';

            $ri = new ilRadioGroupInputGUI($this->lng->txt('tst_add_quest_cont_edit_mode'), 'add_quest_cont_edit_mode');

            $option_ipe = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            );
            $option_ipe->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_IPE_info'));
            $ri->addOption($option_ipe);

            $option_rte = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
            $option_rte->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_RTE_info'));
            $ri->addOption($option_rte);

            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE);

            $form->addItem($ri, true);
        } else {
            $hi = new ilHiddenInputGUI('question_content_editing_type');
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);
            $form->addItem($hi, true);
        }

        $subScreenId[] = 'poolSelect';

        // use pool
        $usage = new ilRadioGroupInputGUI($this->lng->txt('assessment_pool_selection'), 'usage');
        $usage->setRequired(true);
        $no_pool = new ilRadioOption($this->lng->txt('assessment_no_pool'), 1);
        $usage->addOption($no_pool);
        $existing_pool = new ilRadioOption($this->lng->txt('assessment_existing_pool'), 3);
        $usage->addOption($existing_pool);
        $new_pool = new ilRadioOption($this->lng->txt('assessment_new_pool'), 2);
        $usage->addOption($new_pool);
        $form->addItem($usage);

        $usage->setValue(1);

        $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, 'write');
        $pools_data = [];
        foreach ($questionpools as $key => $p) {
            $pools_data[$key] = $p['title'];
        }
        $pools = new ilSelectInputGUI($this->lng->txt('select_questionpool'), 'sel_qpl');
        $pools->setOptions($pools_data);
        $existing_pool->addSubItem($pools);

        $name = new ilTextInputGUI($this->lng->txt('name'), 'txt_qpl');
        $name->setSize(50);
        $name->setMaxLength(50);
        $new_pool->addSubItem($name);

        $form->addCommandButton('handleToolbarCommand', $this->lng->txt('create'));
        $form->addCommandButton('questions', $this->lng->txt('cancel'));

        $this->help->setSubScreenId(implode('_', $subScreenId));

        return $form->getHTML();
    }

    public function questions()
    {
        $this->ctrl->saveParameterByClass('ilobjtestgui', 'q_id');
        $this->ctrl->redirectByClass('ilobjtestgui', 'showPage');
    }

    private function redirectToQuestionEditPage($questionType, $qid, $prev_qid)
    {
        $cmdClass = $questionType . 'GUI';

        $this->ctrl->setParameterByClass($cmdClass, 'ref_id', $this->testrequest->getRefId());
        $this->ctrl->setParameterByClass($cmdClass, 'sel_question_types', $questionType);
        $this->ctrl->setParameterByClass($cmdClass, 'test_ref_id', $this->testrequest->getRefId());
        $this->ctrl->setParameterByClass($cmdClass, 'calling_test', $this->testrequest->getRefId());
        $this->ctrl->setParameterByClass($cmdClass, 'q_id', $qid);
        $this->ctrl->setParameterByClass($cmdClass, 'prev_qid', $prev_qid);

        if ($this->testrequest->raw('test_express_mode')) {
            $this->ctrl->setParameterByClass($cmdClass, 'test_express_mode', 1);
        }

        $this->ctrl->redirectByClass(
            ['ilRepositoryGUI', 'ilObjTestGUI', $questionType . 'GUI'],
            'editQuestion'
        );
    }

    private function redirectToQuestionPoolSelectionPage($questionType, $qid, $prev_qid)
    {
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'ref_id', $this->testrequest->getRefId());
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'q_id', $qid);
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'sel_question_types', $questionType);
        $this->ctrl->setParameterByClass('ilObjTestGUI', 'prev_qid', $prev_qid);
        $redir = $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'createQuestion', '', false, false);

        ilUtil::redirect($redir);
    }

    public function insertQuestions()
    {
        $selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : [];
        if (!count($selected_array)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_insert_missing_question'), true);
            $this->ctrl->redirect($this, 'browseForQuestions');
        } else {
            $manscoring = false;

            $test_question_set_config_factory = new ilTestQuestionSetConfigFactory(
                $this->tree,
                $this->db,
                $this->lng,
                $this->log,
                $this->component_repository,
                $this->test_object,
                $this->questioninfo
            );
            $test_question_set_config = $test_question_set_config_factory->getQuestionSetConfig();

            foreach ($selected_array as $key => $value) {
                $last_question_id = $this->test_object->insertQuestion($test_question_set_config, $value);

                if (!$manscoring) {
                    $manscoring = $manscoring | assQuestion::_needsManualScoring($value);
                }
            }
            $this->test_object->saveCompleteStatus($test_question_set_config);
            if ($manscoring) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('manscoring_hint'), true);
            } else {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_questions_inserted'), true);
            }

            $this->ctrl->setParameter($this, 'q_id', $last_question_id);
            $this->ctrl->redirect($this, 'showPage');
            return;
        }
    }
}
