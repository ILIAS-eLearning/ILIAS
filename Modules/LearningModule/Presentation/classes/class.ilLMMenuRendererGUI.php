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
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLMMenuEditor
     */
    protected $menu_editor;

    /**
     * @var \ilLMPresentationService
     */
    protected $lm_pres_service;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $main_tpl;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
     * @var closure
     */
    protected $additional_content_collector;

    /**
     * Constructor
     */
    public function __construct(
        \ilLMPresentationService $lm_pres_service,
        ilTabsGUI $tabs,
        ilToolbarGUI $toolbar,
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
        ilLanguage $lng,
        $main_tpl,
        closure $additional_content_collector
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->active_tab = $active_tab;
        $this->export_format = $export_format;
        $this->export_all = $export_all;
        $this->tabs = $tabs;
        $this->menu_editor = $menu_editor;
        $this->lng = $lng;
        $this->lm_pres_service = $lm_pres_service;
        $this->toolbar = $toolbar;
        $this->main_tpl = $main_tpl;

        $this->additional_content_collector = $additional_content_collector;

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
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        $getcmd = "getHTML";

        $active[$this->active_tab] = true;

        if (!$this->lm->isActiveLMMenu()) {
            return "";
        }

        $tabs_gui = $ilTabs;

        // workaround for preventing tooltips in export
        if ($this->offline) {
            $tabs_gui->setSetupMode(true);
        }

        // content
        if (!$this->offline && $ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
            if (!$active["content"]) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($this->lng->txt("content"), $ilCtrl->getLinkTargetByClass("illmpresentationgui", "layout"))
                );
            }
        } elseif ($this->offline) {
            $tabs_gui->setForcePresentationOfSingleTab(true);
        }

        if (!$active["content"]) {
            return;
        }

        // info button
        if ($this->export_format != "scorm" && !$this->offline) {
            if (!$this->offline) {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
                $link = $this->ctrl->getLinkTargetByClass(
                    array("illmpresentationgui", "ilinfoscreengui"),
                    "showSummary"
                );
            } else {
                $link = "./info.html";
            }
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard($this->lng->txt("info_short"), $link)
            );
        }

        if (!$this->offline &&
            $ilAccess->checkAccess("read", "", $this->requested_ref_id) && // #14075
            ilLearningProgressAccess::checkAccess($this->requested_ref_id)) {

            $olp = ilObjectLP::getInstance($this->lm->getId());

            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($this->lng->txt("learning_progress"),
                        $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "editManual"))
                );

            } elseif ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($this->lng->txt("learning_progress"),
                        $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "showtlt")
                ));
            }
        }

        // default entries (appearing in lsq and native mode)
        $menu = new \ILIAS\LearningModule\Menu\ilLMMenuGUI($this->lm_pres_service);
        foreach ($menu->getEntries() as $entry) {
            if (is_object($entry["signal"])) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($entry["label"], '')
                                     ->withOnClick($entry["signal"]));
            }
            if (is_object($entry["modal"])) {
                ($this->additional_content_collector)($entry["modal"]);
            }
            if ($entry["on_load"] != "") {
                $this->main_tpl->addOnLoadCode($entry["on_load"]);
            }
        }

        // edit learning module
        if (!$this->offline) {
            if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
                if ($this->current_page <= 0) {
                    $link = $this->ctrl->getLinkTargetByClass(["ilLMEditorGUI", "ilobjlearningmodulegui"], "chapters");
                } else {
                    $link = ILIAS_HTTP_PATH . "/ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $this->requested_ref_id .
                        "&obj_id=" . $this->current_page . "&to_page=1";
                }
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($this->lng->txt("edit_page"),
                        $link
                    ));

            }
        }

        return $tabs_gui->$getcmd();
    }
}
