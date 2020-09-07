<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Block/classes/class.ilBlockGUI.php");
include_once("./Services/Block/classes/class.ilExternalFeedBlockGUIGen.php");
include_once("./Services/Feeds/classes/class.ilExternalFeed.php");

/**
* BlockGUI class for external feed block. This is the one that is used
* within the repository. On the personal desktop ilPDExternalFeedBlockGUI
* is used.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilExternalFeedBlockGUI: ilColumnGUI
* @ingroup ServicesFeeds
*/
class ilExternalFeedBlockGUI extends ilExternalFeedBlockGUIGen
{
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    public static $block_type = "feed";
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->obj_definition = $DIC["objDefinition"];
        $lng = $DIC->language();
        
        parent::__construct();
        
        $lng->loadLanguageModule("feed");
        $this->setLimit(5);
        $this->setRowTemplate("tpl.block_external_feed_row.html", "Services/Feeds");
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
        return true;
    }
    
    /**
    * Get Screen Mode for current command.
    */
    public static function getScreenMode()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        
        switch ($ilCtrl->getCmd()) {
            case "create":
            case "edit":
            case "saveFeedBlock":
            case "updateFeedBlock":
            case "editFeedBlock":
            case "showFeedItem":
            case "confirmDeleteFeedBlock":
                return IL_SCREEN_CENTER;
                break;
                
            default:
                return IL_SCREEN_SIDE;
                break;
        }
    }

    /**
    * Do most of the initialisation.
    */
    public function setBlock($a_block)
    {
        $ilCtrl = $this->ctrl;

        // init block
        $this->feed_block = $a_block;
        $this->setTitle($this->feed_block->getTitle());
        $this->setBlockId($this->feed_block->getId());
        
        // get feed object
        include_once("./Services/Feeds/classes/class.ilExternalFeed.php");
        $this->feed = new ilExternalFeed();
        $this->feed->setUrl($this->feed_block->getFeedUrl());
        
        // init details
        $this->setAvailableDetailLevels(2);
        
        $ilCtrl->setParameter($this, "block_id", $this->feed_block->getId());
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");
        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        if ($this->getDynamic()) {
            $this->setDataSection($this->getDynamicReload());
        } elseif ($this->getCurrentDetailLevel() > 1 && count($this->getData()) > 0) {
            parent::fillDataSection();
        } else {
            $this->setDataSection($this->getOverview());
        }
    }

    /**
    * Get block HTML code.
    */
    public function getHTML()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $ilSetting = $this->settings;
        
        if ($this->getCurrentDetailLevel() == 0) {
            return "";
        }

        $feed_set = new ilSetting("feed");
        
        if ($feed_set->get("disable_rep_feeds")) {
            return "";
        }
        
        // if no dynamic reload
        if (!$this->getDynamic()) {
            $this->feed->fetch();
            $this->setData($this->feed->getItems());
        }

        //$this->setTitle($this->feed->getChannelTitle());
        $this->setData($this->feed->getItems());

        if ($ilAccess->checkAccess("write", "", $this->getRefId())) {
            $ilCtrl->setParameterByClass(
                "ilobjexternalfeedgui",
                "ref_id",
                $this->getRefId()
            );
            $ilCtrl->setParameter($this, "external_feed_block_id", $this->getBlockId());
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass(
                    array("ilrepositorygui", "ilobjexternalfeedgui",
                    "ilexternalfeedblockgui"),
                    "editFeedBlock"
                ),
                $lng->txt("settings")
            );
            $ilCtrl->clearParametersByClass("ilobjexternalfeedgui");
        }

        // JS enabler
        $add = "";
        if ($_SESSION["il_feed_js"] == "n" ||
            ($ilUser->getPref("il_feed_js") == "n" && $_SESSION["il_feed_js"] != "y")) {
            $add = $this->getJSEnabler();
        }

        return parent::getHTML() . $add;
    }

    public function getDynamic()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        if ($ilCtrl->getCmdClass() != "ilcolumngui" && $ilCtrl->getCmd() != "enableJS") {
            if ($_SESSION["il_feed_js"] != "n" &&
                ($ilUser->getPref("il_feed_js") != "n" || $_SESSION["il_feed_js"] == "y")) {
                // do not get feed dynamically, if cache hit is given.
                if (!$this->feed->checkCacheHit()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function getDynamicReload()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $ilCtrl->setParameterByClass(
            "ilcolumngui",
            "block_id",
            "block_feed_" . $this->getBlockId()
        );

        $rel_tpl = new ilTemplate("tpl.dynamic_reload.html", true, true, "Services/Feeds");
        $rel_tpl->setVariable("TXT_LOADING", $lng->txt("feed_loading_feed"));
        $rel_tpl->setVariable("BLOCK_ID", "block_feed_" . $this->getBlockId());
        $rel_tpl->setVariable(
            "TARGET",
            $ilCtrl->getLinkTargetByClass("ilcolumngui", "updateBlock", "", true)
        );
            
        // no JS
        $rel_tpl->setVariable("TXT_FEED_CLICK_HERE", $lng->txt("feed_no_js_click_here"));
        $rel_tpl->setVariable(
            "TARGET_NO_JS",
            $ilCtrl->getLinkTargetByClass("ilexternalfeedblockgui", "disableJS")
        );

        return $rel_tpl->get();
    }
    
    public function getJSEnabler()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $ilCtrl->setParameterByClass(
            "ilcolumngui",
            "block_id",
            "block_feed_" . $this->getBlockId()
        );

        $rel_tpl = new ilTemplate("tpl.js_enabler.html", true, true, "Services/Feeds");
        $rel_tpl->setVariable("BLOCK_ID", "block_feed_" . $this->getBlockId());
        $rel_tpl->setVariable(
            "TARGET",
            $ilCtrl->getLinkTargetByClass("ilexternalfeedblockgui", "enableJS", true, "", false)
        );
            
        return $rel_tpl->get();
    }
    
    
    public function disableJS()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $_SESSION["il_feed_js"] = "n";
        $ilUser->writePref("il_feed_js", "n");
        $ilCtrl->returnToParent($this);
    }
    
    public function enableJS()
    {
        $ilUser = $this->user;
        
        $_SESSION["il_feed_js"] = "y";
        $ilUser->writePref("il_feed_js", "y");
        echo $this->getHTML();
        exit;
    }

    /**
    * Fill feed item row
    */
    public function fillRow($item)
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        if ($this->isRepositoryObject() && !$ilAccess->checkAccess("read", "", $this->getRefId())) {
            $this->tpl->setVariable("TXT_TITLE", $item->getTitle());
        } else {
            $ilCtrl->setParameter($this, "feed_item_id", $item->getId());
            $this->tpl->setCurrentBlock("feed_link");
            $this->tpl->setVariable("VAL_TITLE", $item->getTitle());
            $this->tpl->setVariable(
                "HREF_SHOW",
                $ilCtrl->getLinkTarget($this, "showFeedItem")
            );
            $ilCtrl->setParameter($this, "feed_item_id", "");
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * Get overview.
    */
    public function getOverview()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->setEnableNumInfo(false);
        return '<div class="small">' . ((int) count($this->getData())) . " " . $lng->txt("feed_feed_items") . "</div>";
    }

    /**
    * Show Feed Item
    */
    public function showFeedItem()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once("./Services/News/classes/class.ilNewsItem.php");

        $this->feed->fetch();
        foreach ($this->feed->getItems() as $item) {
            if ($item->getId() == $_GET["feed_item_id"]) {
                $c_item = $item;
                break;
            }
        }
        
        $tpl = new ilTemplate("tpl.show_feed_item.html", true, true, "Services/Feeds");
        
        if (is_object($c_item)) {
            if (trim($c_item->getSummary()) != "") {		// summary
                $tpl->setCurrentBlock("content");
                $tpl->setVariable("VAL_CONTENT", $c_item->getSummary());
                $tpl->parseCurrentBlock();
            }
            if (trim($c_item->getDate()) != "" || trim($c_item->getAuthor()) != "") {		// date
                $tpl->setCurrentBlock("date_author");
                if (trim($c_item->getAuthor()) != "") {
                    $tpl->setVariable("VAL_AUTHOR", $c_item->getAuthor() . " - ");
                }
                $tpl->setVariable("VAL_DATE", $c_item->getDate());
                $tpl->parseCurrentBlock();
            }

            if (trim($c_item->getLink()) != "") {		// link
                $tpl->setCurrentBlock("plink");
                $tpl->setVariable("HREF_LINK", $c_item->getLink());
                $tpl->setVariable("TXT_LINK", $lng->txt("feed_open_source_page"));
                $tpl->parseCurrentBlock();
            }
            $tpl->setVariable("VAL_TITLE", $c_item->getTitle());			// title
        }
        
        include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
        $content_block = new ilPDContentBlockGUI();
        $content_block->setContent($tpl->get());
        $content_block->setTitle($this->getTitle());
        $content_block->setImage(ilUtil::getImagePath("icon_feed.svg"));
        $content_block->addHeaderCommand(
            $ilCtrl->getParentReturn($this),
            $lng->txt("close"),
            true
        );

        return $content_block->getHTML();
    }
    
    /**
    * Create Form for Block.
    */
    public function create()
    {
        $html1 = $this->createFeedBlock();

        $html2 = "";
        if (DEVMODE == 1) {
            $this->initImportForm("feed");
            $html2 = "<br/>" . $this->form->getHTML();
        }

        return $html1 . $html2;
    }

    /**
     * Init object import form
     *
     * @param        string        new type
     */
    public function initImportForm($a_new_type = "")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule("feed");

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setTarget("_top");

        // Import file
        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($lng->txt("import_file"), "importfile");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $this->form->addItem($fi);

        $this->form->addCommandButton("importFile", $lng->txt("import"));
        $this->form->addCommandButton("cancelSaveFeedBlock", $lng->txt("cancel"));
        $this->form->setTitle($lng->txt($a_new_type . "_import"));

        $ilCtrl->setParameter($this, "new_type", $a_new_type);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Import
     *
     * @access	public
     */
    public function importFile()
    {
        $rbacsystem = $this->rbacsystem;
        $tpl = $this->main_tpl;
        $lng = $this->lng;

        $new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type)) {
            $this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
        }
        $this->ctrl->setParameter($this, "new_type", $new_type);
        $this->initImportForm($new_type);
        if ($this->form->checkInput()) {
            // todo: make some check on manifest file
            include_once("./Services/Export/classes/class.ilImport.php");
            $imp = new ilImport((int) $_GET['ref_id']);
            $new_id = $imp->importObject(
                $newObj,
                $_FILES["importfile"]["tmp_name"],
                $_FILES["importfile"]["name"],
                $new_type
            );

            // put new object id into tree
            if ($new_id > 0) {
                $newObj = ilObjectFactory::getInstanceByObjId($new_id);
                $newObj->createReference();
                $newObj->putInTree($_GET["ref_id"]);
                $newObj->setPermissions($_GET["ref_id"]);
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $this->exitSaveFeedBlock();
            }
            return;
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }


    /**
    * FORM FeedBlock: Init form. (We need to overwrite, because Generator
    * does not know FeedUrl Inputs yet.
    *
    * @param	int	$a_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
    */
    public function initFormFeedBlock($a_mode)
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("block");
        
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        
        $this->form_gui = new ilPropertyFormGUI();
        
        // Property Title
        $text_input = new ilTextInputGUI($lng->txt("block_feed_block_title"), "block_title");
        $text_input->setInfo("");
        $text_input->setRequired(true);
        $text_input->setMaxLength(200);
        $this->form_gui->addItem($text_input);
        
        // Property FeedUrl
        $text_input = new ilFeedUrlInputGUI($lng->txt("block_feed_block_feed_url"), "block_feed_url");
        $text_input->setInfo($lng->txt("block_feed_block_feed_url_info"));
        $text_input->setRequired(true);
        $text_input->setMaxLength(250);
        $this->form_gui->addItem($text_input);
        
        
        // save and cancel commands
        if (in_array($a_mode, array(IL_FORM_CREATE,IL_FORM_RE_CREATE))) {
            $this->form_gui->addCommandButton("saveFeedBlock", $lng->txt("save"));
            $this->form_gui->addCommandButton("cancelSaveFeedBlock", $lng->txt("cancel"));
        } else {
            $this->form_gui->addCommandButton("updateFeedBlock", $lng->txt("save"));
            $this->form_gui->addCommandButton("cancelUpdateFeedBlock", $lng->txt("cancel"));
        }
        
        $this->form_gui->setTitle($lng->txt("block_feed_block_head"));
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this));
        
        $this->prepareFormFeedBlock($this->form_gui);
    }

    /**
    * FORM FeedBlock: Prepare Saving of FeedBlock.
    *
    * @param	object	$a_feed_block	FeedBlock object.
    */
    public function prepareSaveFeedBlock(&$a_feed_block)
    {
        $this->getGuiObject()->save($a_feed_block);
        $a_feed_block->setType($this->getBlockType());
    }
    
    /**
    * FORM FeedBlock: Exit save. (Can be overwritten in derived classes)
    *
    */
    public function exitSaveFeedBlock()
    {
        $this->getGuiObject()->exitSave();
    }

    /**
    * FORM FeedBlock: Exit save. (Can be overwritten in derived classes)
    *
    */
    public function cancelUpdateFeedBlock()
    {
        $this->getGuiObject()->cancelUpdate();
    }

    /**
    * FORM FeedBlock: Exit save. (Can be overwritten in derived classes)
    *
    */
    public function exitUpdateFeedBlock()
    {
        $this->getGuiObject()->update($this->external_feed_block);
    }
}
