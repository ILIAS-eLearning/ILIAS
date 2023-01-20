<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\MessageBox\MessageBox;

abstract class ilDashboardBlockGUI extends ilBlockGUI
{
    private string $content;
    private ilFavouritesManager $favourites;
    private ilRbacSystem $rbacsystem;
    private int $requested_item_ref_id;
    protected ilSetting $settings;
    protected ilLogger $logging;
    protected ilPDSelectedItemsBlockListGUIFactory $list_factory;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\UI\Factory $factory;
    protected ILIAS\UI\Renderer $renderer;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected ilPDSelectedItemsBlockViewGUI $blockView;
    /** @var array<string, array>  */
    protected array $data;


    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->logging = $DIC->logger()->root();
        $this->settings = $DIC->settings();

        $this->new_rendering = true;
        $this->initViewSettings();
        $this->viewSettings->parse();
        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);
        $this->list_factory = new ilPDSelectedItemsBlockListGUIFactory($this, $this->blockView);
        $this->favourites = new ilFavouritesManager();
        $this->rbacsystem = $DIC->rbac()->system();

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }

        $this->requested_item_ref_id = (int) ($params["item_ref_id"] ?? 0);

        $this->initData();
    }

    abstract public function initViewSettings(): void;

    abstract public function initData(): void;

    abstract public function emptyHandling(): string;

    abstract public function getCardForData(array $data): ?\ILIAS\UI\Component\Card\RepositoryObject;

    abstract public function getItemForData(array $data): ?\ILIAS\UI\Component\Item\Item;

    protected function getListItemGroups(): array
    {
        $data = $this->loadData();
        $groupedCards = [];
        foreach ($data as $title => $group) {
            $items = [];
            foreach ($group as $datum) {
                $item = $this->getListItemForData($datum);
                if ($item !== null) {
                    $items[] = $item;
                }
            }
            $groupedCards[] = $this->factory->item()->group((string) $title, $items);
        }


        return $groupedCards;
    }


    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        return $this->getItemForData($data);
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    protected function getLegacyContent(): string
    {
        $groupedCards = [];
        foreach ($this->loadData() as $title => $group) {
            $cards = [];
            foreach ($group as $datum) {
                $cards[] = $this->getCardForData($datum);
            }
            $cards = array_filter($cards, static function ($card) {
                return $card !== null;
            });
            if ($cards) {
                $groupedCards[] = $this->ui->factory()->panel()->sub(
                    $title,
                    $this->factory->deck($cards)->withNormalCardsSize()
                );
            }
        }

        if ($groupedCards) {
            return $this->renderer->render($groupedCards);
        }

        return $this->getNoItemFoundContent();
    }

    public function getNoItemFoundContent(): string
    {
        return $this->emptyHandling();
    }

    public function getViewSettings(): ilPDSelectedItemsBlockViewSettings
    {
        return $this->viewSettings;
    }

    protected function initAndShow(): void
    {
        $this->initViewSettings();
        $this->viewSettings->parse();

        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }

        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        }

        $this->returnToContext();
    }

    public function getHTML(): string
    {
        if (!$this->data) {
            return $this->emptyHandling();
        }

        $this->setTitle(
            $this->lng->txt('dash_' . $this->viewSettings->getViewName($this->viewSettings->getCurrentView()))
        );
        $this->addCommandActions();

        // sort
        $data = $this->getData();
        switch ($this->viewSettings->getEffectiveSortingMode()) {
            case ilPDSelectedItemsBlockConstants::SORT_BY_ALPHABET:
                uasort($data, static function ($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
                break;
            case ilPDSelectedItemsBlockConstants::SORT_BY_START_DATE:
                uasort($data, static function ($a, $b) {
                    return $a['lso_obj']->getCreateDate() <=> $b['lso_obj']->getCreateDate();
                });
                break;
        }
        $this->setData($data);

        return parent::getHTML();
    }

    public function addCommandActions(): void
    {
        $sortings = $this->viewSettings->getActiveSortingsByView($this->viewSettings->getCurrentView());
        foreach ($sortings as $sorting) {
            $this->ctrl->setParameter($this, 'sorting', $sorting);
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'changePDItemSorting'),
                $this->lng->txt('dash_sort_by_' . $sorting),
                $this->ctrl->getLinkTarget($this, 'changePDItemSorting', '', true)
            );
            $this->ctrl->setParameter($this, 'sorting', null);
        }

        $presentations = $this->viewSettings->getActivePresentationsByView($this->viewSettings->getCurrentView());
        foreach ($presentations as $presentation) {
            $this->ctrl->setParameter($this, 'presentation', $presentation);
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
                $this->lng->txt('pd_presentation_mode_' . $presentation),
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation', '', true)
            );
            $this->ctrl->setParameter($this, 'presentation', null);
        }

        $this->addBlockCommand(
            $this->ctrl->getLinkTarget($this, 'manage'),
            $this->viewSettings->isSelectedItemsViewActive() ?
                $this->lng->txt('pd_remove_multiple') :
                $this->lng->txt('pd_unsubscribe_multiple_memberships')
        );
    }

    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd('getHTML');

        switch ($next_class) {
            case 'ilcommonactiondispatchergui':
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                if ($gui instanceof ilCommonActionDispatcherGUI) {
                    $this->ctrl->forwardCommand($gui);
                }
                break;

            default:
                if (method_exists($this, $cmd)) {
                    return $this->$cmd();
                }
                if (method_exists($this, $cmd . 'Object')) {
                    return $this->{$cmd . 'Object'}();
                }
        }
        return "";
    }

    public function changePDItemSorting(): void
    {
        $this->viewSettings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        $this->initAndShow();
    }

    public function changePDItemPresentation(): void
    {
        $this->viewSettings->storeActorPresentationMode(
            \ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        $this->initAndShow();
    }

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function returnToContext(): void
    {
        $this->ctrl->setParameterByClass('ildashboardgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ildashboardgui', 'show');
    }

    public function manageObject(): string
    {
        $this->blockView->setIsInManageMode(true);

        $top_tb = new ilToolbarGUI();
        $top_tb->setFormAction($this->ctrl->getFormAction($this));
        $top_tb->setLeadingImage(ilUtil::getImagePath('arrow_upright.svg'), $this->lng->txt('actions'));

        $button = ilSubmitButton::getInstance();
        $grouped_items = $this->blockView->getItemGroups();
        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $button->setCaption('remove');
        } else {
            $button->setCaption('pd_unsubscribe_memberships');
            foreach ($grouped_items as $group) {
                $items = $group->getItems();
                $group->setItems([]);
                foreach ($items as $item) {
                    if ($this->rbacsystem->checkAccess('leave', $item['ref_id'])) {
                        $group->pushItem($item);
                    }
                }
            }
        }
        $button->setCommand('confirmRemove');
        $top_tb->addStickyItem($button);

        $top_tb->setCloseFormTag(false);

        $bot_tb = new ilToolbarGUI();
        $bot_tb->setLeadingImage(ilUtil::getImagePath('arrow_downright.svg'), $this->lng->txt('actions'));
        $bot_tb->addStickyItem($button);
        $bot_tb->setOpenFormTag(false);

        return $top_tb->getHTML() . $this->renderManageList($grouped_items) . $bot_tb->getHTML();
    }

    protected function renderManageList(array $grouped_items): string
    {
        $ui = $this->ui;

        $this->ctrl->setParameter($this, "manage", "1");
        $groupedCommands = $this->getGroupedCommandsForView(true);
        foreach ($groupedCommands as $group) {
            foreach ($group as $command) {
                $this->addBlockCommand(
                    (string) $command['url'],
                    (string) $command['txt'],
                    (string) ($command['asyncUrl'] ?? "")
                );
            }
        }

        if (is_array($groupedCommands[0])) {
            $actions = array_map(static function ($item) use ($ui) {
                return $ui->factory()->link()->standard($item["txt"], $item["url"]);
            }, $groupedCommands[0]);
            if (count($actions) > 0) {
                $dd = $this->ui->factory()->dropdown()->standard($actions);
                $this->main_tpl->setHeaderActionMenu($ui->renderer()->render($dd));
            }
        }

        return (new ilDashObjectsTableRenderer($this))->render($grouped_items);
    }

    public function addToDeskObject(): void
    {
        $this->favourites->add($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("rep_added_to_favourites"), true);
        $this->returnToContext();
    }


    public function removeFromDeskObject(): void
    {
        $this->lng->loadLanguageModule("rep");
        $this->favourites->remove($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("rep_removed_from_favourites"), true);
        $this->returnToContext();
    }


    public function confirmRemoveObject(): string
    {
        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());

        $refIds = (array) ($this->http->request()->getParsedBody()['id'] ?? []);
        if (0 === count($refIds)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'manage');
        }

        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $question = $this->lng->txt('dash_info_sure_remove_from_favs');
            $cmd = 'confirmedRemove';
        } else {
            $question = $this->lng->txt('mmbr_info_delete_sure_unsubscribe');
            $cmd = 'confirmedUnsubscribe';
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($question);

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt('cancel'), 'manage');
        $cgui->setConfirm($this->lng->txt('confirm'), $cmd);

        foreach ($refIds as $ref_id) {
            $obj_id = ilObject::_lookupObjectId((int) $ref_id);
            $title = ilObject::_lookupTitle($obj_id);
            $type = ilObject::_lookupType($obj_id);

            $cgui->addItem(
                'ref_id[]',
                $ref_id,
                $title,
                ilObject::_getIcon($obj_id, 'small', $type),
                $this->lng->txt('icon') . ' ' . $this->lng->txt('obj_' . $type)
            );
        }

        return $cgui->getHTML();
    }

    public function confirmedRemove(): void
    {
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if (0 === count($refIds)) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            $this->favourites->remove($this->user->getId(), (int) $ref_id);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('pd_remove_multi_confirm'), true);
        $this->ctrl->redirect($this, 'manage');
    }

    public function confirmedUnsubscribe(): void
    {
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if (0 === count($refIds)) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            if ($this->access->checkAccess('leave', '', (int) $ref_id)) {
                switch (ilObject::_lookupType($ref_id, true)) {
                    case 'crs':
                        $members = new ilCourseParticipants(ilObject::_lookupObjId((int) $ref_id));
                        $members->delete($this->user->getId());

                        $members->sendUnsubscribeNotificationToAdmins($this->user->getId());
                        $members->sendNotification(
                            ilCourseMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
                            $this->user->getId()
                        );
                        break;

                    case 'grp':
                        $members = new ilGroupParticipants(ilObject::_lookupObjId((int) $ref_id));
                        $members->delete($this->user->getId());

                        $members->sendNotification(
                            ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
                            $this->user->getId()
                        );
                        $members->sendNotification(
                            ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
                            $this->user->getId()
                        );
                        break;

                    default:
                        continue 2;
                }

                ilForumNotification::checkForumsExistsDelete($ref_id, $this->user->getId());
            }
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('mmbr_unsubscribed_from_objs'), true);
        $this->ctrl->returnToParent($this);
    }

    protected function getGroupedCommandsForView(
        bool $manage = false
    ): array {
        $commandGroups = [];

        $sortingCommands = [];
        $sortings = $this->viewSettings->getSelectableSortingModes();
        $effectiveSorting = $this->viewSettings->getEffectiveSortingMode();
        foreach ($sortings as $sorting) {
            $this->ctrl->setParameter($this, 'sorting', $sorting);
            $sortingCommands[] = [
                'txt' => $this->lng->txt('dash_sort_by_' . $sorting),
                'url' => $this->ctrl->getLinkTarget($this, 'changePDItemSorting'),
                'xxxasyncUrl' => $this->ctrl->getLinkTarget($this, 'changePDItemSorting', '', true),
                'active' => $sorting === $effectiveSorting,
            ];
            $this->ctrl->setParameter($this, 'sorting', null);
        }

        if (count($sortingCommands) > 1) {
            $commandGroups[] = $sortingCommands;
        }

        if ($manage) {
            return $commandGroups;
        }

        $presentationCommands = [];
        $presentations = $this->viewSettings->getSelectablePresentationModes();
        $effectivePresentation = $this->viewSettings->getEffectivePresentationMode();
        foreach ($presentations as $presentation) {
            $this->ctrl->setParameter($this, 'presentation', $presentation);
            $presentationCommands[] = [
                'txt' => $this->lng->txt('pd_presentation_mode_' . $presentation),
                'url' => $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
                'xxxasyncUrl' => $this->ctrl->getLinkTarget($this, 'changePDItemPresentation', '', true),
                'active' => $presentation === $effectivePresentation,
            ];
            $this->ctrl->setParameter($this, 'presentation', null);
        }

        if (count($presentationCommands) > 1) {
            $commandGroups[] = $presentationCommands;
        }

        $commandGroups[] = [
            [
                'txt' => $this->viewSettings->isSelectedItemsViewActive() ?
                    $this->lng->txt('pd_remove_multiple') :
                    $this->lng->txt('pd_unsubscribe_multiple_memberships'),
                'url' => $this->ctrl->getLinkTarget($this, 'manage'),
                'asyncUrl' => null,
                'active' => false,
            ]
        ];

        return $commandGroups;
    }
}
