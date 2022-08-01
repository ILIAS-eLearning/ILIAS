<?php declare(strict_types=1);

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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilObjPollGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilObjPollGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjPollGUI: ilPermissionGUI, ilObjectCopyGUI, ilExportGUI
 */
class ilObjPollGUI extends ilObject2GUI
{
    protected ilHelpGUI $help;
    protected ilTabsGUI $tabs;
    protected ilNavigationHistory $nav_history;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->help = $DIC["ilHelp"];
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->locator = $DIC["ilLocator"];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        
        $this->lng->loadLanguageModule("poll");
    }

    public function getType() : string
    {
        return "poll";
    }
    
    protected function afterSave(ilObject $new_object) : void
    {
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this, "render");
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form) : void
    {
        // activation
        $this->lng->loadLanguageModule('rep');
        
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $a_form->addItem($section);
        
        // additional info only with multiple references
        $act_obj_info = $act_ref_info = "";
        if (count(ilObject::_getAllReferences($this->object->getId())) > 1) {
            $act_obj_info = ' ' . $this->lng->txt('rep_activation_online_object_info');
            $act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
        }
        
        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online');
        $online->setInfo($this->lng->txt('poll_activation_online_info') . $act_obj_info);
        $a_form->addItem($online);

        $dur = new ilDateDurationInputGUI($this->lng->txt('rep_visibility_until'), "access_period");
        $dur->setShowTime(true);
        $a_form->addItem($dur);
        
        
        // period/results
        
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('poll_voting_period_and_results'));
        $a_form->addItem($section);
        
        $vdur = new ilDateDurationInputGUI($this->lng->txt('poll_voting_period_limited'), "voting_period");
        $vdur->setShowTime(true);
        $a_form->addItem($vdur);

        $results = new ilRadioGroupInputGUI($this->lng->txt("poll_view_results"), "results");
        $results->setRequired(true);
        $results->addOption(new ilRadioOption(
            $this->lng->txt("poll_view_results_always"),
            (string) ilObjPoll::VIEW_RESULTS_ALWAYS
        ));
        $results->addOption(new ilRadioOption(
            $this->lng->txt("poll_view_results_never"),
            (string) ilObjPoll::VIEW_RESULTS_NEVER
        ));
        $results->addOption(new ilRadioOption(
            $this->lng->txt("poll_view_results_after_vote"),
            (string) ilObjPoll::VIEW_RESULTS_AFTER_VOTE
        ));
        $results->addOption(new ilRadioOption(
            $this->lng->txt("poll_view_results_after_period"),
            (string) ilObjPoll::VIEW_RESULTS_AFTER_PERIOD
        ));
        $a_form->addItem($results);
        
        $show_result_as = new ilRadioGroupInputGUI($this->lng->txt("poll_show_results_as"), "show_results_as");
        $show_result_as->setRequired(true);
        $result_bar = new ilRadioOption(
            $this->lng->txt("poll_barchart"),
            (string) ilObjPoll::SHOW_RESULTS_AS_BARCHART
        );
        $show_result_as->addOption($result_bar);
        $show_result_as->addOption(new ilRadioOption(
            $this->lng->txt("poll_piechart"),
            (string) ilObjPoll::SHOW_RESULTS_AS_PIECHART
        ));
        $a_form->addItem($show_result_as);

        $sort = new ilRadioGroupInputGUI($this->lng->txt("poll_result_sorting"), "sort");
        $sort->setRequired(true);
        $sort->addOption(new ilRadioOption($this->lng->txt("poll_result_sorting_answers"), "0"));
        $sort->addOption(new ilRadioOption($this->lng->txt("poll_result_sorting_votes"), "1"));
        $a_form->addItem($sort);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('poll_comments'));
        $a_form->addItem($section);

        $comment = new ilCheckboxInputGUI($this->lng->txt('poll_comments'), 'comment');
        //$comment->setInfo($this->lng->txt('poll_comments_info'));
        $a_form->addItem($comment);
    }

    protected function getEditFormCustomValues(array &$a_values) : void
    {
        $a_values["online"] = !$this->object->getOfflineStatus();
        $a_values["results"] = $this->object->getViewResults();
        $a_values["access_period"]["start"] = $this->object->getAccessBegin()
            ? new ilDateTime($this->object->getAccessBegin(), IL_CAL_UNIX)
            : null;
        $a_values["access_period"]["end"] = $this->object->getAccessEnd()
            ? new ilDateTime($this->object->getAccessEnd(), IL_CAL_UNIX)
            : null;
        $a_values["voting_period"]["start"] = $this->object->getVotingPeriodBegin()
            ? new ilDateTime($this->object->getVotingPeriodBegin(), IL_CAL_UNIX)
            : null;
        $a_values["voting_period"]["end"] = $this->object->getVotingPeriodEnd()
            ? new ilDateTime($this->object->getVotingPeriodEnd(), IL_CAL_UNIX)
            : null;
        $a_values["sort"] = (string) (int) $this->object->getSortResultByVotes();
        $a_values["comment"] = $this->object->getShowComments();
        $a_values["show_results_as"] = $this->object->getShowResultsAs();
    }
    
    protected function validateCustom(ilPropertyFormGUI $form) : bool
    {
        #20594
        if (!$form->getInput("voting_period") &&
            (int) $form->getInput("results") === ilObjPoll::VIEW_RESULTS_AFTER_PERIOD) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
            $form->getItemByPostVar("results")->setAlert($this->lng->txt("poll_view_results_after_period_impossible"));
            return false;
        }
        return parent::validateCustom($form);
    }

    protected function updateCustom(ilPropertyFormGUI $form) : void
    {
        $this->object->setViewResults((int) $form->getInput("results"));
        $this->object->setOfflineStatus(!((string) $form->getInput("online") === "1"));
        $this->object->setSortResultByVotes((bool) $form->getInput("sort"));
        $this->object->setShowComments((bool) $form->getInput("comment"));
        $this->object->setShowResultsAs((int) $form->getInput("show_results_as"));

        $period = $form->getItemByPostVar("access_period");
        if ($period->getStart() && $period->getEnd()) {
            $this->object->setAccessType(ilObjectActivation::TIMINGS_ACTIVATION);
            $this->object->setAccessBegin($period->getStart()->get(IL_CAL_UNIX));
            $this->object->setAccessEnd($period->getEnd()->get(IL_CAL_UNIX));
        } else {
            $this->object->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
        }
                                                    
        $period = $form->getItemByPostVar("voting_period");
        if ($period->getStart() && $period->getEnd()) {
            $this->object->setVotingPeriod(true);
            $this->object->setVotingPeriodBegin($period->getStart()->get(IL_CAL_UNIX));
            $this->object->setVotingPeriodEnd($period->getEnd()->get(IL_CAL_UNIX));
        } else {
            $this->object->setVotingPeriodBegin(0);
            $this->object->setVotingPeriodEnd(0);
            $this->object->setVotingPeriod(false);
        }
    }

    protected function setTabs() : void
    {
        $this->help->setScreenIdComponent("poll");

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "content",
                $this->lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "")
            );
        }
        
        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );
            
            $this->tabs_gui->addTab(
                "participants",
                $this->lng->txt("poll_result"),
                $this->ctrl->getLinkTarget($this, "showParticipants")
            );
            
            $this->tabs_gui->addTab(
                "export",
                $this->lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        // will add permissions if needed
        parent::setTabs();
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->tpl->loadStandardTemplate();

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $this->getAccessHandler()->checkAccess("read", "", $this->node_id)) {
            $link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "frameset");
            $this->nav_history->addItem($this->node_id, $link, "poll");
        }
        
        switch ($next_class) {
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilpermissiongui":
                $this->prepareOutput();
                $this->tabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case "ilobjectcopygui":
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("poll");
                $this->ctrl->forwardCommand($cp);
                break;
            
            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $this->ctrl->forwardCommand($exp_gui);
                break;

            default:
                parent::executeCommand();
        }
    }
    
    public function render(?ilPropertyFormGUI $a_form = null) : void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"));
            return;
        }
        
        $this->tabs->activateTab("content");

        $message = "";
        if (!$a_form) {
            if ($this->object->countVotes()) {
                $url = $this->ctrl->getLinkTarget($this, "showParticipants");

                $mbox = $this->ui_factory->messageBox()->info($this->lng->txt("poll_votes_no_edit"))
                    ->withLinks([$this->ui_factory->link()->standard(
                        $this->lng->txt("poll_result"),
                        $url
                    )]);

                $message = $this->ui_renderer->render($mbox);
            }
            
            $a_form = $this->initQuestionForm((bool) $this->object->countVotes());
        }
            
        $this->tpl->setPermanentLink('poll', $this->node_id);
        
        $this->tpl->setContent($message . $a_form->getHTML());
    }
    
    protected function initQuestionForm(bool $a_read_only = false) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveQuestion"));
        $form->setTitle($this->lng->txt("obj_poll"));
        
        $question = new ilTextAreaInputGUI($this->lng->txt("poll_question"), "question");
        $question->setRequired(true);
        $question->setCols(40);
        $question->setRows(2);
        $question->setValue($this->object->getQuestion());
        $question->setDisabled($a_read_only);
        $form->addItem($question);
        
        $dimensions = " (" . ilObjPoll::getImageSize() . "px)";
        $img = new ilImageFileInputGUI($this->lng->txt("poll_image") . $dimensions, "image");
        $img->setDisabled($a_read_only);
        $form->addItem($img);
            
        // show existing file
        $file = $this->object->getImageFullPath(true);
        if ($file) {
            $img->setImage($file);
        }
        
        $anonymous = new ilRadioGroupInputGUI($this->lng->txt("poll_mode"), "mode");
        $anonymous->setRequired(true);
        $option = new ilRadioOption($this->lng->txt("poll_mode_anonymous"), "0");
        $option->setInfo($this->lng->txt("poll_mode_anonymous_info"));
        $anonymous->addOption($option);
        $option = new ilRadioOption($this->lng->txt("poll_mode_personal"), "1");
        $option->setInfo($this->lng->txt("poll_mode_personal_info"));
        $anonymous->addOption($option);
        $anonymous->setValue($this->object->getNonAnonymous() ? "1" : "0");
        $anonymous->setDisabled($a_read_only);
        $form->addItem($anonymous);
        
        $nanswers = new ilNumberInputGUI($this->lng->txt("poll_max_number_of_answers"), "nanswers");
        $nanswers->setRequired(true);
        $nanswers->setMinValue(1);
        $nanswers->setSize(3);
        $nanswers->setValue((string) $this->object->getMaxNumberOfAnswers());
        $nanswers->setDisabled($a_read_only);
        $form->addItem($nanswers);
        
        $answers = new ilTextInputGUI($this->lng->txt("poll_answers"), "answers");
        $answers->setRequired(true);
        $answers->setMulti(true, true);
        $answers->setDisabled($a_read_only);
        $form->addItem($answers);
                
        $multi_answers = array();
        foreach ($this->object->getAnswers() as $idx => $item) {
            $answer = (string) ($item['answer'] ?? '');
            if (!$idx) {
                $answers->setValue($answer);
            }
            $multi_answers[] = $answer;
        }
        $answers->setMultiValues($multi_answers);
        
        if (!$a_read_only) {
            $form->addCommandButton("saveQuestion", $this->lng->txt("save"));
        }
        
        return $form;
    }
    
    public function saveQuestion() : void
    {
        $form = $this->initQuestionForm();
        if ($form->checkInput()) {
            $this->object->setQuestion((string) $form->getInput("question"));
            $this->object->setNonAnonymous((bool) $form->getInput("mode"));
                        
            $image = $form->getItemByPostVar("image");
            $res = $form->getFileUpload("image");
            if (!empty($res)) {
                $this->object->uploadImage($res);
            } elseif ($image->getDeletionFlag()) {
                $this->object->deleteImage();
            }
             
            $nr_of_anwers = $this->object->saveAnswers((array) $form->getInput("answers"));
            
            // #15073
            $this->object->setMaxNumberOfAnswers(min((int) $form->getInput("nanswers"), $nr_of_anwers));
            
            if ($this->object->update()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "render");
            }
        }
        
        $form->setValuesByPost();
        $this->render($form);
    }
    
    protected function setParticipantsSubTabs(string $a_active) : void
    {
        if (!$this->object->getNonAnonymous()) {
            return;
        }
        
        $this->tabs->addSubTab(
            "result_answers",
            $this->lng->txt("poll_result_answers"),
            $this->ctrl->getLinkTarget($this, "showParticipants")
        );
        $this->tabs->addSubTab(
            "result_users",
            $this->lng->txt("poll_result_users"),
            $this->ctrl->getLinkTarget($this, "showParticipantVotes")
        );
        
        $this->tabs->activateSubTab($a_active);
    }
    
    public function showParticipants() : void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"));
            return;
        }
        
        $this->tabs->activateTab("participants");
        $this->setParticipantsSubTabs("result_answers");

        $tbl = new ilPollAnswerTableGUI($this, "showParticipants");
        $this->tpl->setContent($tbl->getHTML());
    }
    
    public function showParticipantVotes() : void
    {
        if (!$this->checkPermissionBool("write") ||
            !$this->object->getNonAnonymous()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"));
            return;
        }
        
        $this->tabs->activateTab("participants");
        $this->setParticipantsSubTabs("result_users");

        $tbl = new ilPollUserTableGUI($this, "showParticipantVotes");
        $this->tpl->setContent($tbl->getHTML());
    }
    
    public function confirmDeleteAllVotes() : void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"));
            return;
        }
        
        $this->tabs->activateTab("participants");
        
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("poll_delete_votes_sure"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "showParticipants");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteAllVotes");

        $this->tpl->setContent($cgui->getHTML());
    }
    
    public function deleteAllVotes() : void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"));
            return;
        }
        
        $this->object->deleteAllVotes();
            
        $this->ctrl->redirect($this, "showParticipants");
    }
                
    public function vote() : void
    {
        global $DIC;
        $aw = (array) ($DIC->http()->request()->getParsedBody()['aw'] ?? array());

        $valid = true;
        if ($this->object->getMaxNumberOfAnswers() > 1) {
            if (count($aw) > $this->object->getMaxNumberOfAnswers()) {
                $valid = false;
            }
            if (!count($aw)) {
                $valid = false;
            }
        } elseif ((int) !$aw) {
            $valid = false;
        }

        $session_last_poll_vote = ilSession::get('last_poll_vote');
        if ($valid) {
            unset($session_last_poll_vote[$this->object->getId()]);
            ilSession::set('last_poll_vote', $session_last_poll_vote);
            $this->object->saveVote($this->user->getId(), $aw);
            
            $this->sendNotifications();
        } else {
            $session_last_poll_vote[$this->object->getId()] = $aw;
            ilSession::set('last_poll_vote', $session_last_poll_vote);
        }

        ilUtil::redirect(ilLink::_getLink($this->tree->getParentId($this->ref_id)));
    }
    
    public function subscribe() : void
    {
        ilNotification::setNotification(ilNotification::TYPE_POLL, $this->user->getId(), $this->object->getId(), true);
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        ilUtil::redirect(ilLink::_getLink($this->tree->getParentId($this->ref_id)));
    }
    
    public function unsubscribe() : void
    {
        ilNotification::setNotification(ilNotification::TYPE_POLL, $this->user->getId(), $this->object->getId(), false);
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        ilUtil::redirect(ilLink::_getLink($this->tree->getParentId($this->ref_id)));
    }
    
    protected function sendNotifications() : void
    {
        // recipients
        $users = ilNotification::getNotificationsForObject(
            ilNotification::TYPE_POLL,
            $this->object->getId(),
            null,
            true
        );
        if (!count($users)) {
            return;
        }

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("poll"));
        $ntf->setRefId($this->ref_id);
        
        if ($this->object->getNonAnonymous()) {
            $ntf->setChangedByUserId($this->user->getId());
        }
        
        $ntf->setSubjectLangId('poll_vote_notification_subject');
        $ntf->setIntroductionLangId('poll_vote_notification_body');
        $ntf->setGotoLangId('poll_vote_notification_link');
        $ntf->setReasonLangId('poll_vote_notification_reason');
                
        $notified = $ntf->sendMailAndReturnRecipients($users, null, "read");

        ilNotification::updateNotificationTime(ilNotification::TYPE_POLL, $this->object->getId(), $notified);
    }

    protected function addLocatorItems() : void
    {
        if (is_object($this->object)) {
            $this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
        }
    }

    public static function _goto(string $a_target) : void
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilAccess = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        
        $id = explode("_", $a_target);
        $ref_id = (int) ($id[0] ?? 0);
                    
        // #13728 - used in notification mostly
        if ($ilAccess->checkAccess("write", "", $ref_id)) {
            $ilCtrl->setParameterByClass(self::class, "ref_id", $ref_id);
            $ilCtrl->redirectByClass([ilRepositoryGUI::class, self::class,], "showParticipants");
        } else {
            // is sideblock: so show parent instead
            $container_id = $tree->getParentId($ref_id);

            // #11810
            ilUtil::redirect(ilLink::_getLink($container_id) .
                "#poll" . ilObject::_lookupObjId($id[0]));
        }
    }
}
