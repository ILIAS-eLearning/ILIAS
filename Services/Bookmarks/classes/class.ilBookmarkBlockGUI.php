<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for Bookmarks block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesBookmarks
*
* @ilCtrl_IsCalledBy ilBookmarkBlockGUI: ilColumnGUI
*/
class ilBookmarkBlockGUI extends ilBlockGUI
{
    public static $block_type = "pdbookm";
    
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
        
        $this->setImage(ilUtil::getImagePath("icon_bm.svg"));
        $this->setTitle($lng->txt("my_bms"));
        $this->setEnableNumInfo(false);
        $this->setLimit(99999);
        $this->setAvailableDetailLevels(3);
        
        $this->id = (empty($_GET["bmf_id"]))
            ? $bmf_id = 1
            : $_GET["bmf_id"];
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
        // workaround to show details row
        $this->setData(array("dummy"));
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
        
        include_once("./Services/Bookmarks/classes/class.ilBookmarkFolder.php");
        $bm_items = ilBookmarkFolder::_getNumberOfObjects();
        $this->num_bookmarks = $bm_items["bookmarks"];
        $this->num_folders = $bm_items["folders"];

        if ($this->getCurrentDetailLevel() > 1 &&
            ($this->num_bookmarks > 0 || $this->num_folders > 0)) {
            if ($ilUser->getPref("il_pd_bkm_mode") == 'tree') {
                $this->setDataSection($this->getPDBookmarkListHTMLTree());
            } else {
                $this->setRowTemplate("tpl.bookmark_pd_list.html", "Services/Bookmarks");
                $this->getListRowData();
                $this->setColSpan(2);
                parent::fillDataSection();
            }
        } else {
            if ($this->num_bookmarks == 0 && $this->num_folders == 0) {
                $this->setEnableDetailRow(false);
            }
            $this->setDataSection($this->getOverview());
        }
    }
    
    /**
    * get tree bookmark list for personal desktop
    */
    public function getPDBookmarkListHTMLTree()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        include_once("./Services/Bookmarks/classes/class.ilBookmarkBlockExplorerGUI.php");
        $exp = new ilBookmarkBlockExplorerGUI($this, "getPDBookmarkListHTMLTree");
        if (!$exp->handleCommand()) {
            return "<div id='tree_div'>" . $exp->getHTML() . "</div>";
        }
    }

    /**
    * block footer
    */
    public function fillFooter()
    {
        $this->setFooterLinks();
        $this->fillFooterLinks();
        $this->tpl->setVariable("FCOLSPAN", $this->getColSpan());
        if ($this->tpl->blockExists("block_footer")) {
            $this->tpl->setCurrentBlock("block_footer");
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * Set footer links.
    */
    public function setFooterLinks()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if ($this->num_bookmarks == 0 && $this->num_folders == 0) {
            return;
        }
        
        // flat
        $this->addFooterLink(
            $lng->txt("list"),
            $ilCtrl->getLinkTarget($this, "setPdFlatMode"),
            $ilCtrl->getLinkTarget(
                $this,
                "setPdFlatMode",
                "",
                true
            ),
            "block_" . $this->getBlockType() . "_" . $this->block_id,
            false,
            false,
            ($ilUser->getPref("il_pd_bkm_mode") != 'tree')
        );

        // as tree
        $this->addFooterLink(
            $lng->txt("tree"),
            $ilCtrl->getLinkTarget(
                $this,
                "setPdTreeMode"
            ),
            "",
            "block_" . $this->getBlockType() . "_" . $this->block_id,
            false,
            false,
            ($ilUser->getPref("il_pd_bkm_mode") == 'tree')
        );
    }

    /**
    * Get list data (for flat list).
    */
    public function getListRowData()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once("./Services/Bookmarks/classes/class.ilBookmarkFolder.php");

        $data = array();
        
        $sess_cur_bm_folder = "";
        if (isset($_SESSION["ilCurBMFolder"])) {
            $sess_cur_bm_folder = $_SESSION["ilCurBMFolder"];
        }
        
        $bm_items = ilBookmarkFolder::getObjects($sess_cur_bm_folder);

        if (!ilBookmarkFolder::isRootFolder($sess_cur_bm_folder)
            && !empty($_SESSION["ilCurBMFolder"])) {
            $ilCtrl->setParameter(
                $this,
                "curBMFolder",
                ilBookmarkFolder::_getParentId($sess_cur_bm_folder)
            );

            $data[] = array(
                "img" => ilUtil::getImagePath("icon_bmf.svg"),
                "alt" => $lng->txt("bmf"),
                "title" => "..",
                "link" => $ilCtrl->getLinkTarget($this, "setCurrentBookmarkFolder"));

            $this->setTitle($this->getTitle() . ": " . ilBookmarkFolder::_lookupTitle($sess_cur_bm_folder));
        }

        foreach ($bm_items as $bm_item) {
            switch ($bm_item["type"]) {
                case "bmf":
                    $ilCtrl->setParameter($this, "curBMFolder", $bm_item["obj_id"]);
                    $data[] = array(
                        "img" => ilUtil::getImagePath("icon_bmf.svg"),
                        "alt" => $lng->txt("bmf"),
                        "title" => ilUtil::prepareFormOutput($bm_item["title"]),
                        "desc" => ilUtil::prepareFormOutput($bm_item["desc"]),
                        "link" => $ilCtrl->getLinkTarget(
                            $this,
                            "setCurrentBookmarkFolder"
                        ),
                        "target" => "");
                    break;

                case "bm":
                    $data[] = array(
                        "img" => ilUtil::getImagePath("spacer.png"),
                        "alt" => $lng->txt("bm"),
                        "title" => ilUtil::prepareFormOutput($bm_item["title"]),
                        "desc" => ilUtil::prepareFormOutput($bm_item["desc"]),
                        "link" => ilUtil::prepareFormOutput($bm_item["target"]),
                        "rel" => "noopener",
                        "target" => "_blank");
                    break;
            }
        }
        
        $this->setData($data);
    }
    
    /**
    * get flat bookmark list for personal desktop
    */
    public function fillRow($a_set)
    {
        $ilUser = $this->user;
        
        $this->tpl->setVariable("IMG_BM", $a_set["img"]);
        $this->tpl->setVariable("IMG_ALT", $a_set["alt"]);
        $this->tpl->setVariable("BM_TITLE", $a_set["title"]);
        $this->tpl->setVariable("BM_LINK", $a_set["link"]);
        $this->tpl->setVariable("BM_TARGET", ilUtil::prepareFormOutput($a_set["target"]));
        if (isset($a_set['rel'])) {
            $this->tpl->setVariable("BM_REL", $a_set['rel']);
        }

        if ($this->getCurrentDetailLevel() > 2) {
            $this->tpl->setVariable("BM_DESCRIPTION", ilUtil::prepareFormOutput($a_set["desc"]));
        } else {
            $this->tpl->setVariable("BM_TOOLTIP", ilUtil::prepareFormOutput($a_set["desc"]));
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
                
        return '<div class="small">' . $this->num_bookmarks . " " . $lng->txt("bm_num_bookmarks") . ", " .
            $this->num_folders . " " . $lng->txt("bm_num_bookmark_folders") . "</div>";
    }

    /**
    * set current desktop view mode to flat
    */
    public function setPdFlatMode()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $ilUser->writePref("il_pd_bkm_mode", 'flat');
        if ($ilCtrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
        }
    }

    /**
    * set current desktop view mode to tree
    */
    public function setPdTreeMode()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $ilUser->writePref("il_pd_bkm_mode", 'tree');
        if ($ilCtrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        } else {
            $ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
        }
    }

    /**
    * set current bookmarkfolder on personal desktop
    */
    public function setCurrentBookmarkFolder()
    {
        $ilCtrl = $this->ctrl;
        
        $_SESSION["ilCurBMFolder"] = $_GET["curBMFolder"];
        $ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
    }
}
