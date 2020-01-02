<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for wiki sideblock
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiSideBlockGUI extends ilBlockGUI
{
    public static $block_type = "wikiside";
    public static $st_data;
    
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
        $lng = $DIC->language();
        
        parent::__construct();
        
        $lng->loadLanguageModule("wiki");
        $this->setEnableNumInfo(false);

        $this->setTitle($lng->txt("wiki_quick_navigation"));
        $this->allow_moving = false;
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
        return IL_SCREEN_SIDE;
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
    * Set Page Object
    *
    * @param	int	$a_pageob	Page Object
    */
    public function setPageObject($a_pageob)
    {
        $this->pageob = $a_pageob;
    }

    /**
    * Get Page Object
    *
    * @return	int	Page Object
    */
    public function getPageObject()
    {
        return $this->pageob;
    }

    /**
    * Get bloch HTML code.
    */
    public function getHTML()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        
        return parent::getHTML();
    }

    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        $tpl = new ilTemplate("tpl.wiki_side_block_content.html", true, true, "Modules/Wiki");

        $wp = $this->getPageObject();
        
        // start page
        $actions[] = array(
            "txt" => $lng->txt("wiki_start_page"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoStartPage")
            );

        // all pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_all_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "allPages")
            );

        // new pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_new_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "newPages")
            );

        // popular pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_popular_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "popularPages")
            );

        // orphaned pages
        $actions[] = array(
            "txt" => $lng->txt("wiki_orphaned_pages"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "orphanedPages")
            );

        // recent changes
        $actions[] = array(
            "txt" => $lng->txt("wiki_recent_changes"),
            "href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "recentChanges")
            );

        foreach ($actions as $a) {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("HREF", $a["href"]);
            $tpl->setVariable("TXT", $a["txt"]);
            $tpl->parseCurrentBlock();

            $tpl->touchBlock("item");
        }

        $this->setDataSection($tpl->get());
    }
}
