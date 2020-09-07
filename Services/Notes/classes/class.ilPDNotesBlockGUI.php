<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for Personal Desktop Notes block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDNotesBlockGUI: ilColumnGUI
*/
class ilPDNotesBlockGUI extends ilBlockGUI
{
    public static $block_type = "pdnotes";
    protected $note_gui = null;
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $lng = $DIC->language();

        parent::__construct();
        
        $this->setLimit(5);
        $this->setTitle($lng->txt("notes"));
        $this->setAvailableDetailLevels(3);
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
    * Get Screen Mode for current command.
    */
    public static function getScreenMode()
    {
        switch ($_GET["cmd"]) {
            case "showNote":
                return IL_SCREEN_CENTER;
                break;
                
            default:
                return IL_SCREEN_SIDE;
                break;
        }
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        return $this->$cmd();
    }

    public function getHTML()
    {
        if ($this->getCurrentDetailLevel() == 0) {
            return "";
        } else {
            return parent::getHTML();
        }
    }
    
    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $ilUser = $this->user;
        
        include_once("Services/Notes/classes/class.ilNote.php");
        $this->notes = ilNote::_getLastNotesOfUser();

        if ($this->getCurrentDetailLevel() > 1 && count($this->notes) > 0) {
            $this->setRowTemplate("tpl.pd_notes_overview.html", "Services/Notes");
            $this->getListRowData();
            //$this->setColSpan(2);
            parent::fillDataSection();
        } else {
            $this->setEnableNumInfo(false);
            if (count($this->notes) == 0) {
                $this->setEnableDetailRow(false);
            }
            $this->setDataSection($this->getOverview());
        }
    }
    

    /**
    * Get list data.
    */
    public function getListRowData()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $data = array();
        
        foreach ($this->notes as $note) {
            switch ($note->getLabel()) {
                case IL_NOTE_UNLABELED:
                    $img = ilUtil::getImagePath("note_unlabeled.svg");
                    $alt = $lng->txt("note");
                    break;
                    
                case IL_NOTE_IMPORTANT:
                    $img = ilUtil::getImagePath("note_unlabeled.svg");
                    $alt = $lng->txt("note") . ", " . $lng->txt("important");
                    break;
                    
                case IL_NOTE_QUESTION:
                    $img = ilUtil::getImagePath("note_unlabeled.svg");
                    $alt = $lng->txt("note") . ", " . $lng->txt("question");
                    break;
                    
                case IL_NOTE_PRO:
                    $img = ilUtil::getImagePath("note_unlabeled.svg");
                    $alt = $lng->txt("note") . ", " . $lng->txt("pro");
                    break;
                    
                case IL_NOTE_CONTRA:
                    $img = ilUtil::getImagePath("note_unlabeled.svg");
                    $alt = $lng->txt("note") . ", " . $lng->txt("contra");
                    break;
            }

            // details
            $target = $note->getObject();
            
            // new notes do not have subject anymore
            $title = $note->getSubject();
            if (!$title) {
                $title = ilUtil::shortenText($note->getText(), 75, true, true);
            }

            $data[] = array(
                "subject" => $title,
                "img" => $img,
                "alt" => $alt,
                "text" => ilUtil::shortenText($note->getText(), 150, true, true),
                "date" => $note->getCreationDate(),
                "id" => $note->getId(),
                "obj_type" => $target["obj_type"],
                "obj_id" => $target["obj_id"],
                "rep_obj_id" => $target["rep_obj_id"]);
        }
        
        $this->setData($data);
    }
    
    /**
    * get flat bookmark list for personal desktop
    */
    public function fillRow($a_set)
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        include_once("Services/Notes/classes/class.ilNoteGUI.php");
        if (!is_object($this->note_gui)) {
            $this->note_gui = new ilNoteGUI(0, 0, "");
            $this->note_gui->enableTargets();
        }

        $this->tpl->setVariable("VAL_SUBJECT", $a_set["subject"]);

        // link subject to show note function
        $ilCtrl->setParameter($this, "rel_obj", $a_set["rep_obj_id"]);
        $ilCtrl->setParameter($this, "note_id", $a_set["id"]);
        $ilCtrl->setParameter($this, "note_type", IL_NOTE_PRIVATE);
        $this->tpl->setVariable(
            "HREF_SHOW_NOTE",
            $ilCtrl->getLinkTarget($this, "showNote")
        );
        $this->tpl->setVariable("IMG_NOTE", $a_set["img"]);
        $this->tpl->setVariable("ALT_NOTE", $a_set["alt"]);
        $ilCtrl->clearParameters($this);
        
        // details
        if ($this->getCurrentDetailLevel() > 2) {
            $this->tpl->setCurrentBlock("details");
            if (substr($a_set["text"], 0, 40) != substr($a_set["text"], 0, 40)) {
                $this->tpl->setVariable("NOTE_TEXT", $a_set["text"]);
            }
            $this->tpl->setVariable(
                "VAL_DATE",
                ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
            );
            $this->tpl->parseCurrentBlock();
                
            // target objects
            $note = new ilNote($a_set["id"]);
            $this->tpl->setVariable(
                "TARGET_OBJECTS",
                $this->note_gui->renderTargets($note)
            );

            // edit button
            $this->tpl->setCurrentBlock("edit_note");
            $this->tpl->setVariable("TXT_EDIT_NOTE", $lng->txt("edit"));
            $ilCtrl->setParameterByClass("ilnotegui", "rel_obj", $a_set["rep_obj_id"]);
            $ilCtrl->setParameterByClass("ilnotegui", "note_id", $a_set["id"]);
            $ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PRIVATE);
            $this->tpl->setVariable(
                "LINK_EDIT_NOTE",
                $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpdnotesgui", "ilnotegui"), "editNoteForm")
                . "#note_edit"
            );
            $this->tpl->parseCurrentBlock();
        }
        $ilCtrl->clearParametersByClass("ilnotegui");
    }

    /**
    * Get overview.
    */
    public function getOverview()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
                
        return '<div class="small">' . ((int) count($this->notes)) . " " . $lng->txt("notes") . "</div>";
    }

    /**
    * show single note
    */
    public function showNote()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once("./Services/Notes/classes/class.ilNoteGUI.php");
        $note_gui = new ilNoteGUI();
        $note_gui->enableTargets();
        include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
        $content_block = new ilPDContentBlockGUI();
        $content_block->setContent($note_gui->getPDNoteHTML($_GET["note_id"]));
        $content_block->setTitle($lng->txt("note"));
        $content_block->setColSpan(2);
        $content_block->addHeaderCommand(
            $ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "show"),
            $lng->txt("selected_items_back")
        );
        
        return $content_block->getHTML();
    }
}
