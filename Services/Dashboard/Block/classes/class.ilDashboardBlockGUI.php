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

declare(strict_types=1);

use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * @ilCtrl_IsCalledBy ilDashboardBlockGUI: ilColumnGUI
 * @ilCtrl_Calls ilDashboardBlockGUI: ilCommonActionDispatcherGUI
 */
abstract class ilDashboardBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    private string $content;
    private ilFavouritesManager $favourites;
    private ilRbacSystem $rbacsystem;
    private int $requested_item_ref_id;
    private mixed $object_cache;
    private ilTree $tree;
    private mixed $objDefinition;
    protected ilSetting $settings;
    protected ilLogger $logging;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\UI\Factory $factory;
    protected ILIAS\UI\Renderer $renderer;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
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
        $this->object_cache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC["objDefinition"];

        $this->new_rendering = true;
        $this->initViewSettings();
        $this->viewSettings->parse();
        $this->favourites = new ilFavouritesManager();
        $this->rbacsystem = $DIC->rbac()->system();

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }

        $params = $DIC->http()->request()->getQueryParams();
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
        $this->initData();

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
        $this->setTitle(
            $this->lng->txt('dash_' . $this->viewSettings->getViewName($this->viewSettings->getCurrentView()))
        );

        if (!$this->data) {
            return $this->emptyHandling();
        }

        $this->addCommandActions();
        $this->setData($this->getItemGroups());

        return parent::getHTML();
    }

    public function setData(array $a_data): void
    {
        $this->data = array_filter($a_data);
    }

    public function groupItemsByStartDate(): array
    {
        $data = $this->getData();
        $items = array_merge(...array_values($data));

        $groups = [
            'upcoming' => [],
            'ongoing' => [],
            'ended' => [],
            'not_dated' => []
        ];
        foreach ($items as $item) {
            if (isset($item['start'], $item['end']) && $item['start'] instanceof ilDateTime && $item['start']->get(IL_CAL_UNIX) > 0) {
                if ($item['start']->get(IL_CAL_UNIX) > time()) {
                    $groups['upcoming'][] = $item;
                } elseif ($item['end'] instanceof ilDateTime && $item['end']->get(IL_CAL_UNIX) > time()) {
                    $groups['ongoing'][] = $item;
                } else {
                    $groups['ended'][] = $item;
                }
            } else {
                $groups['not_dated'][] = $item;
            }
        }


        $orderByDate = static function (array $left, array $right, bool $asc = true) {
            if ($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX)) {
                return $asc ? -1 : 1;
            }

            if ($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX)) {
                return $asc ? 1 : -1;
            }

            return strcmp($left['title'], $right['title']);
        };

        uasort($groups['upcoming'], static fn ($left, $right) => $orderByDate($left, $right));
        uasort($groups['ongoing'], static fn ($left, $right) => $orderByDate($left, $right, false));
        uasort($groups['ended'], static fn ($left, $right) => $orderByDate($left, $right));
        $groups['not_dated'] = $this->sortByTitle($groups['not_dated']);

        // map keys to titles
        foreach ($groups as $key => $group) {
            $groups[$this->lng->txt('pd_' . $key)] = $group;
            unset($groups[$key]);
        }
        return $groups;
    }

    protected function groupItemsByType(): array
    {
        $object_types_by_container = $this->objDefinition->getGroupedRepositoryObjectTypes(
            ['cat', 'crs', 'grp', 'fold']
        );
        $grouped_items = [];
        $data = $this->getData();
        $data = array_merge(...array_values($data));
        $provider = new ilPDSelectedItemsBlockMembershipsProvider($this->viewSettings->getActor());

        foreach ($data as $item) {
            if (isset($object_types_by_container[$item['type']])) {
                $object_types_by_container[$item['type']]['items'][] = $item;
            }
        }

        foreach ($object_types_by_container as $type_title => $type) {
            if (!$this->objDefinition->isPlugin($type_title)) {
                $title = $this->lng->txt('objs_' . $type_title);
            } else {
                $pl = ilObjectPlugin::getPluginObjectByType($type_title);
                $title = $pl->txt("objs_" . $type_title);
            }

            if (isset($type['items'])) {
                $grouped_items[$title] = $type['items'];
            }
        }

        foreach ($grouped_items as $key => $group) {
            $grouped_items[$key] = $this->sortByTitle($group);
        }

        return $grouped_items;
    }

    protected function groupItemsByLocation(): array
    {
        $grouped_items = [];
        $data = $this->getData();
        $data = array_merge(...array_values($data));

        $parent_ref_ids = array_values(array_unique(
            array_map(fn (array $item): ?int => $this->tree->getParentId($item['ref_id']), $data)
        ));
        $this->object_cache->preloadReferenceCache($parent_ref_ids);

        foreach ($data as $key => $item) {
            $parent_ref = $this->tree->getParentId($item['ref_id']);
            if ($this->isRootNode($parent_ref)) {
                $title = $this->getRepositoryTitle();
            } else {
                $title = $this->object_cache->lookupTitle($this->object_cache->lookupObjId($parent_ref));
            }
            $grouped_items[$title][] = $item;
        }
        ksort($grouped_items);
        foreach ($grouped_items as $key => $group) {
            $grouped_items[$key] = $this->sortByTitle($group);
        }
        return $grouped_items;
    }

    protected function isRootNode(int $refId): bool
    {
        return $this->tree->getRootId() === $refId;
    }

    protected function getRepositoryTitle(): string
    {
        $nd = $this->tree->getNodeData($this->tree->getRootId());
        $title = $nd['title'];

        if ($title === 'ILIAS') {
            $title = $this->lng->txt('repository');
        }

        return $title;
    }

    public function addCommandActions(): void
    {
        $sortings = $this->viewSettings->getSelectableSortingModes();
        foreach ($sortings as $sorting) {
            $this->ctrl->setParameter($this, 'sorting', $sorting);
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'changePDItemSorting'),
                $this->lng->txt('dash_sort_by_' . $sorting),
                $this->ctrl->getLinkTarget($this, 'changePDItemSorting', '', true)
            );
            $this->ctrl->setParameter($this, 'sorting', null);
        }

        $presentations = $this->viewSettings->getSelectablePresentationModes();
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
                if (method_exists($this, $cmd . 'Object')) {
                    return $this->{$cmd . 'Object'}();
                }
        }
        return "";
    }

    public function changePDItemSortingObject(): void
    {
        $this->viewSettings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        $this->initAndShow();
    }

    public function changePDItemPresentationObject(): void
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

    public function getItemGroups(): array
    {
        switch ($this->viewSettings->getEffectiveSortingMode()) {
            case ilPDSelectedItemsBlockConstants::SORT_BY_ALPHABET:
                $data = $this->getData();
                $data = array_merge(...array_values($data));
                $data = $this->sortByTitle($data);
                return ['' => $data];
                break;
            case ilPDSelectedItemsBlockConstants::SORT_BY_START_DATE:
                return $this->groupItemsByStartDate();
            case ilPDSelectedItemsBlockConstants::SORT_BY_TYPE:
                return $this->groupItemsByType();
            case ilPDSelectedItemsBlockConstants::SORT_BY_LOCATION:
            default:
                return $this->groupItemsByLocation();
        }
    }

    public function manageObject(): string
    {
        $top_tb = new ilToolbarGUI();
        $top_tb->setFormAction($this->ctrl->getFormAction($this));
        $top_tb->setLeadingImage(ilUtil::getImagePath('arrow_upright.svg'), $this->lng->txt('actions'));

        $button = ilSubmitButton::getInstance();
        $grouped_items = [];
        $item_groups = $this->getItemGroups();
        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $button->setCaption('remove');
        } else {
            $button->setCaption('pd_unsubscribe_memberships');
        }
        foreach ($item_groups as $key => $item_group) {
            $group = new ilPDSelectedItemsBlockGroup();
            $group->setLabel($key);
            $items = [];
            foreach ($item_group as $item) {
                if ($this->rbacsystem->checkAccess('leave', $item['ref_id'])) {
                    $items[] = $item;
                }
            }
            $group->setItems($items);
            $grouped_items[] = $group;
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
            $actions = array_map(
                static fn (array $item): ILIAS\UI\Component\Link\Standard =>
                    $ui->factory()->link()->standard($item["txt"], $item["url"]),
                $groupedCommands[0]
            );
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
                'asyncUrl' => $this->ctrl->getLinkTarget($this, 'changePDItemSorting', '', true),
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
                'asyncUrl' => $this->ctrl->getLinkTarget($this, 'changePDItemPresentation', '', true),
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

    /**
     * @throws ilException
     */
    public function byType(string $a_type): ilObjectListGUI
    {
        $class = $this->objDefinition->getClassName($a_type);
        if (!$class) {
            throw new ilException(sprintf("Could not find a class for object type: %s", $a_type));
        }

        $location = $this->objDefinition->getLocation($a_type);
        if (!$location) {
            throw new ilException(sprintf("Could not find a class location for object type: %s", $a_type));
        }

        $full_class = 'ilObj' . $class . 'ListGUI';
        $item_list_gui = new $full_class();

        $item_list_gui->setContainerObject($this);
        $item_list_gui->enableNotes(false);
        $item_list_gui->enableComments(false);
        $item_list_gui->enableTags(false);

        $item_list_gui->enableIcon(true);
        $item_list_gui->enableDelete(false);
        $item_list_gui->enableCut(false);
        $item_list_gui->enableCopy(false);
        $item_list_gui->enableLink(false);
        $item_list_gui->enableInfoScreen(true);

        $item_list_gui->enableCommands(true, true);

        return $item_list_gui;
    }

    public function sortByTitle(array $data, bool $asc = true): array
    {
        uasort(
            $data,
            static fn ($left, $right) => $asc ?
                strcmp($left['title'], $right['title']) :
                strcmp($right['title'], $left['title'])
        );
        return $data;
    }
}
