<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Private Notes on PD
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPDNotesGUI: ilNoteGUI
 */
class ilPDNotesGUI
{
    public const PUBLIC_COMMENTS = "publiccomments";
    public const PRIVATE_NOTES = "privatenotes";
    /**
     * @var mixed
     */
    protected int $current_rel_obj;
    protected \ILIAS\Notes\StandardGUIRequest $request;

    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilSetting $settings;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        $ilHelp = $DIC["ilHelp"];

        $this->request = $DIC->notes()
            ->internal()
            ->gui()
            ->standardRequest();

        $ilHelp->setScreenIdComponent("note");

        $lng->loadLanguageModule("notes");
        
        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        // link from ilPDNotesBlockGUI
        $rel_obj = $this->request->getRelatedObjId();
        if ($rel_obj > 0) {
            $mode = ($this->request->getNoteType() == ilNote::PRIVATE)
                ? self::PRIVATE_NOTES
                : self::PUBLIC_COMMENTS;
            $ilUser->writePref("pd_notes_mode", $mode);
            $ilUser->writePref("pd_notes_rel_obj" . $mode, (string) $rel_obj);
        }
        // edit link
        elseif ($this->request->getNoteId() > 0) {
            $note = new ilNote($this->request->getNoteId());
            $mode = ($note->getType() == ilNote::PRIVATE) ? self::PRIVATE_NOTES : self::PUBLIC_COMMENTS;
            $obj = $note->getObject();
            $ilUser->writePref("pd_notes_mode", $mode);
            $ilUser->writePref("pd_notes_rel_obj" . $mode, $obj["rep_obj_id"]);
        }
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "ilnotegui":
                $this->displayHeader();
                $this->view();		// forwardCommand is invoked in view() method
                break;
                
