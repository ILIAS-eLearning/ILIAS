<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Handles user interface for wikis
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilWikiHandlerGUI: ilObjWikiGUI
 */
class ilWikiHandlerGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $ilCtrl = $DIC->ctrl();


        // initialisation stuff
        $this->ctrl = $ilCtrl;

        $DIC->globalScreen()->tool()->context()->claim()->repository();
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilNavigationHistory = $this->nav_history;
        
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjwikigui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
            $title = ilObject::_lookupTitle($obj_id);

            if ($_GET["page"] != "") {
                $page = $_GET["page"];
            } else {
                $page = ilObjWiki::_lookupStartPage($obj_id);
            }

            if (ilWikiPage::exists($obj_id, $page)) {

                $add = "_" . rawurlencode($page);

                $page_id = ilWikiPage::getPageIdForTitle($obj_id, $page);
                $ptitle = ilWikiPage::lookupTitle($page_id);
                
                $title .= ": " . $ptitle;
                
                $append = ($_GET["page"] != "")
                    ? "_" . ilWikiUtil::makeUrlTitle($page)
                    : "";
                $goto = ilLink::_getStaticLink(
                    $_GET["ref_id"],
                    "wiki",
                    true,
                    $append
                );
                //var_dump($goto);
                $ilNavigationHistory->addItem(
                    $_GET["ref_id"],
                    "./goto.php?target=wiki_" . $_GET["ref_id"] . $add,
                    "wiki",
                    $title,
                    $page_id,
                    $goto
                );
            }
        }

        switch ($next_class) {
            case 'ilobjwikigui':
                $mc_gui = new ilObjWikiGUI("", (int) $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($mc_gui);
                break;
        }

        $tpl->printToStdout();
    }
}
