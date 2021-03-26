<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Important pages wiki block
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilWikiImportantPagesBlockGUI extends ilBlockGUI
{
    public static $block_type = "wikiimppages";
    public static $st_data;
    protected $export = false;
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct();
        
        $lng->loadLanguageModule("wiki");
        $this->setEnableNumInfo(false);
        
        $this->setTitle($lng->txt("wiki_navigation"));
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
    * Get bloch HTML code.
    */
    public function getHTML($a_export = false)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->export = $a_export;

        if (!$this->export && ilWikiPerm::check("edit_wiki_navigation", $_GET["ref_id"])) {
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass("ilobjwikigui", "editImportantPages"),
                $lng->txt("edit")
            );
        }
        
        return parent::getHTML();
    }

    /**
     * Fill data section
     */
    public function fillDataSection()
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    protected $new_rendering = true;


    /**
     * @inheritdoc
     */
    protected function getLegacyContent() : string
    {
        $ilCtrl = $this->ctrl;
        $cpar[0] = $cpar[1] = 0;
        
        $list = new ilNestedList();
        $list->setItemClass("ilWikiBlockItem");
        $list->setListClass("ilWikiBlockList");
        $list->setListClass("ilWikiBlockListNoIndent", 1);
        
        $cnt = 1;
        $title = ilObjWiki::_lookupStartPage(ilObject::_lookupObjId($_GET["ref_id"]));
        if (!$this->export) {
            $list->addListNode("<p class='small'><a href='" .
                $ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoStartPage")
                . "'>" . $title . "</a></p>", 1, 0);
        } else {
            $list->addListNode("<p class='small'><a href='" .
                "index.html" .
                "'>" . $title . "</a></p>", 1, 0);
        }
        $cpar[0] = 1;
        
        $ipages = ilObjWiki::_lookupImportantPagesList(ilObject::_lookupObjId($_GET["ref_id"]));
        foreach ($ipages as $p) {
            $cnt++;
            $title = ilWikiPage::lookupTitle($p["page_id"]);
            if (!$this->export) {
                $list->addListNode("<p class='small'><a href='" .
                    ilObjWikiGUI::getGotoLink($_GET["ref_id"], $title)
                    . "'>" . $title . "</a></p>", $cnt, (int) $cpar[$p["indent"] - 1]);
            } else {
                $list->addListNode("<p class='small'><a href='" .
                    "wpg_" . $p["page_id"] . ".html" .
                    "'>" . $title . "</a></p>", $cnt, (int) $cpar[$p["indent"] - 1]);
            }
            $cpar[$p["indent"]] = $cnt;
        }
        
        return $list->getHTML();
    }
}
