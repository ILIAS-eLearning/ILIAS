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

class ilBadgeProfileGUI implements ilCtrlSecurityInterface
{
    private const string LIST_BADGES_ACTION = 'listBadges';
    private const string MANAGE_BADGES_ACTION = 'manageBadges';
    private const string TABLE_ACTIONS = 'handleTableActions';
    private const string DEFAULT_ACTION = self::LIST_BADGES_ACTION;

    public const string TABLE_ALL_OBJECTS_ACTION = 'ALL_OBJECTS';

    final public const string BACKPACK_EMAIL = 'badge_mozilla_bp';

    private readonly ilBadgeGUIRequest $request;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilTabsGUI $tabs;
    private readonly ilObjUser $user;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly \ILIAS\HTTP\GlobalHttpState $http;
    private readonly BadgeNotificationPrefRepository $noti_repo;
    private readonly TileView $tile_view;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC['tpl'];
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->refinery = $DIC->refinery();
        $this->http = $DIC->http();
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
        $this->lng->loadLanguageModule('badge');

        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->ctrl->getCmd(self::DEFAULT_ACTION);
                if ($cmd === '' || $cmd === null || !method_exists($this, $cmd . 'Cmd')) {
                    $cmd = self::DEFAULT_ACTION;
                }
                $cmd .= 'Cmd';

