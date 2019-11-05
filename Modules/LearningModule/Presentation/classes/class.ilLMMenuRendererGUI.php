<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Menu / Tabs renderer
 *
 * @author killing@leifos.de
 */
class ilLMMenuRendererGUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var int
     */
    protected $current_page;

    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    /**
     * @var bool
     */
    protected $offline;

    /**
     * @var ilCtrl
     */
    protected $ctrl;


    /**
     * @var string
     */
    protected $lang;

    /**
     * @var string
     */
    protected $active_tab;

    /**
     * @var string
     */
    protected $export_format;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLMMenuEditor
     */
    protected $menu_editor;

    /**
     * Constructor
     */
    public function __construct(
        ilTabsGUI $tabs,
        int $current_page,
        string $active_tab,
        string $export_format,
        bool $export_all,
        ilObjLearningModule $lm,
        bool $offline,
        ilLMMenuEditor $menu_editor,
        string $lang,
        ilCtrl $ctrl,
        ilAccessHandler $access,
        ilObjUser $user,
        ilLanguage $lng
    ) {
        $this->active_tab = $active_tab;
        $this->export_format = $export_format;
        $this->export_all = $export_all;
        $this->tabs = $tabs;
        $this->menu_editor = $menu_editor;
        $this->lng = $lng;

        $this->access = $access;
        $this->user = $user;
        $this->ctrl = $ctrl;
        $this->lang = $lang;
        $this->current_page = $current_page;
        $this->lm = $lm;
        $this->offline = $offline;

        $this->requested_obj_id = (int) $_GET["obj_id"];
        $this->requested_ref_id = (int) $_GET["ref_id"];

    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        $addcmd = "addTarget";
        $getcmd = "getHTML";

        //$ilHelp->setScreenIdComponent("lm");

        $active[$this->active_tab] = true;

        if (!$this->lm->isActiveLMMenu())
        {
            return "";
        }

        $tabs_gui = $ilTabs;

        // workaround for preventing tooltips in export
        if ($this->offline)
        {
            $tabs_gui->setSetupMode(true);
        }

        // Determine whether the view of a learning resource should
        // be shown in the frameset of ilias, or in a separate window.
        //$showViewInFrameset = true;


        // content
        if (!$this->offline && $ilAccess->checkAccess("read", "", $this->requested_ref_id))
        {
            $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
            $tabs_gui->$addcmd("content",
                $ilCtrl->getLinkTargetByClass("illmpresentationgui", "layout"),
                "", "", "",  $active["content"]);
            /*
            if ($active["content"])
            {
                $ilHelp->setScreenId("content");
                $ilHelp->setSubScreenId("content");
            }*/
        }
        else if ($this->offline)
        {
            $tabs_gui->setForcePresentationOfSingleTab(true);
        }

        // table of contents
        if($this->lm->isActiveTOC() && $ilAccess->checkAccess("read", "", $this->requested_ref_id))
        {
            if (!$this->offline)
            {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
                $link = $ilCtrl->getLinkTargetByClass("illmpresentationgui", "showTableOfContents");
            }
            else
            {
                if ($this->export_all)
                {
                    $link = "./table_of_contents_".$this->lang.".html";
                }
                else
                {
                    $link = "./table_of_contents.html";
                }
            }
            $tabs_gui->$addcmd("cont_toc", $link,
                "", "", "", $active["toc"]);
        }

        // print view
        if($this->lm->isActivePrintView() && $ilAccess->checkAccess("read", "", $this->requested_ref_id))
        {
            if (!$this->offline)		// has to be implemented for offline mode
            {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
                $link = $ilCtrl->getLinkTargetByClass("illmpresentationgui", "showPrintViewSelection");
                $tabs_gui->$addcmd("cont_print_view", $link,
                    "", "", "", $active["print"]);
            }
        }

        // download
        if($ilUser->getId() == ANONYMOUS_USER_ID)
        {
            $is_public = $this->lm->isActiveDownloadsPublic();
        }
        else
        {
            $is_public = true;
        }

        if($this->lm->isActiveDownloads() && !$this->offline && $is_public &&
            $ilAccess->checkAccess("read", "", $this->requested_ref_id))
        {
            $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
            $link = $ilCtrl->getLinkTargetByClass("illmpresentationgui", "showDownloadList");
            $tabs_gui->$addcmd("download", $link,
                "", "", "", $active["download"]);
        }

        // info button
        if ($this->export_format != "scorm" && !$this->offline)
        {
            if (!$this->offline)
            {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
                $link = $this->ctrl->getLinkTargetByClass(
                    array("illmpresentationgui", "ilinfoscreengui"), "showSummary");
            }
            else
            {
                $link = "./info.html";
            }

            $tabs_gui->$addcmd('info_short', $link,
                "", "", "", $active["info"]);
        }

        if(!$this->offline &&
            $ilAccess->checkAccess("read", "", $this->requested_ref_id) && // #14075
            ilLearningProgressAccess::checkAccess($this->requested_ref_id))
        {
            $olp = ilObjectLP::getInstance($this->lm->getId());
            if($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL)
            {
                $tabs_gui->$addcmd("learning_progress",
                    $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "editManual"),
                    "", "", "", $active["learning_progress"]);
            }
            else if($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT)
            {
                $tabs_gui->$addcmd("learning_progress",
                    $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "showtlt"),
                    "", "", "", $active["learning_progress"]);
            }
        }

        // get user defined menu entries
        $entries = $this->menu_editor->getMenuEntries(true);
        if (count($entries) > 0 && $ilAccess->checkAccess("read", "", $this->requested_ref_id))
        {
            foreach ($entries as $entry)
            {
                // build goto-link for internal resources
                if ($entry["type"] == "intern")
                {
                    $entry["link"] = ILIAS_HTTP_PATH."/goto.php?target=".$entry["link"];
                }

                // add http:// prefix if not exist
                if (!strstr($entry["link"],'://') && !strstr($entry["link"],'mailto:'))
                {
                    $entry["link"] = "http://".$entry["link"];
                }

                if (!strstr($entry["link"],'mailto:'))
                {
                    $entry["link"] = ilUtil::appendUrlParameterString($entry["link"], "ref_id=".
                        $this->requested_ref_id."&structure_id=".$this->requested_obj_id);
                }
                $tabs_gui->$addcmd($entry["title"],
                    $entry["link"],
                    "", "", "_blank", "", true);
            }
        }

        // edit learning module
        if (!$this->offline)
        {
            if ($ilAccess->checkAccess("write", "", $this->requested_ref_id))
            {
                if ($this->current_page <= 0)
                {
                    $link = $this->ctrl->getLinkTargetByClass(["ilLMEditorGUI", "ilobjlearningmodulegui"], "chapters");
                }
                else
                {
                    $link = ILIAS_HTTP_PATH."/ilias.php?baseClass=ilLMEditorGUI&ref_id=".$this->requested_ref_id.
                        "&obj_id=".$this->current_page."&to_page=1";
                }
                $tabs_gui->addNonTabbedLink("edit_page",
                    $this->lng->txt("lm_editing_view"),
                    $link);
            }
        }

        // user interface hook [uihk]
        /*
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        $plugin_html = false;
        foreach ($pl_names as $pl)
        {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->modifyGUI("Modules/LearningModule", "lm_menu_tabs",
                array("lm_menu_tabs" => $tabs_gui));
        }*/

        return $tabs_gui->$getcmd();
    }


}
