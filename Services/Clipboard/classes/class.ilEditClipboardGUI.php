<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");

/**
* Class ilEditClipboardGUI
*
* Clipboard for editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilEditClipboardGUI: ilObjMediaObjectGUI
*
* @ingroup ServicesClipboard
*/
class ilEditClipboardGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->tree = $DIC->repositoryTree();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->error = $DIC["ilErr"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $this->multiple = false;
        $this->page_back_title = $lng->txt("cont_back");
        if ($_GET["returnCommand"] != "") {
            $this->mode = "getObject";
        } else {
            $this->mode = "";
        }

        $ilCtrl->setParameter(
            $this,
            "returnCommand",
            rawurlencode($_GET["returnCommand"])
        );

        $ilCtrl->saveParameter($this, array("clip_item_id"));
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            case "ilobjmediaobjectgui":
                $ilCtrl->setReturn($this, "view");
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "view")
                );
                $mob_gui = new ilObjMediaObjectGUI("", $_GET["clip_item_id"], false, false);
                $mob_gui->setTabs();
                $ret = $ilCtrl->forwardCommand($mob_gui);
                switch ($cmd) {
                    case "save":
                        $ilUser->addObjectToClipboard($ret->getId(), "mob", $ret->getTitle());
                        $ilCtrl->redirect($this, "view");
                        break;
                }
                break;

            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }
    
    /**
    * set, if multiple selections are enabled
    */
    public function setMultipleSelections($a_multiple = true)
    {
        $this->multiple = $a_multiple;
    }

    /**
    * check wether multiple selections are enabled
    */
    public function getMultipleSelections()
    {
        return $this->multiple;
    }

    /**
    * Set Insert Button Title.
    *
    * @param	string	$a_insertbuttontitle	Insert Button Title
    */
    public function setInsertButtonTitle($a_insertbuttontitle)
    {
        $this->insertbuttontitle = $a_insertbuttontitle;
    }

    /**
    * Get Insert Button Title.
    *
    * @return	string	Insert Button Title
    */
    public function getInsertButtonTitle()
    {
        $lng = $this->lng;
        
        if ($this->insertbuttontitle == "") {
            return $lng->txt("insert");
        }
        
        return $this->insertbuttontitle;
    }

    /*
    * display clipboard content
    */
    public function view()
    {
        $tree = $this->tree;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
        $but = ilLinkButton::getInstance();
        $but->setUrl($ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "create"));
        $but->setCaption("cont_create_mob");
        $ilToolbar->addButtonInstance($but);

        include_once("./Services/Clipboard/classes/class.ilClipboardTableGUI.php");
        $table_gui = new ilClipboardTableGUI($this, "view");
        $tpl->setContent($table_gui->getHTML());
    }


    /**
    * get Object
    */
    public function getObject()
    {
        $this->mode = "getObject";
        $this->view();
    }


    /**
    * remove item from clipboard
    */
    public function remove()
    {
        $ilErr = $this->error;
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        // check number of objects
        if (!isset($_POST["id"])) {
            $ilErr->raiseError($lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        foreach ($_POST["id"] as $obj_id) {
            $id = explode(":", $obj_id);
            if ($id[0] == "mob") {
                $ilUser->removeObjectFromClipboard($id[1], "mob");
                include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                $mob = new ilObjMediaObject($id[1]);
                $mob->delete();			// this method don't delete, if mob is used elsewhere
            }
            if ($id[0] == "incl") {
                $ilUser->removeObjectFromClipboard($id[1], "incl");
            }
        }
        $ilCtrl->redirect($this, "view");
    }

    /**
    * insert
    */
    public function insert()
    {
        $lng = $this->lng;
        $ilErr = $this->error;
        
        // check number of objects
        if (!isset($_POST["id"])) {
            $ilErr->raiseError($lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }
        
        if (!$this->getMultipleSelections()) {
            if (count($_POST["id"]) > 1) {
                $ilErr->raiseError($lng->txt("cont_select_max_one_item"), $ilErr->MESSAGE);
            }
        }

        $_SESSION["ilEditClipboard_mob_id"] = $_POST["id"];
        ilUtil::redirect($_GET["returnCommand"]);
    }
    
    public static function _getSelectedIDs()
    {
        return $_SESSION["ilEditClipboard_mob_id"];
    }

    /**
    * output tabs
    */
    public function setTabs()
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $tpl->setTitle($lng->txt("clipboard"));
        $this->getTabs($ilTabs);
    }
    
    /**
    * Set title for back link
    */
    public function setPageBackTitle($a_title)
    {
        $this->page_back_title = $a_title;
    }

    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function getTabs(&$tabs_gui)
    {
        $ilCtrl = $this->ctrl;
        
        // back to upper context
        $tabs_gui->setBackTarget(
            $this->page_back_title,
            $ilCtrl->getParentReturn($this)
        );
    }
}