                $this->$cmd();
                break;
        }
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    private function getTableAction(): ?string
    {
        return $this->http->wrapper()->query()->retrieve(
            'badge_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
    }

    private function handleTableActionsCmd(): void
    {
        match ($this->getTableAction()) {
            'obj_badge_activate' => $this->activate(),
            'obj_badge_deactivate' => $this->deactivate(),
            default => $this->ctrl->redirect($this, self::DEFAULT_ACTION),
        };
    }

    public function getUnsafeGetCommands(): array
    {
        return [self::TABLE_ACTIONS];
    }

    private function listBadgesCmd(): void
    {
        $this->tpl->setContent($this->renderDeck($this->tile_view->show()));
        $this->noti_repo->updateLastCheckedTimestamp();
    }

    private function renderDeck(string $deck): string
    {
        $template = new ilTemplate('tpl.badge_backpack.html', true, true, 'components/ILIAS/Badge/');
        $template->setVariable('DECK', $deck);
        return $template->get();
    }

    private function manageBadgesCmd(): void
    {
        $tpl = new ilBadgePersonalTableGUI();
        $tpl->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    private function getMultiSelection(): array
    {
        $badge_ids = array_filter(
            $this->http->wrapper()->query()->retrieve(
                'badge_id',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                    $this->refinery->always([])
                ])
            )
        );

        if ($badge_ids === [self::TABLE_ALL_OBJECTS_ACTION]) {
            $badge_assignments = array_filter(
                ilBadgeAssignment::getInstancesByUserId($this->user->getId()),
                static fn(ilBadgeAssignment $ass): bool => (bool) $ass->getTimestamp()
            );
        } else {
            $badge_assignments = array_filter(
                array_map(
                    fn(int $badge_id): ilBadgeAssignment => new ilBadgeAssignment(
                        $badge_id,
                        $this->user->getId()
                    ),
                    $this->request->getBadgeIds()
                ),
                static fn(ilBadgeAssignment $ass): bool => (bool) $ass->getTimestamp()
            );
        }

        if (!empty($badge_assignments)) {
            return $badge_assignments;
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('select_one'), true);
        $this->ctrl->redirect($this, self::MANAGE_BADGES_ACTION);
    }

    private function activate(): void
    {
        foreach ($this->getMultiSelection() as $ass) {
            // already active?
            if (!$ass->getPosition()) {
                $ass->setPosition(999);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('position_updated'), true);
        $this->ctrl->redirect($this, self::MANAGE_BADGES_ACTION);
    }

    private function deactivate(): void
    {
        foreach ($this->getMultiSelection() as $ass) {
            // already inactive?
            if ($ass->getPosition()) {
                $ass->setPosition(null);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('position_updated'), true);
        $this->ctrl->redirect($this, self::MANAGE_BADGES_ACTION);
    }

    private function activateInCardCmd(): void
    {
        foreach ($this->getMultiSelection() as $ass) {
            // already active?
            if (!$ass->getPosition()) {
                $ass->setPosition(999);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('position_updated'), true);
        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    private function deactivateInCardCmd(): void
    {
        foreach ($this->getMultiSelection() as $ass) {
            // already inactive?
            if ($ass->getPosition()) {
                $ass->setPosition(null);
                $ass->store();
            }
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('position_updated'), true);
        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    private function setBackpackSubTabs(): void
    {
        $this->tabs->addSubTab(
            'backpack_badges',
            $this->lng->txt('obj_bdga'),
            $this->ctrl->getLinkTarget($this, 'listBackpackGroups')
        );

        $this->tabs->addSubTab(
            'backpack_settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'editSettings')
        );

        $this->tabs->activateTab('backpack_badges');
    }

    private function listBackpackGroupsCmd(): void
    {
        $this->setBackpackSubTabs();
        $this->tabs->activateSubTab('backpack_badges');

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('badge_backpack_gallery_info'));

        $bp = new ilBadgeBackpack($this->getBackpackMail());
        $bp_groups = $bp->getGroups();

        if (!count($bp_groups)) {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('badge_backpack_no_groups'));
            return;
        }

        $tmpl = new ilTemplate('tpl.badge_backpack.html', true, true, 'components/ILIAS/Badge/');

        $tmpl->setVariable('BACKPACK_TITLE', $this->lng->txt('badge_backpack_list'));

        ilDatePresentation::setUseRelativeDates(false);

        foreach ($bp_groups as $group_id => $group) {
            $bp_badges = $bp->getBadges($group_id);
            if (count($bp_badges)) {
                foreach ($bp_badges as $badge) {
                    $tmpl->setCurrentBlock('badge_bl');
                    $tmpl->setVariable('BADGE_TITLE', $badge['title']);
                    $tmpl->setVariable('BADGE_DESC', $badge['description']);
                    $tmpl->setVariable('BADGE_IMAGE', $badge['image_url']);
                    $tmpl->setVariable('BADGE_CRITERIA', $badge['criteria_url']);
                    $tmpl->setVariable('BADGE_ISSUER', $badge['issuer_name']);
                    $tmpl->setVariable('BADGE_ISSUER_URL', $badge['issuer_url']);
                    $tmpl->setVariable('BADGE_DATE', ilDatePresentation::formatDate($badge['issued_on']));
                    $tmpl->parseCurrentBlock();
                }
            }

            $tmpl->setCurrentBlock('group_bl');
            $tmpl->setVariable('GROUP_TITLE', $group['title']);
            $tmpl->parseCurrentBlock();
        }

        $this->tpl->setContent($tmpl->get());
    }

    //
    // settings
    //

    private function getBackpackMail(): string
    {
        $mail = $this->user->getPref(self::BACKPACK_EMAIL);
        if (!$mail) {
            $mail = $this->user->getEmail();
        }

        return $mail;
    }

    private function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
        $form->setTitle($this->lng->txt('settings'));

        $email = new ilEMailInputGUI($this->lng->txt('badge_backpack_email'), 'email');
        $email->setInfo($this->lng->txt('badge_backpack_email_info'));
        $email->setValue($this->getBackpackMail());
        $form->addItem($email);

        $form->addCommandButton('saveSettings', $this->lng->txt('save'));

        return $form;
    }

    private function editSettingsCmd(?ilPropertyFormGUI $a_form = null): void
    {
        $this->ctrl->redirect($this, self::LIST_BADGES_ACTION);
    }

    private function saveSettingsCmd(): void
    {
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $new_email = $form->getInput('email');
            $old_email = $this->getBackpackMail();

            $this->user->writePref(self::BACKPACK_EMAIL, $new_email);

            // if email was changed: delete badge files
            if ($new_email !== $old_email) {
                ilBadgeAssignment::clearBadgeCache($this->user->getId());
            }

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'editSettings');
        }

        $form->setValuesByPost();
        $this->editSettingsCmd($form);
    }
}
