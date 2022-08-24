<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * BlockGUI class for Selected Items on Personal Desktop
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_IsCalledBy ilPDSelectedItemsBlockGUI: ilColumnGUI
 * @ilCtrl_Calls ilPDSelectedItemsBlockGUI: ilCommonActionDispatcherGUI
 */
class ilPDSelectedItemsBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    private int $requested_item_ref_id;
    protected ilRbacSystem $rbacsystem;
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    public static string $block_type = 'pditems';
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected ilPDSelectedItemsBlockViewGUI $blockView;
    protected bool $manage = false;
    protected string $content = '';
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected \ILIAS\HTTP\Services $http;
    protected ilObjectService $objectService;
    protected ilFavouritesManager $favourites;
    protected ilTree $tree;
    protected ilPDSelectedItemsBlockListGUIFactory $list_factory;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->access = $DIC->access();
        $this->ui = $DIC->ui();
        $this->http = $DIC->http();
        $this->objectService = $DIC->object();
        $this->favourites = new ilFavouritesManager();
        $this->tree = $DIC->repositoryTree();

        parent::__construct();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->lng->loadLanguageModule('pd');
        $this->lng->loadLanguageModule('cntr'); // #14158
        $this->lng->loadLanguageModule('rep'); // #14158

        $this->setEnableNumInfo(false);
        $this->setLimit(99999);
        $this->allow_moving = false;

        $this->initViewSettings();

        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }

        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_item_ref_id = (int) ($params["item_ref_id"] ?? 0);
    }

    protected function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockViewSettings::VIEW_SELECTED_ITEMS
        );

        $this->viewSettings->parse();

        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function getViewSettings(): ilPDSelectedItemsBlockViewSettings
    {
        return $this->viewSettings;
    }

    public function addToDeskObject(): void
    {
        $this->favourites->add($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("rep_added_to_favourites"), true);
        $this->returnToContext();
    }

    protected function returnToContext(): void
    {
        $this->ctrl->setParameterByClass('ildashboardgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ildashboardgui', 'show');
    }

    public function removeFromDeskObject(): void
    {
        $this->lng->loadLanguageModule("rep");
        $this->favourites->remove($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("rep_removed_from_favourites"), true);
        $this->returnToContext();
    }

    public function getBlockType(): string
    {
        return static::$block_type;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    protected function getViewTitle(): string
    {
        return $this->blockView->getTitle();
    }

    public function getHTML(): string
    {
        global $DIC;

        $this->setTitle($this->getViewTitle());

        $DIC->database()->useSlave(true);

        // workaround to show details row
        $this->setData([['dummy']]);

        $DIC['ilHelp']->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, $this->blockView->getScreenId());

        $this->ctrl->clearParameters($this);

        $DIC->database()->useSlave(false);

        return parent::getHTML();
    }

    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd('getHTML');

        switch ($next_class) {
            case 'ilcommonactiondispatchergui':
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (method_exists($this, $cmd)) {
                    return $this->$cmd();
                } else {
                    return $this->{$cmd . 'Object'}();
                }
        }
        return "";
    }

    protected function getContent(): string
    {
        return $this->content;
    }

    protected function setContent(string $a_content): void
    {
        $this->content = $a_content;
    }

    public function fillDataSection(): void
    {
        if ($this->getContent() == '') {
            $this->setDataSection($this->blockView->getIntroductionHtml());
        } else {
            $this->tpl->setVariable('BLOCK_ROW', $this->getContent());
        }
    }

    protected function getGroupedCommandsForView(
        bool $manage = false
    ): array {
        $commandGroups = [];

        $sortingCommands = [];
        $sortings = $this->viewSettings->getSelectableSortingModes();
        $effectiveSorting = $this->viewSettings->getEffectiveSortingMode();
        // @todo: set checked on $sorting === $effectiveSorting
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
        // @todo: set checked on $presentation === $effectivePresentation
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

    public function changePDItemPresentation(): void
    {
        $this->viewSettings->storeActorPresentationMode(
            \ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        $this->initAndShow();
    }

    public function changePDItemSorting(): void
    {
        $this->viewSettings->storeActorSortingMode(
            \ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        $this->initAndShow();
    }

    protected function initAndShow(): void
    {
        $this->initViewSettings();

        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        }

        $this->returnToContext();
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

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
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
            $this->favourites->remove($this->user->getId(), $ref_id);
        }

        // #12909
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
                        // see ilObjCourseGUI:performUnsubscribeObject()
                        $members = new ilCourseParticipants(ilObject::_lookupObjId((int) $ref_id));
                        $members->delete($this->user->getId());

                        $members->sendUnsubscribeNotificationToAdmins($this->user->getId());
                        $members->sendNotification(
                            ilCourseMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
                            $this->user->getId()
                        );
                        break;

                    case 'grp':
                        // see ilObjGroupGUI:performUnsubscribeObject()
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
                        // do nothing
                        continue 2;
                }

                ilForumNotification::checkForumsExistsDelete($ref_id, $this->user->getId());
            }
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('mmbr_unsubscribed_from_objs'), true);
        $this->ctrl->returnToParent($this);
    }

    //
    // New rendering
    //

    protected bool $new_rendering = true;


    /**
     * Get items
     *
     * @return \ILIAS\UI\Component\Item\Group[]
     */
    protected function getListItemGroups(): array
    {
        global $DIC;
        $factory = $DIC->ui()->factory();

        $this->list_factory = new ilPDSelectedItemsBlockListGUIFactory($this, $this->blockView);

        $groupedItems = $this->blockView->getItemGroups();
        $groupedCommands = $this->getGroupedCommandsForView();
        foreach ($groupedCommands as $group) {
            foreach ($group as $command) {
                $asynch_url = $command['asyncUrl'] ?? "";
                $this->addBlockCommand(
                    (string) $command['url'],
                    (string) $command['txt'],
                    (string) $asynch_url
                );
            }
        }

        ////
        ///


        $item_groups = [];
        $list_items = [];

        foreach ($groupedItems as $group) {
            $list_items = [];

            foreach ($group->getItems() as $item) {
                try {
                    $itemListGUI = $this->list_factory->byType($item['type']);
                    ilObjectActivation::addListGUIActivationProperty($itemListGUI, $item);

                    $list_items[] = $this->getListItemForData($item);
                } catch (ilException $e) {
                    continue;
                }
            }
            if (count($list_items) > 0) {
                $item_groups[] = $factory->item()->group($group->getLabel(), $list_items);
            }
        }

        return $item_groups;
    }

    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $listFactory = $this->list_factory;

        /** @var ilObjectListGUI $itemListGui */
        $itemListGui = $listFactory->byType($data['type']);
        ilObjectActivation::addListGUIActivationProperty($itemListGui, $data);

        $list_item = $itemListGui->getAsListItem(
            (int) $data['ref_id'],
            (int) $data['obj_id'],
            (string) $data['type'],
            (string) $data['title'],
            (string) $data['description']
        );

        return $list_item;
    }

    protected function getCardForData(array $item): \ILIAS\UI\Component\Card\RepositoryObject
    {
        $listFactory = $this->list_factory;

        /** @var ilObjectListGUI $itemListGui */
        $itemListGui = $listFactory->byType($item['type']);
        ilObjectActivation::addListGUIActivationProperty($itemListGui, $item);

        $card = $itemListGui->getAsCard(
            (int) $item['ref_id'],
            (int) $item['obj_id'],
            (string) $item['type'],
            (string) $item['title'],
            (string) $item['description']
        );

        return $card;
    }

    protected function getLegacyContent(): string
    {
        $renderer = $this->ui->renderer();
        $factory = $this->ui->factory();

        $this->list_factory = new ilPDSelectedItemsBlockListGUIFactory($this, $this->blockView);

        $groupedCommands = $this->getGroupedCommandsForView();
        foreach ($groupedCommands as $group) {
            foreach ($group as $command) {
                $asynch_url = $command['asyncUrl'] ?? "";
                $this->addBlockCommand(
                    (string) $command['url'],
                    (string) $command['txt'],
                    (string) $asynch_url
                );
            }
        }

        $groupedItems = $this->blockView->getItemGroups();

        $subs = [];
        foreach ($groupedItems as $group) {
            $cards = [];

            foreach ($group->getItems() as $item) {
                try {
                    $itemListGUI = $this->list_factory->byType($item['type']);
                    ilObjectActivation::addListGUIActivationProperty($itemListGUI, $item);

                    $cards[] = $this->getCardForData($item);
                } catch (ilException $e) {
                    continue;
                }
            }
            if (count($cards) > 0) {
                $subs[] = $factory->panel()->sub(
                    $group->getLabel(),
                    $factory->deck($cards)->withNormalCardsSize()
                );
            }
        }


        return $renderer->render($subs);
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

        // action drop down
        if (is_array($groupedCommands[0])) {
            $actions = array_map(function ($item) use ($ui) {
                return $ui->factory()->link()->standard($item["txt"], $item["url"]);
            }, $groupedCommands[0]);
            if (count($actions) > 0) {
                $dd = $this->ui->factory()->dropdown()->standard($actions);
                $this->main_tpl->setHeaderActionMenu($ui->renderer()->render($dd));
            }
        }

        return (new ilDashObjectsTableRenderer($this))->render($grouped_items);
    }

    public function getNoItemFoundContent(): string
    {
        $txt = $this->lng->txt("rep_fav_intro1") . "<br>";
        $txt .= sprintf(
            $this->lng->txt('rep_fav_intro2'),
            $this->getRepositoryTitle()
        ) . "<br>";
        $txt .= $this->lng->txt("rep_fav_intro3");
        $mbox = $this->ui->factory()->messageBox()->info($txt);
        $mbox = $mbox->withLinks([$this->ui->factory()->link()->standard($this->getRepositoryTitle(), ilLink::_getStaticLink(1, 'root', true))]);
        return $this->ui->renderer()->render($mbox);
    }

    protected function getRepositoryTitle(): string
    {
        $nd = $this->tree->getNodeData($this->tree->getRootId());
        $title = $nd['title'];

        if ($title == 'ILIAS') {
            $title = $this->lng->txt('repository');
        }

        return $title;
    }
}
