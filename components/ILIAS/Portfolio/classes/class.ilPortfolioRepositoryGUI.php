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

use ILIAS\Portfolio\StandardGUIRequest;

/**
 * Portfolio repository gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilPortfolioRepositoryGUI: ilObjPortfolioGUI, ilObjExerciseGUI
 */
class ilPortfolioRepositoryGUI
{
    protected \ILIAS\PersonalWorkspace\InternalGUIService $workspace_gui;
    protected \ILIAS\Portfolio\InternalDomainService $domain;
    protected \ILIAS\Portfolio\InternalGUIService $gui;
    protected StandardGUIRequest $port_request;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilLocatorGUI $locator;
    protected ilToolbarGUI $toolbar;
    protected ilSetting $settings;
    protected int $user_id;
    protected ilPortfolioAccessHandler $access_handler;
    protected \ILIAS\DI\UIServices $ui;

    public function __construct()
    {
        global $DIC;

        $service = $DIC->portfolio()->internal();
        $this->workspace_gui = $DIC->personalWorkspace()->internal()->gui();
        $this->domain = $service->domain();
        $this->gui = $service->gui();

        $this->lng = $this->domain->lng();
        $this->user = $this->domain->user();
        $this->ctrl = $this->gui->ctrl();
        $this->tpl = $this->gui->ui()->mainTemplate();
        $this->tabs = $this->gui->tabs();
        $this->help = $this->gui->help();
        $this->locator = $this->gui->locator();
        $this->toolbar = $this->gui->toolbar();
        $this->settings = $this->domain->settings();
        $this->ui = $this->gui->ui();

        $this->lng->loadLanguageModule("prtf");
        $this->lng->loadLanguageModule("user");

        $this->access_handler = new ilPortfolioAccessHandler();

        $this->port_request = $this->gui->standardRequest();

        $this->user_id = $this->user->getId();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        if (!$this->access_handler->editPortfolios()) {
            throw new ilException($this->lng->txt("no_permission"));
        }

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("show");

        $tpl->setTitle($lng->txt("portfolio"));
        $tpl->setTitleIcon(
            ilUtil::getImagePath("standard/icon_prtf.svg"),
            $lng->txt("portfolio")
        );

        switch ($next_class) {
            case "ilobjportfoliogui":

                $gui = new ilObjPortfolioGUI($this->port_request->getPortfolioId());

                if ($cmd !== "preview") {
                    $this->setLocator();

                    $exercise_back_ref_id = $this->port_request->getExcBackRefId();
                    if ($exercise_back_ref_id > 0) {
                        $ilTabs->setBack2Target($lng->txt("obj_exc"), ilLink::_getLink($exercise_back_ref_id));
                    } else {
                        $ilTabs->setBack2Target($lng->txt("prtf_tab_portfolios"), $ilCtrl->getLinkTarget($this, "show"));
                    }
                }

                $ilCtrl->forwardCommand($gui);
                break;

            default:
                $this->setLocator();
                $this->setTabs();
                $this->$cmd();
                break;
        }
    }

    public function setTabs(): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("prtf");

        $ilTabs->addTab(
            "mypf",
            $lng->txt("prtf_tab_portfolios"),
            $ilCtrl->getLinkTarget($this)
        );

        $ilTabs->addTab(
            "otpf",
            $lng->txt("prtf_tab_other_users"),
            $ilCtrl->getLinkTarget($this, "showotherFilter")
        );

