<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Menu / Tabs renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMMenuRendererGUI
{
    protected ilLanguage $lng;
    protected bool $export_all;
    protected int $requested_ref_id;
    protected int $requested_obj_id;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected int $current_page;
    protected ilObjLearningModule $lm;
    protected bool $offline;
    protected ilCtrl $ctrl;
    protected string $lang;
    protected string $active_tab;
    protected string $export_format;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ilLMMenuEditor $menu_editor;
    protected ilLMPresentationService $lm_pres_service;
    protected ilGlobalTemplateInterface $main_tpl;
    protected \ILIAS\UI\Factory $ui_factory;
    protected Closure $additional_content_collector;

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
        ilGlobalTemplateInterface $main_tpl,
        Closure $additional_content_collector
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
        $request = $lm_pres_service->getRequest();
        $this->requested_obj_id = $request->getObjId();
        $this->requested_ref_id = $request->getRefId();
    }

    public function render(): string
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        $getcmd = "getHTML";

        $content_active = ($this->active_tab === "content");

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
            if (!$content_active) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($this->lng->txt("content"), $ilCtrl->getLinkTargetByClass("illmpresentationgui", "layout"))
                );
            }
        } elseif ($this->offline) {
            $tabs_gui->setForcePresentationOfSingleTab(true);
        }

        if (!$content_active) {
            return "";
        }

        // info button
        if ($this->lm->isInfoEnabled()) {
            if (!$this->offline) {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
                $link = $this->ctrl->getLinkTargetByClass(
                    array("illmpresentationgui", "ilinfoscreengui"),
                    "showSummary"
                );
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($this->lng->txt("info_short"), $link)
                );
            }
        }

        if (!$this->offline &&
            $ilAccess->checkAccess("read", "", $this->requested_ref_id) && // #14075
            ilLearningProgressAccess::checkAccess($this->requested_ref_id)) {
            $olp = ilObjectLP::getInstance($this->lm->getId());

            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard(
                        $this->lng->txt("learning_progress"),
                        $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "editManual")
                    )
                );
            } elseif ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard(
                        $this->lng->txt("learning_progress"),
                        $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "showtlt")
                    )
                );
            }
        }

        // default entries (appearing in lsq and native mode)
        $menu = new \ILIAS\LearningModule\Menu\ilLMMenuGUI($this->lm_pres_service);
        foreach ($menu->getEntries() as $entry) {
            if (is_object($entry["signal"])) {
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard($entry["label"], '')
                                     ->withOnClick($entry["signal"])
                );
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
                    $this->ui_factory->button()->standard(
                        $this->lng->txt("edit_page"),
                        $link
                    )
                );
            }
        }

        return $tabs_gui->$getcmd();
    }
}