            default:
                $cmd = $this->ctrl->getCmd("view");
                $this->displayHeader();
                $this->$cmd();
                break;
        }
        $this->tpl->printToStdout(true);
    }

    public function displayHeader() : void
    {
        $ilSetting = $this->settings;

        $t = $this->lng->txt("notes");
        if (!$ilSetting->get("disable_notes") && !$ilSetting->get("disable_comments")) {
            $t = $this->lng->txt("notes_and_comments");
        }
        if ($ilSetting->get("disable_notes")) {
            $t = $this->lng->txt("notes_comments");
        }

        if ($this->getMode() == self::PRIVATE_NOTES) {
            $t = $this->lng->txt("private_notes");
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_nots.svg"));
        } else {
            $t = $this->lng->txt("notes_public_comments");
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_coms.svg"));
        }

        $this->tpl->setTitle($t);
    }

    public function view() : void
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        $ilToolbar = $this->toolbar;

        $rel_objs = ilNote::_getRelatedObjectsOfUser($this->getMode());
        //var_dump($rel_objs);
        // prepend personal dektop, if first object
        //		if ($rel_objs[0]["rep_obj_id"] > 0 && $this->getMode() == ilPDNotesGUI::PRIVATE_NOTES)
        if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES) {
            $rel_objs = array_merge(
                [0 => [
                    "rep_obj_id" => 0,
                    "ref_ids" => []
                ]],
                $rel_objs
            );
        }

        // #9410
        if (!$rel_objs && $this->getMode() == ilPDNotesGUI::PUBLIC_COMMENTS) {
            $lng->loadLanguageModule("notes");
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_no_search_result"));
            return;
        }
        $first = true;
        $current_ref_ids = [];
        foreach ($rel_objs as $r) {
            if ($first) {	// take first one as default
                $this->current_rel_obj = (int) $r["rep_obj_id"];
                $current_ref_ids = $r["ref_ids"];
            }
            if ($r["rep_obj_id"] == $ilUser->getPref("pd_notes_rel_obj" . $this->getMode())) {
                $this->current_rel_obj = (int) $r["rep_obj_id"];
                $current_ref_ids = $r["ref_ids"];
            }
            $first = false;
        }
        if ($this->current_rel_obj > 0) {
            $notes_gui = new ilNoteGUI(
                $this->current_rel_obj,
                0,
                ilObject::_lookupType($this->current_rel_obj),
                true,
                0,
                false
            );
        } else {
            $notes_gui = new ilNoteGUI(0, $ilUser->getId(), "pd", false, 0, false);
        }
        
        if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES) {
            $notes_gui->enablePrivateNotes(true);
            $notes_gui->enablePublicNotes(false);
        } else {
            $notes_gui->enablePrivateNotes(false);
            $notes_gui->enablePublicNotes(true);
            // #13707
            if ($this->current_rel_obj > 0 &&
                count($current_ref_ids) > 0 &&
                $ilSetting->get("comments_del_tutor", 1)) {
                foreach ($current_ref_ids as $ref_id) {
                    if ($ilAccess->checkAccess("write", "", $ref_id)) {
                        $notes_gui->enablePublicNotesDeletion(true);
                        break;
                    }
                }
            }
        }
        $notes_gui->enableHiding(false);
        $notes_gui->enableTargets(true);
        $notes_gui->enableMultiSelection(true);
        $notes_gui->enableAnchorJump(false);

        $next_class = $this->ctrl->getNextClass($this);

        if ($next_class == "ilnotegui") {
            $html = $this->ctrl->forwardCommand($notes_gui);
        } else {
            if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES) {
                $html = $notes_gui->getOnlyNotesHTML();
            } else {
                $html = $notes_gui->getOnlyCommentsHTML();
            }
        }
        
        if (count($rel_objs) > 1 ||
            ($rel_objs[0]["rep_obj_id"] > 0)) {
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

            $options = [];
            foreach ($rel_objs as $obj) {
                if ($obj["rep_obj_id"] > 0) {
                    $type = ilObject::_lookupType($obj["rep_obj_id"]);
                    $type_str = (in_array($type, array("lm", "htlm", "sahs")))
                        ? $lng->txt("obj_lm")
                        : $lng->txt("obj_" . $type);
                    $caption = $type_str . ": " . ilObject::_lookupTitle($obj["rep_obj_id"]);
                } else {
                    $caption = $lng->txt("note_without_object");
                }
                
                $options[$obj["rep_obj_id"]] = $caption;
            }
            
            $rel = new ilSelectInputGUI($lng->txt("related_to"), "rel_obj");
            $rel->setOptions($options);
            $rel->setValue($this->current_rel_obj);
            $ilToolbar->addStickyItem($rel);

            $btn = ilSubmitButton::getInstance();
            $btn->setCaption('change');
            $btn->setCommand('changeRelatedObject');
            $ilToolbar->addStickyItem($btn);
        }
        
        $this->tpl->setContent($html);
    }
    
    public function changeRelatedObject() : void
    {
        $ilUser = $this->user;

        $ilUser->writePref(
            "pd_notes_rel_obj" . $this->getMode(),
            (string) $this->request->getRelatedObjId()
        );
        $this->ctrl->redirect($this);
    }

    public function showPrivateNotes() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        $ilUser->writePref("pd_notes_mode", ilPDNotesGUI::PRIVATE_NOTES);
        $ilCtrl->redirect($this, "");
    }
    
    public function showPublicComments() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        
        if ($ilSetting->get("disable_comments")) {
            $ilCtrl->redirect($this, "showPrivateNotes");
        }
        
        $ilUser->writePref("pd_notes_mode", ilPDNotesGUI::PUBLIC_COMMENTS);
        $ilCtrl->redirect($this, "");
    }

    public function getMode() : string
    {
        $ilUser = $this->user;
        $ilSetting = $this->settings;
        
        if ($ilUser->getPref("pd_notes_mode") == ilPDNotesGUI::PUBLIC_COMMENTS &&
            !$ilSetting->get("disable_comments")) {
            return ilPDNotesGUI::PUBLIC_COMMENTS;
        } else {
            return ilPDNotesGUI::PRIVATE_NOTES;
        }
    }
}