        $ilTabs->activateTab("mypf");
    }

    protected function setLocator(): void
    {
        $ilLocator = $this->locator;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $ilLocator->addItem(
            $lng->txt("portfolio"),
            $ilCtrl->getLinkTarget($this, "show")
        );

        $tpl->setLocator();
    }

    protected function checkAccess(
        string $a_permission,
        ?int $a_portfolio_id = null
    ): bool {
        if ($a_portfolio_id) {
            return $this->access_handler->checkAccess($a_permission, "", $a_portfolio_id);
        }
        // currently only object-based permissions
        return true;
    }


    //
    // LIST INCL. ACTIONS
    //

    protected function show(): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;

        $this->gui->button(
            $this->lng->txt("prtf_add_portfolio"),
            $ilCtrl->getLinkTargetByClass("ilObjPortfolioGUI", "create")
        )->toToolbar(true);

        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
        if (count($templates) > 0) {
            $this->gui->button(
                $this->lng->txt("prtf_add_portfolio_from_template"),
                $ilCtrl->getLinkTargetByClass("ilObjPortfolioGUI", "createFromTemplate")
            )->toToolbar(true);
        }

        $portfolio_list = $this->getPortfolioList();

        $tpl->setContent($portfolio_list);
    }

    protected function getPortfolioList(): string
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $renderer = $ui->renderer();
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $access_handler = new ilPortfolioAccessHandler();

        $shared_objects = $access_handler->getObjectsIShare(false);

        $items = [];

        foreach (ilObjPortfolio::getPortfoliosOfUser($this->user_id) as $port) {
            // icon
            $icon = $f->symbol()->icon()->custom(
                ilUtil::getImagePath("standard/icon_prtf.svg"),
                $lng->txt("obj_portfolio"),
                "medium"
            );
            if (!$port["is_online"] || !in_array($port["id"], $shared_objects)) {
                $icon = $icon->withDisabled(true);
            }

            // actions
            $prtf_path = array(get_class($this), "ilobjportfoliogui");
            $action = [];
            //	... preview
            $ctrl->setParameterByClass("ilobjportfoliogui", "prt_id", $port["id"]);
            $preview_action = $ctrl->getLinkTargetByClass($prtf_path, "preview");
            $action[] = $f->button()->shy($lng->txt("preview"), $preview_action);
            //	... edit content
            $action[] = $f->button()->shy(
                $lng->txt("prtf_edit_content"),
                $ctrl->getLinkTargetByClass($prtf_path, "view")
            );
            $ctrl->setParameter($this, "prt_id", $port["id"]);
            if ($port["is_online"]) {
                //	... set offline
                $action[] = $f->button()->shy(
                    $lng->txt("prtf_set_offline"),
                    $ctrl->getLinkTarget($this, "setOffline")
                );
            } else {
                //	... set online
                $action[] = $f->button()->shy(
                    $lng->txt("prtf_set_online"),
                    $ctrl->getLinkTarget($this, "setOnline")
                );
            }
            $ctrl->setParameter($this, "prt_id", "");
            //	... settings
            $action[] = $f->button()->shy(
                $lng->txt("settings"),
                $ctrl->getLinkTargetByClass($prtf_path, "edit")
            );
            //	... sharing
            $action[] = $f->button()->shy(
                $lng->txt("wsp_permissions"),
                $ctrl->getLinkTargetByClass(array(get_class($this), "ilobjportfoliogui", "ilWorkspaceAccessGUI"), "share")
            );
            $ctrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");

            // ... delete
            $ctrl->setParameter($this, "prtf", $port["id"]);
            $action[] = $f->button()->shy(
                $lng->txt("delete"),
                $ctrl->getLinkTarget($this, "confirmPortfolioDeletion")
            );
            $ctrl->setParameter($this, "prtf", "");
            $actions = $f->dropdown()->standard($action);


            // properties
            $props = [];
            // ... online
            $props[$lng->txt("online")] = ($port["is_online"])
                ? $lng->txt("yes")
                : $lng->txt("no");
            //: "<span class='il_ItemAlertProperty'>" . $lng->txt("no") . "</span>";
            // ... shared
            $props[$lng->txt("wsp_status_shared")] = (in_array($port["id"], $shared_objects))
                ? $lng->txt("yes")
                : $lng->txt("no");
            //: "<span class='il_ItemAlertProperty'>" . $lng->txt("no") . "</span>";
            // ... default (my profile)
            if ($port["is_default"]) {
                $props[$lng->txt("prtf_default_portfolio")] = $lng->txt("yes");
            }
            // ... handed in
            // exercise portfolio?
            $exercises = ilPortfolioExerciseGUI::checkExercise($this->user_id, $port["id"], false, true);
            $visible_to_tutor = false;
            foreach ($exercises as $exinfo) {
                if ($exinfo["submitted"]) {
                    $visible_to_tutor = true;
                    $props[$exinfo["ass_title"]] =
                        str_replace("$1", $exinfo["submitted_date"], $lng->txt("prtf_submission_on"));
                } else {
                    $props[$exinfo["ass_title"]] = $lng->txt("prtf_no_submission");
                    //$props[$exinfo["ass_title"]] = "<span class='il_ItemAlertProperty'>" . $lng->txt("prtf_no_submission") . "</span>";
                }
            }
            if ($visible_to_tutor) {
                $props[$lng->txt("prtf_visible_for_tutor")] = $lng->txt("yes");
            }


            $items[] = $f->item()->standard($f->button()->shy($port["title"], $preview_action))
                ->withActions($actions)
                ->withProperties($props)
                ->withLeadIcon($icon);
        }


        $std_list = $f->panel()->listing()->standard($lng->txt("prtf_portfolios"), array(
            $f->item()->group("", $items)
        ));

        return $renderer->render($std_list);
    }


    protected function setOnline(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $prt_id = $this->port_request->getPortfolioId();
        if (ilObjPortfolio::_lookupOwner($prt_id) === $this->user_id) {
            $portfolio = new ilObjPortfolio($prt_id, false);
            $portfolio->setOfflineStatus(false);
            $portfolio->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt("saved_successfully"), true);
            $ilCtrl->redirect($this, "show");
        }
        $ilCtrl->redirect($this, "show");
    }

    protected function setOffline(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $prt_id = $this->port_request->getPortfolioId();
        if (ilObjPortfolio::_lookupOwner($prt_id) === $this->user_id) {
            $portfolio = new ilObjPortfolio($prt_id, false);
            $portfolio->setOfflineStatus(true);
            $portfolio->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt("saved_successfully"), true);
            $ilCtrl->redirect($this, "show");
        }
        $ilCtrl->redirect($this, "show");
    }


    protected function saveTitles(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $titles = $this->port_request->getTitles();
        $online = $this->port_request->getOnline();
        foreach ($titles as $id => $title) {
            if (trim($title) && $this->checkAccess("write", $id)) {
                $portfolio = new ilObjPortfolio($id, false);
                $portfolio->setTitle(ilUtil::stripSlashes($title));

                if (in_array($id, $online)) {
                    $portfolio->setOfflineStatus(false);
                } else {
                    $portfolio->setOfflineStatus(true);
                }

                $portfolio->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("saved_successfully"), true);
        $ilCtrl->redirect($this, "show");
    }

    protected function confirmPortfolioDeletion(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $prtfs = $this->port_request->getPortfolioIds();

        if (count($prtfs) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "show");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("prtf_sure_delete_portfolios"));
            $cgui->setCancel($lng->txt("cancel"), "show");
            $cgui->setConfirm($lng->txt("delete"), "deletePortfolios");

            foreach ($prtfs as $id) {
                $cgui->addItem("prtfs[]", $id, ilObjPortfolio::_lookupTitle($id));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    protected function deletePortfolios(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $port_ids = $this->port_request->getPortfolioIds();
        foreach ($port_ids as $id) {
            if ($this->checkAccess("write", $id)) {
                $portfolio = new ilObjPortfolio($id, false);
                if ($portfolio->getOwner() === $this->user_id) {
                    $this->access_handler->removePermission($id);
                    $portfolio->delete();
                }
            }
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("prtf_portfolio_deleted"), true);
        $ilCtrl->redirect($this, "show");
    }


    //
    // DEFAULT PORTFOLIO (aka profile)
    //


    protected function getWorkspaceAccess(): ilPortfolioAccessHandler
    {
        $wsp_access = $this->access_handler;
        return $wsp_access;
    }



    //
    // SHARE
    //

    protected function showOtherFilter(): void
    {
        $this->showOther(false);
    }

    protected function showOther(
        bool $a_load_data = true
    ): void {
        $ilTabs = $this->tabs;
        $ilTabs->activateTab("otpf");
        $work_gui = $this->workspace_gui;
        $filter = $work_gui->workspaceShareFilter(
            "workspace_share_1_filter",
            $this,
            "showOther",
            $this->getWorkspaceAccess(),
            true
        );
        $filter_data = $filter->getData() ?? [];
        $show_data = $a_load_data || $this->hasShareFilterData($filter_data);
        $content = $filter->render();

        if ($show_data) {
            $table = $work_gui->workspaceShareTableBuilder(
                $this->getWorkspaceAccess(),
                true,
                0,
                $this,
                "showOther"
            )->getTable()->filterData($filter_data);
            if ($table->handleCommand()) {
                return;
            }
            $content .= $table->render();
        }

        $this->tpl->setContent($content);
    }

    protected function applyShareFilter(): void
    {
        $this->showOther();
    }

    protected function resetShareFilter(): void
    {
        $this->showOther(false);
    }

    public function redirectSendMailToSharer(string $share_id): void
    {
        $parts = explode("_", $share_id);
        $owner_id = isset($parts[0]) && ctype_digit($parts[0]) ? (int) $parts[0] : 0;
        $prt_id = isset($parts[1]) && ctype_digit($parts[1]) ? (int) $parts[1] : 0;

        if ($owner_id > 0) {
            $login = ilObjUser::_lookupLogin($owner_id);

            // #16530 - see ilObjCourseGUI::createMailSignature
            $sig = chr(13) . chr(10) . chr(13) . chr(10);
            $sig .= $this->lng->txt('prtf_permanent_link');
            $sig .= chr(13) . chr(10) . chr(13) . chr(10);
            $sig .= ilLink::_getStaticLink($prt_id, "prtf", true);
            $sig = rawurlencode(base64_encode($sig));

            ilUtil::redirect(ilMailFormCall::getRedirectTarget(
                $this,
                "showotherFilter",
                array(),
                array(
                    'type' => 'new',
                    'rcp_to' => $login,
                    ilMailFormCall::SIGNATURE_KEY => $sig
                )
            ));
        }
    }

    protected function hasShareFilterData(array $filter_data): bool
    {
        foreach (["user", "title", "acl_type", "acl_date"] as $key) {
            if (!empty($filter_data[$key])) {
                return true;
            }
        }

        return false;
    }

}
