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
 * Object-based submissions (ends up as static file)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilExSubmissionTextGUI:
 */
class ilExSubmissionTextGUI extends ilExSubmissionBaseGUI
{
    protected ilObjUser $user;
    protected ilHelpGUI $help;

    public function __construct(
        ilObjExercise $a_exercise,
        ilExSubmission $a_submission
    ) {
        global $DIC;

        parent::__construct($a_exercise, $a_submission);
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->assignment ||
            $this->assignment->getType() != ilExAssignment::TYPE_TEXT ||
            !$this->submission->canView()) {
            return;
        }
        
        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("showassignmenttext");
        
        switch ($class) {
            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }
    
    public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ) : void {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $button = ilLinkButton::getInstance();
        if ($a_submission->canSubmit()) {
            $button->setPrimary(true);
            $button->setCaption("exc_text_assignment_edit");
            $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTextGUI"), "editAssignmentText"));
        } else {
            $button->setCaption("exc_text_assignment_show");
            $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTextGUI"), "showAssignmentText"));
        }
        $files_str = $button->render();

        $a_info->addProperty($lng->txt("exc_files_returned_text"), $files_str);
    }
    
    
    //
    // TEXT ASSIGNMENT (EDIT)
    //
    
    protected function initAssignmentTextForm(
        bool $a_read_only = false
    ) : ilPropertyFormGUI {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("exc_assignment") . " \"" . $this->assignment->getTitle() . "\"");
            
        if (!$a_read_only) {
            $text = new ilTextAreaInputGUI($this->lng->txt("exc_your_text"), "atxt");
            $text->setRequired(
                $this->mandatory_manager->isMandatoryForUser($this->submission->getAssignment()->getId(), $this->user->getId())
            );
            $text->setRows(40);
            $text->setMaxNumOfChars($this->assignment->getMaxCharLimit());
            $text->setMinNumOfChars($this->assignment->getMinCharLimit());

            if ($text->isCharLimited()) {
                $char_msg = "";
                if ($this->assignment->getMinCharLimit() !== 0) {
                    $char_msg .= $lng->txt("exc_min_char_limit") . ": " . $this->assignment->getMinCharLimit();
                }
                if ($this->assignment->getMaxCharLimit() !== 0) {
                    $char_msg .= " " . $lng->txt("exc_max_char_limit") . ": " . $this->assignment->getMaxCharLimit();
                }
                $text->setInfo($char_msg);
            }

            $form->addItem($text);
            
            // custom rte tags
            $text->setUseRte(true);
            $text->setRTESupport($this->submission->getUserId(), "exca~", "exc_ass");
            
            // see ilObjForumGUI
            $text->disableButtons(array(
                'charmap',
                'undo',
                'redo',
                'alignleft',
                'aligncenter',
                'alignright',
                'alignjustify',
                'anchor',
                'fullscreen',
                'cut',
                'copy',
                'paste',
                'pastetext',
                'code',
                // 'formatselect' #13234
            ));
            
            $form->setFormAction($ilCtrl->getFormAction($this, "updateAssignmentText"));
            $form->addCommandButton("updateAssignmentTextAndReturn", $this->lng->txt("save_return"));
            $form->addCommandButton("updateAssignmentText", $this->lng->txt("save"));
            $form->addCommandButton("returnToParent", $this->lng->txt("cancel"));
        } else {
            $form->setFormAction($ilCtrl->getFormAction($this, "returnToParent"));
            $text = new ilNonEditableValueGUI($this->lng->txt("exc_files_returned_text"), "atxt", true);
            $form->addItem($text);
        }
        
        return $form;
    }
    
    public function editAssignmentTextObject(
        ilPropertyFormGUI $a_form = null
    ) : void {
        $ilCtrl = $this->ctrl;

        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exercise_time_over"), true);
            $ilCtrl->redirect($this, "returnToParent");
        }

        $this->triggerAssignmentTool();

        $this->handleTabs();

        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("exc");
        $ilHelp->setScreenId("text_submission");

        if ($a_form === null) {
            $a_form = $this->initAssignmentTextForm();

            $files = $this->submission->getFiles();
            if ($files !== []) {
                $files = array_shift($files);
                if (trim($files["atext"]) !== '' && trim($files["atext"]) !== '0') {
                    $text = $a_form->getItemByPostVar("atxt");
                    // mob id to mob src
                    $text->setValue(ilRTE::_replaceMediaObjectImageSrc($files["atext"], 1));
                }
            }
        }
    
        $this->tpl->setContent($a_form->getHTML());
    }
    
    public function updateAssignmentTextAndReturnObject() : void
    {
        $this->updateAssignmentTextObject(true);
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    public function updateAssignmentTextObject(
        bool $a_return = false
    ) : void {
        $ilCtrl = $this->ctrl;
        
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exercise_time_over"), true);
            $ilCtrl->redirect($this, "returnToParent");
        }
        
        $form = $this->initAssignmentTextForm();
        
        // we are not using a purifier, so we have to set the valid RTE tags
        // :TODO:
        $rte = $form->getItemByPostVar("atxt");
        $rte->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("exc_ass"));
        
        if ($form->checkInput()) {
            $text = trim($form->getInput("atxt"));
                                    
            $returned_id = $this->submission->updateTextSubmission(
                // mob src to mob id
                ilRTE::_replaceMediaObjectImageSrc($text, 0)
            );
            
            // no empty text
            if ($returned_id) {
                // #16532 - always send notifications
                $this->handleNewUpload();
                
                // mob usage
                $mobs = ilRTE::_getMediaObjects($text, 0);
                foreach ($mobs as $mob) {
                    if (ilObjMediaObject::_exists($mob)) {
                        ilObjMediaObject::_removeUsage($mob, 'exca~:html', $this->submission->getUserId());
                        ilObjMediaObject::_saveUsage($mob, 'exca:html', $returned_id);
                    }
                }
            } else {
                $this->handleRemovedUpload();
            }
            
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_text_saved"), true);
            if ($a_return) {
                $ilCtrl->redirect($this, "returnToParent");
            } else {
                $ilCtrl->redirect($this, "editAssignmentText");
            }
        }
        
        $form->setValuesByPost();
        $this->editAssignmentTextObject($form);
    }
    
    public function showAssignmentTextObject() : void
    {
        if (!$this->submission->isTutor()) {
            $this->handleTabs();
        }
        
        $a_form = $this->initAssignmentTextForm(true);
        
        $files = $this->submission->getFiles();
        if ($files !== []) {
            $files = array_shift($files);
            if (trim($files["atext"]) !== '' && trim($files["atext"]) !== '0') {
                if ($files["late"] &&
                    !$this->submission->hasPeerReviewAccess()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_late_submission"));
                }
                
                $text = $a_form->getItemByPostVar("atxt");
                // mob id to mob src
                $text->setValue(nl2br(ilRTE::_replaceMediaObjectImageSrc($files["atext"], 1)));
            }
        }
    
        $this->tpl->setContent($a_form->getHTML());
    }
}
