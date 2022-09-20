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

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->locator = $DIC["ilLocator"];
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();
        $this->ui = $DIC->ui();
        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $lng->loadLanguageModule("prtf");
        $lng->loadLanguageModule("user");

        $this->access_handler = new ilPortfolioAccessHandler();

        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->user_id = $ilUser->getId();
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
            ilUtil::getImagePath("icon_prtf.svg"),
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

        $button = ilLinkButton::getInstance();
        $button->setCaption("prtf_add_portfolio");
        $button->setUrl($ilCtrl->getLinkTargetByClass("ilObjPortfolioGUI", "create"));
        $ilToolbar->addButtonInstance($button);
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
                ilUtil::getImagePath("icon_prtf.svg"),
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
            $action[] = $f->button()->shy($lng->txt("user_profile_preview"), $preview_action);
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

            if ($port["is_online"]) {
                if (!$port["is_default"]) {
                    //	... set as default
                    $ctrl->setParameter($this, "prt_id", $port["id"]);

                    $action[] = $f->button()->shy(
                        $lng->txt("prtf_set_as_default"),
                        $ctrl->getLinkTarget($this, "setDefaultConfirmation")
                    );

                    $ctrl->setParameter($this, "prt_id", "");
                } else {
                    //	... unset as default
                    $action[] = $f->button()->shy(
                        $lng->txt("prtf_unset_as_default"),
                        $ctrl->getLinkTarget($this, "unsetDefault")
                    );
                }
            }
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
            foreach ($exercises as $exinfo) {
                if ($exinfo["submitted"]) {
                    $props[$exinfo["ass_title"]] =
                        str_replace("$1", $exinfo["submitted_date"], $lng->txt("prtf_submission_on"));
                } else {
                    $props[$exinfo["ass_title"]] = $lng->txt("prtf_no_submission");
                    //$props[$exinfo["ass_title"]] = "<span class='il_ItemAlertProperty'>" . $lng->txt("prtf_no_submission") . "</span>";
                }
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
            $portfolio->setOnline(true);
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
            $portfolio->setOnline(false);
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
                    $portfolio->setOnline(true);
                } else {
                    $portfolio->setOnline(false);
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

    protected function unsetDefault(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        if ($this->checkAccess("write")) {
            // #12845
            $ilUser->setPref("public_profile", "n");
            $ilUser->writePrefs();

            ilObjPortfolio::setUserDefault($this->user_id);
            $this->tpl->setOnScreenMessage('success', $lng->txt("prtf_unset_default_share_info"), true);
        }
        $ilCtrl->redirect($this, "show");
    }

    /**
     * Confirm sharing when setting default
     */
    protected function setDefaultConfirmation(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilSetting = $this->settings;

        $prtf_id = $this->port_request->getPortfolioId();

        if ($prtf_id && $this->checkAccess("write")) {
            // if already shared, no need to ask again
            if ($this->access_handler->hasRegisteredPermission($prtf_id) ||
                $this->access_handler->hasGlobalPermission($prtf_id)) {
                $this->setDefault($prtf_id);
                return;
            }

            $ilTabs->clearTargets();
            $ilTabs->setBackTarget(
                $lng->txt("cancel"),
                $ilCtrl->getLinkTarget($this, "show")
            );

            $ilCtrl->setParameter($this, "prt_id", $prtf_id);

            // #20310
            if (!$ilSetting->get("enable_global_profiles")) {
                $ilCtrl->redirect($this, "setDefaultRegistered");
            }

            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("prtf_set_default_publish_confirmation"));
            $cgui->setCancel($lng->txt("prtf_set_default_publish_global"), "setDefaultGlobal");
            $cgui->setConfirm($lng->txt("prtf_set_default_publish_registered"), "setDefaultRegistered");
            $tpl->setContent($cgui->getHTML());

            return;
        }

        $ilCtrl->redirect($this, "show");
    }

    protected function setDefaultGlobal(): void
    {
        $ilCtrl = $this->ctrl;

        $prtf_id = $this->port_request->getPortfolioId();
        if ($prtf_id && $this->checkAccess("write")) {
            $this->access_handler->addPermission($prtf_id, ilWorkspaceAccessGUI::PERMISSION_ALL);
            $this->setDefault($prtf_id);
        }
        $ilCtrl->redirect($this, "show");
    }

    protected function setDefaultRegistered(): void
    {
        $ilCtrl = $this->ctrl;

        $prtf_id = $this->port_request->getPortfolioId();
        if ($prtf_id && $this->checkAccess("write")) {
            $this->access_handler->addPermission($prtf_id, ilWorkspaceAccessGUI::PERMISSION_REGISTERED);
            $this->setDefault($prtf_id);
        }
        $ilCtrl->redirect($this, "show");
    }

    protected function setDefault(int $a_prtf_id): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        if ($a_prtf_id && $this->checkAccess("write")) {
            // #12845
            if ($this->access_handler->hasGlobalPermission($a_prtf_id)) {
                $ilUser->setPref("public_profile", "g");
                $ilUser->writePrefs();
            } elseif ($this->access_handler->hasRegisteredPermission($a_prtf_id)) {
                $ilUser->setPref("public_profile", "y");
                $ilUser->writePrefs();
            } else {
                return;
            }
            ilObjPortfolio::setUserDefault($this->user_id, $a_prtf_id);
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }
        $ilCtrl->redirect($this, "show");
    }

    protected function getWorkspaceAccess(): ilPortfolioAccessHandler
    {
        /** @var ilWorkspaceAccessHandler $wsp_access */
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
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("otpf");

        $tbl = new ilWorkspaceShareTableGUI($this, "showOther", $this->getWorkspaceAccess(), null, $a_load_data);
        $tpl->setContent($tbl->getHTML());
    }

    protected function applyShareFilter(): void
    {
        $tbl = new ilWorkspaceShareTableGUI($this, "showOther", $this->getWorkspaceAccess());
        $tbl->resetOffset();
        $tbl->writeFilterToSession();

        $this->showOther();
    }

    protected function resetShareFilter(): void
    {
        $tbl = new ilWorkspaceShareTableGUI($this, "showOther", $this->getWorkspaceAccess());
        $tbl->resetOffset();
        $tbl->resetFilter();

        $this->showOther();
    }
}
