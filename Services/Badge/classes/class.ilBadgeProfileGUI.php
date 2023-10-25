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

use ILIAS\Badge\Notification\BadgeNotificationPrefRepository;
use ILIAS\Badge\TileView;
use ILIAS\Badge\PresentationHeader;
use ILIAS\Badge\Tile;

/**
 * Class ilBadgeProfileGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeProfileGUI
{
    final public const BACKPACK_EMAIL = "badge_mozilla_bp";
    protected ilBadgeGUIRequest $request;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected \ILIAS\UI\Factory $factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected BadgeNotificationPrefRepository $noti_repo;
    private readonly TileView $tile_view;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = new ilBadgeGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->noti_repo = new BadgeNotificationPrefRepository();

        $this->tile_view = new TileView(
            $DIC,
            self::class,
            new Tile($DIC),
            new PresentationHeader($DIC, self::class)
        );
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("badge");

        switch ($ilCtrl->getNextClass()) {
            default:
                $this->setTabs();
                $cmd = $ilCtrl->getCmd("listBadges");
                $this->$cmd();
                break;
        }
    }

    protected function setTabs(): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    }

    protected function getSubTabs(string $a_active): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->addTab(
            "list",
            $lng->txt("badge_profile_view"),
            $ilCtrl->getLinkTarget($this, "listBadges")
        );
        $ilTabs->addTab(
            "manage",
            $lng->txt("badge_profile_manage"),
            $ilCtrl->getLinkTarget($this, "manageBadges")
        );
        $ilTabs->activateTab($a_active);
    }

    protected function listBadges(): void
    {
        $this->tpl->setContent($this->renderDeck($this->tile_view->show()));
    }

    private function renderDeck(string $deck): string
    {
        $template = new ilTemplate('tpl.badge_backpack.html', true, true, 'Services/Badge');
        $template->setVariable('DECK', $deck);
        return $template->get();
    }

    protected function manageBadges(): void
    {
        global $DIC;

        $table = new ilBadgePersonalTableGUI($this, "manageBadges");
        (new PresentationHeader($DIC, self::class))->show($this->lng->txt('table_view'));
        $this->tpl->setContent($table->getHTML());
    }

    protected function getMultiSelection(): array
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $ids = $this->request->getBadgeIds();
        if (count($ids) > 0) {
            $res = array();
            foreach ($ids as $id) {
                $ass = new ilBadgeAssignment($id, $ilUser->getId());
                if ($ass->getTimestamp()) {
                    $res[] = $ass;
                }
            }

            return $res;
        }

        $this->tpl->setOnScreenMessage('failure', $lng->txt("select_one"), true);
        $ilCtrl->redirect($this, "manageBadges");
        return [];
    }

    protected function activate(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        foreach ($this->getMultiSelection() as $ass) {
            // already active?
            if (!$ass->getPosition()) {
                $ass->setPosition(999);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("position_updated"), true);
        $ilCtrl->redirect($this, "manageBadges");
    }

    protected function deactivate(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        foreach ($this->getMultiSelection() as $ass) {
            // already inactive?
            if ($ass->getPosition()) {
                $ass->setPosition(null);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("position_updated"), true);
        $ilCtrl->redirect($this, "manageBadges");
    }

    protected function activateInCard(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        foreach ($this->getMultiSelection() as $ass) {
            // already active?
            if (!$ass->getPosition()) {
                $ass->setPosition(999);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("position_updated"), true);
        $ilCtrl->redirect($this, "listBadges");
    }

    protected function deactivateInCard(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        foreach ($this->getMultiSelection() as $ass) {
            // already inactive?
            if ($ass->getPosition()) {
                $ass->setPosition(null);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("position_updated"), true);
        $ilCtrl->redirect($this, "listBadges");
    }

    protected function setBackpackSubTabs(): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTab(
            "backpack_badges",
            $lng->txt("obj_bdga"),
            $ilCtrl->getLinkTarget($this, "listBackpackGroups")
        );

        $ilTabs->addSubTab(
            "backpack_settings",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "editSettings")
        );

        $ilTabs->activateTab("backpack_badges");
    }

    protected function listBackpackGroups(): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $this->setBackpackSubTabs();
        $ilTabs->activateSubTab("backpack_badges");

        $this->tpl->setOnScreenMessage('info', $lng->txt("badge_backpack_gallery_info"));

        $bp = new ilBadgeBackpack($this->getBackpackMail());
        $bp_groups = $bp->getGroups();

        if (!count($bp_groups)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("badge_backpack_no_groups"));
            return;
        }

        $tmpl = new ilTemplate("tpl.badge_backpack.html", true, true, "Services/Badge");

        $tmpl->setVariable("BACKPACK_TITLE", $lng->txt("badge_backpack_list"));

        ilDatePresentation::setUseRelativeDates(false);

        foreach ($bp_groups as $group_id => $group) {
            $bp_badges = $bp->getBadges($group_id);
            if (count($bp_badges)) {
                foreach ($bp_badges as $idx => $badge) {
                    $tmpl->setCurrentBlock("badge_bl");
                    $tmpl->setVariable("BADGE_TITLE", $badge["title"]);
                    $tmpl->setVariable("BADGE_DESC", $badge["description"]);
                    $tmpl->setVariable("BADGE_IMAGE", $badge["image_url"]);
                    $tmpl->setVariable("BADGE_CRITERIA", $badge["criteria_url"]);
                    $tmpl->setVariable("BADGE_ISSUER", $badge["issuer_name"]);
                    $tmpl->setVariable("BADGE_ISSUER_URL", $badge["issuer_url"]);
                    $tmpl->setVariable("BADGE_DATE", ilDatePresentation::formatDate($badge["issued_on"]));
                    $tmpl->parseCurrentBlock();
                }
            }

            $tmpl->setCurrentBlock("group_bl");
            $tmpl->setVariable("GROUP_TITLE", $group["title"]);
            $tmpl->parseCurrentBlock();
        }

        $tpl->setContent($tmpl->get());
    }

    //
    // settings
    //

    protected function getBackpackMail(): string
    {
        $ilUser = $this->user;

        $mail = $ilUser->getPref(self::BACKPACK_EMAIL);
        if (!$mail) {
            $mail = $ilUser->getEmail();
        }
        return $mail;
    }

    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveSettings"));
        $form->setTitle($lng->txt("settings"));

        $email = new ilEMailInputGUI($lng->txt("badge_backpack_email"), "email");
        // $email->setRequired(true);
        $email->setInfo($lng->txt("badge_backpack_email_info"));
        $email->setValue($this->getBackpackMail());
        $form->addItem($email);

        $form->addCommandButton("saveSettings", $lng->txt("save"));

        return $form;
    }

    protected function editSettings(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilCtrl->redirect($this, "listBadges");
    }

    protected function saveSettings(): void
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $new_email = $form->getInput("email");
            $old_email = $this->getBackpackMail();

            ilObjUser::_writePref($ilUser->getId(), self::BACKPACK_EMAIL, $new_email);

            // if email was changed: delete badge files
            if ($new_email != $old_email) {
                ilBadgeAssignment::clearBadgeCache($ilUser->getId());
            }

            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }
}
