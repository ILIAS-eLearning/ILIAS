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

use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Implementation\Component\ReplaceSignal;
use JetBrains\PhpStorm\NoReturn;
use ILIAS\UI\Component\Card\RepositoryObject;
use ILIAS\UI\Component\Item\Item;
use ILIAS\components\Dashboard\Block\BlockDTO;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Filesystem\Stream\Streams;

abstract class ilDashboardBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    private string $content;
    private ilRbacSystem $rbacsystem;
    private string $parent;
    protected ilFavouritesManager $favourites_manager;
    protected int $requested_item_ref_id;
    private mixed $object_cache;
    private ilTree $tree;
    private mixed $objDefinition;
    protected ilSetting $settings;
    protected ilLogger $logging;
    protected Services $http;
    private Factory $refinery;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    /** @var array<BlockDTO[]> */
    protected array $data;

    public function __construct()
    {
        parent::__construct();
        global $DIC;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->logging = $DIC->logger()->root();
        $this->settings = $DIC->settings();
        $this->object_cache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC['objDefinition'];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->favourites_manager = new ilFavouritesManager();
        $this->parent = $this->ctrl->getCurrentClassPath()[0] ?? '';
        $this->init();
    }

    abstract public function initViewSettings(): void;

    abstract public function initData(): void;

    abstract public function emptyHandling(): string;

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, int $ref_id): void
    {
    }

    protected function getCardForData(BlockDTO $data): ?RepositoryObject
    {
        $itemListGui = $this->byType($data->getType());
        $this->addCustomCommandsToActionMenu($itemListGui, $data->getRefId());
        $card = $itemListGui->getAsCard(
            $data->getRefId(),
            $data->getObjId(),
            $data->getType(),
            $data->getTitle(),
            $data->getDescription()
        );

        return $card;
    }

    protected function getListItemGroups(): array
    {
        $data = $this->loadData();
        $groupedCards = [];
        $obj_ids = [];
        foreach ($data as $group) {
            foreach ($group as $datum) {
                $obj_ids[] = $datum->getObjId();
            }
        }
        ilLPStatus::preloadListGUIData($obj_ids);

        foreach ($data as $title => $group) {
            $items = [];
            foreach ($group as $datum) {
                $item = $this->getListItemForDataDTO($datum);
                if ($item !== null) {
                    $items[] = $item;
                }
            }
            $groupedCards[] = $this->factory->item()->group((string) $title, $items);
        }

        return $groupedCards;
    }

    protected function getListItemForDataDTO(BlockDTO $data): ?Item
    {
        $itemListGui = $this->byType($data->getType());
        $this->addCustomCommandsToActionMenu($itemListGui, $data->getRefId());
        $list_item = $itemListGui->getAsListItem(
            $data->getRefId(),
            $data->getObjId(),
            $data->getType(),
            $data->getTitle(),
            $data->getDescription()
        );

        $list_item = $list_item->withProperties($list_item->getProperties() + $data->getAdditionalData());

        return $list_item;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    protected function getLegacyContent(): string
    {
        $groupedCards = [];
        foreach ($this->loadData() as $title => $group) {
            $cards = array_filter(array_map($this->getCardForData(...), $group));
            if ($cards) {
                $groupedCards[] = $this->ui->factory()->panel()->sub(
                    (string) $title,
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

    public function init(): void
    {
        $this->lng->loadLanguageModule('dash');
        $this->lng->loadLanguageModule('rep');
        $this->lng->loadLanguageModule('pd');
        $this->initViewSettings();
        $this->viewSettings->parse();
        $this->requested_item_ref_id = (int) ($this->http->request()->getQueryParams()['item_ref_id'] ?? 0);
        $this->initData();

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_SEC_LIST);
        }
    }

    protected function initAndShow(): string
    {
        $this->init();
        if ($this->parent === ilDashboardGUI::class) {
            $this->ctrl->redirectByClass(ilDashboardGUI::class, 'show');
        }

        return $this->getHTML();
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

    /**
     * @param array<BlockDTO[]> $a_data
     */
    public function setData(array $a_data): void
    {
        $this->data = array_filter(
            array_map(
                static fn($group) => array_filter($group, static fn($item) => $item instanceof BlockDTO),
                $a_data
            )
        );
    }

    /**
     * @return array<BlockDTO[]>
     */
    public function getData(): array
    {
        return parent::getData();
    }

    /**
     * @return array<BlockDTO[]>
     */
    public function groupItemsByStartDate(): array
    {
        $data = $this->getData();
        /** @var BlockDTO[] $items */
        $items = array_merge(...array_values($data));

        $groups = [
            'upcoming' => [],
            'ongoing' => [],
            'ended' => [],
            'not_dated' => []
        ];
        foreach ($items as $item) {
            if ($item->isDated()) {
                if ($item->hasNotStarted()) {
                    $groups['upcoming'][] = $item;
                } elseif ($item->isRunning()) {
                    $groups['ongoing'][] = $item;
                } else {
                    $groups['ended'][] = $item;
                }
            } else {
                $groups['not_dated'][] = $item;
            }
        }

        $orderByDate = static function (BlockDTO $left, BlockDTO $right, bool $asc = true): int {
            if ($left->getStartDate() && $right->getStartDate() && $left->getStartDate()->get(
                IL_CAL_UNIX
            ) < $right->getStartDate()->get(IL_CAL_UNIX)) {
                return $asc ? -1 : 1;
            }

            if ($left->getStartDate() && $right->getStartDate() && $left->getStartDate()->get(
                IL_CAL_UNIX
            ) > $right->getStartDate()->get(IL_CAL_UNIX)) {
                return $asc ? 1 : -1;
            }

            return strcmp($left->getTitle(), $right->getTitle());
        };

        uasort($groups['upcoming'], $orderByDate);
        uasort($groups['ongoing'], static fn(BlockDTO $left, BlockDTO $right): int => $orderByDate($left, $right, false));
        uasort($groups['ended'], $orderByDate);
        $groups['not_dated'] = $this->sortByTitle($groups['not_dated']);

        foreach ($groups as $key => $group) {
            $groups[$this->lng->txt('pd_' . $key)] = $group;
            unset($groups[$key]);
        }
        return $groups;
    }

    /**
     * @return array<BlockDTO[]>
     */
    protected function groupItemsByType(): array
    {
        $object_types_by_container = $this->objDefinition->getGroupedRepositoryObjectTypes(
            ['cat', 'crs', 'grp', 'fold']
        );
        $grouped_items = [];
        $data = $this->getData();
        /** @var BlockDTO[] $data */
        $data = array_merge(...array_values($data));

        foreach ($object_types_by_container as $type_title => $type) {
            if (!$this->objDefinition->isPlugin($type_title)) {
                $title = $this->lng->txt('objs_' . $type_title);
            } else {
                $pl = ilObjectPlugin::getPluginObjectByType($type_title);
                $title = $pl->txt('objs_' . $type_title);
            }

            foreach ($data as $item) {
                if (in_array($item->getType(), $type['objs'])) {
                    $grouped_items[$title][] = $item;
                }
            }
        }

        foreach ($grouped_items as $key => $group) {
            $grouped_items[$key] = $this->sortByTitle($group);
        }

        return $grouped_items;
    }

    /**
     * @return array<BlockDTO[]>
     */
    protected function groupItemsByLocation(): array
    {
        $grouped_items = [];
        $data = $this->getData();
        /** @var BlockDTO[] $data */
        $data = array_merge(...array_values($data));

        $parent_ref_ids = array_values(array_unique(
            array_map(fn(BlockDTO $item): ?int => $this->tree->getParentId($item->getRefId()), $data)
        ));
        $this->object_cache->preloadReferenceCache($parent_ref_ids);

        foreach ($data as $item) {
            $parent_ref = $this->tree->getParentId($item->getRefId());
            if ($this->isRootNode($parent_ref)) {
                $title = $this->getRepositoryTitle();
            } else {
                $title = $this->object_cache->lookupTitle($this->object_cache->lookupObjId($parent_ref));
            }
            $grouped_items[$title][] = $item;
        }
        ksort($grouped_items);
        $grouped_items = array_map($this->sortByTitle(...), $grouped_items);
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
        if (count($sortings) > 1) {
            foreach ($sortings as $sorting) {
                $this->addSortOption(
                    $sorting,
                    $this->lng->txt(ilObjDashboardSettingsGUI::DASH_SORT_PREFIX . $sorting),
                    $sorting === $this->viewSettings->getEffectiveSortingMode()
                );
            }
            $this->setSortTarget($this->ctrl->getLinkTarget($this, 'changePDItemSorting'));
        }

        $presentations = $this->viewSettings->getSelectablePresentationModes();
        foreach ($presentations as $presentation) {
            $this->ctrl->setParameter($this, 'presentation', $presentation);
            $this->addPresentation(
                $this->ui->renderer()->render($this->ui->factory()->symbol()->glyph()->{$presentation . 'View'}()),
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
                $presentation === $this->viewSettings->getEffectivePresentationMode()
            );
            $this->ctrl->setParameter($this, 'presentation', null);
        }

        if ($this->removeMultipleEnabled()) {
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'manage'),
                $this->getRemoveMultipleActionText(),
                '',
                $this->getRemoveModal()
            );
        }
    }

    public function getRemoveModal(): RoundTrip
    {
        $items = $this->getManageFields();
        if ($items !== []) {
            if ($this->viewSettings->isSelectedItemsViewActive()) {
                $question = $this->lng->txt('dash_info_sure_remove_from_favs');
            } else {
                $question = $this->lng->txt('mmbr_info_delete_sure_unsubscribe');
            }
            $modal = $this->ui->factory()->modal()->roundtrip(
                $this->getRemoveMultipleActionText(),
                [
                    $this->ui->factory()->messageBox()->confirmation($question),
                    $this->ui->factory()->messageBox()->info($this->lng->txt('select_one')),
                ],
                $items,
                $this->ctrl->getLinkTargetByClass([ilDashboardGUI::class, $this::class], 'confirmedRemove')
            )->withSubmitLabel($this->getRemoveMultipleActionText());

            $modal = $modal->withOnLoadCode(static fn($id) => "il.Dashboard.confirmModal($id)");
        } else {
            $modal = $this->ui->factory()->modal()->roundtrip(
                $this->getRemoveMultipleActionText(),
                $this->ui->factory()->messageBox()->info($this->lng->txt('pd_no_items_to_manage'))
            );
        }

        return $modal;
    }

    protected function getManageFields(): array
    {
        $inputs = [];
        foreach ($this->getItemGroups() as $key => $item_group) {
            $options = [];
            foreach ($item_group as $item) {
                $icon = $this->ui->renderer()->render($this->ui->factory()->symbol()->icon()->custom(ilObject::_getIcon($item->getObjId()), ''));
                if ($this instanceof ilMembershipBlockGUI) {
                    if ($this->rbacsystem->checkAccess('leave', $item->getRefId())) {
                        if ($item->getType() === 'crs' || $item->getType() === 'grp') {
                            $members_obj = ilParticipants::getInstance($item->getRefId());
                            if (!$members_obj->checkLastAdmin([$this->user->getId()])) {
                                continue;
                            }
                        }
                        $options[$item->getRefId()] = $icon . $item->getTitle();
                    }
                } else {
                    $options[$item->getRefId()] = $icon . $item->getTitle();
                }
            }
            if ($options !== []) {
                $inputs[] = $this->ui->factory()->input()->field()->multiSelect((string) $key, $options)
                    ->withAdditionalTransformation($this->refinery->to()->listOf($this->refinery->kindlyTo()->int()));
            }
        }

        return $inputs;
    }

    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd('getHTML');

        switch ($next_class) {
            case strtolower(ilCommonActionDispatcherGUI::class):
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                if ($gui instanceof ilCommonActionDispatcherGUI) {
                    $this->ctrl->forwardCommand($gui);
                }
                break;

            default:
                switch ($cmd) {
                    case 'confirmedRemove':
                        $form = $this->ui->factory()->input()->container()->form()->standard('', $this->getManageFields())->withRequest($this->http->request());
                        $this->confirmedRemove(array_merge(...array_filter($form->getData())));
                        // no break
                    default:
                        if (method_exists($this, $cmd . 'Object')) {
                            return $this->{$cmd . 'Object'}();
                        }
                }
        }
        return '';
    }

    public function viewDashboardObject(): void
    {
        $this->initAndShow();
    }

    public function changePDItemSortingObject(): string
    {
        $this->viewSettings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        return $this->initAndShow();
    }

    public function changePDItemPresentationObject(): string
    {
        $this->viewSettings->storeActorPresentationMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        return $this->initAndShow();
    }

    /**
     * @return array<BlockDTO[]>
     */
    public function getItemGroups(): array
    {
        switch ($this->viewSettings->getEffectiveSortingMode()) {
            case ilPDSelectedItemsBlockConstants::SORT_BY_ALPHABET:
                $data = $this->getData();
                $data = array_merge(...array_values($data));
                $data = $this->sortByTitle($data);
                return ['' => $data];
            case ilPDSelectedItemsBlockConstants::SORT_BY_START_DATE:
                return $this->groupItemsByStartDate();
            case ilPDSelectedItemsBlockConstants::SORT_BY_TYPE:
                return $this->groupItemsByType();
            case ilPDSelectedItemsBlockConstants::SORT_BY_LOCATION:
            default:
                return $this->groupItemsByLocation();
        }
    }

    public function addToDeskObject(): void
    {
        $this->favourites_manager->add($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('rep_added_to_favourites'), true);
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'show');
    }

    public function removeFromDeskObject(): void
    {
        $this->favourites_manager->remove($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('rep_removed_from_favourites'), true);
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'show');
    }

    abstract public function removeMultipleEnabled(): bool;

    abstract public function getRemoveMultipleActionText(): string;

    /**
     * @param int[] $ids
     */
    public function confirmedRemove(array $ids): void
    {
    }

    public function byType(string $a_type): ilObjectListGUI
    {
        $class = $this->objDefinition->getClassName($a_type);
        if (!$class) {
            throw new ilException(sprintf('Could not find a class for object type: %s', $a_type));
        }

        $location = $this->objDefinition->getLocation($a_type);
        if (!$location) {
            throw new ilException(sprintf('Could not find a class location for object type: %s', $a_type));
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

    /**
     * @param BlockDTO[] $data
     */
    private function sortByTitle(array $data): array
    {
        uasort(
            $data,
            static fn(BlockDTO $left, BlockDTO $right): int => strcmp($left->getTitle(), $right->getTitle())
        );
        return $data;
    }
}
