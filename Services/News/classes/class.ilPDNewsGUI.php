<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* News on PD
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPDNewsGUI:
*
*/

class ilPDNewsGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilObjUser
     */
    protected $user;


    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->user = $DIC->user();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilHelp = $DIC["ilHelp"];

        $ilHelp->setScreenIdComponent("news");
        
        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $lng->loadLanguageModule("news");
        
        $this->ctrl->saveParameter($this, "news_ref_id");
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
                
            default:
                $cmd = $this->ctrl->getCmd("view");
                $this->displayHeader();
                $this->$cmd();
                break;
        }
        $this->tpl->show(true);
        return true;
    }

    /**
    * display header and locator
    */
    public function displayHeader()
    {
        $this->tpl->setTitle($this->lng->txt("news"));
        
        // display infopanel if something happened
        ilUtil::infoPanel();
    }

    /*
    * display notes
    */
    public function view()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $ref_ids = array();
        $obj_ids = array();
        $pd_items = $ilUser->getDesktopItems();
        foreach ($pd_items as $item) {
            $ref_ids[] = $item["ref_id"];
            $obj_ids[] = $item["obj_id"];
        }
        
        $sel_ref_id = ($_GET["news_ref_id"] > 0)
            ? $_GET["news_ref_id"]
            : $ilUser->getPref("news_sel_ref_id");
        
        include_once("./Services/News/classes/class.ilNewsItem.php");
        $per = ($_SESSION["news_pd_news_per"] != "")
            ? $_SESSION["news_pd_news_per"]
            : ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
        $news_obj_ids = ilNewsItem::filterObjIdsPerNews($obj_ids, $per);
        
        // related objects (contexts) of news
        $contexts[0] = $lng->txt("news_all_items");
        
        $conts = array();
        $sel_has_news = false;
        foreach ($ref_ids as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $title = ilObject::_lookupTitle($obj_id);
            
            $conts[$ref_id] = $title;
            if ($sel_ref_id == $ref_id) {
                $sel_has_news = true;
            }
        }
        
        $cnt = array();
        $nitem = new ilNewsItem();
        $news_items = $nitem->_getNewsItemsOfUser(
            $ilUser->getId(),
            false,
            true,
            $per,
            $cnt
        );

        // reset selected news ref id, if no news are given for id
        if (!$sel_has_news) {
            $sel_ref_id = "";
        }
        asort($conts);
        foreach ($conts as $ref_id => $title) {
            $contexts[$ref_id] = $title . " (" . (int) $cnt[$ref_id] . ")";
        }
        
        
        if ($sel_ref_id > 0) {
            $obj_id = ilObject::_lookupObjId($sel_ref_id);
            $obj_type = ilObject::_lookupType($obj_id);
            $nitem->setContextObjId($obj_id);
            $nitem->setContextObjType($obj_type);
            $news_items = $nitem->getNewsForRefId(
                $sel_ref_id,
                false,
                false,
                $per,
                true
            );
        }
                
        include_once("./Services/News/classes/class.ilPDNewsTableGUI.php");
        $pd_news_table = new ilPDNewsTableGUI($this, "view", $contexts, $sel_ref_id);
        $pd_news_table->setData($news_items);
        $pd_news_table->setNoEntriesText($lng->txt("news_no_news_items"));
        
        $tpl->setContent($pd_news_table->getHTML());
    }
    
    /**
    * change related object
    */
    public function applyFilter()
    {
        $ilUser = $this->user;
        
        $this->ctrl->setParameter($this, "news_ref_id", $_POST["news_ref_id"]);
        $ilUser->writePref("news_sel_ref_id", $_POST["news_ref_id"]);
        if ($_POST["news_per"] > 0) {
            $_SESSION["news_pd_news_per"] = $_POST["news_per"];
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
    * reset filter
    */
    public function resetFilter()
    {
        $ilUser = $this->user;
        $this->ctrl->setParameter($this, "news_ref_id", 0);
        $ilUser->writePref("news_sel_ref_id", 0);
        $_SESSION["news_pd_news_per"] = "";
        $this->ctrl->redirect($this, "view");
    }
}
