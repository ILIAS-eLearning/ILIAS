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

use ILIAS\UI\Component\Table\Ordering;
use ILIAS\UI\Component\ViewControl\Sortation;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Card\RepositoryObject;
use ILIAS\UI\Component\Item\Item;
use ILIAS\components\Dashboard\Block\BlockDTO;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Data\URI;

abstract class ilDashboardBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    protected string $content;
    protected readonly ilRbacSystem $rbacsystem;
    protected string $parent;
    protected readonly ilFavouritesManager $favourites_manager;
    protected int $requested_item_ref_id;
    protected mixed $object_cache;
    protected readonly ilTree $tree;
    protected readonly mixed $obj_definition;
    protected readonly ilSetting $settings;
    protected readonly ilLogger $logging;
    protected readonly Services $http;
    protected readonly Factory $refinery;
    protected ilPDSelectedItemsBlockViewSettings $view_settings;
    /** @var array<BlockDTO[]> */
    protected array $data;
    private ?RoundTrip $manual_sort_modal = null;
    private readonly SignalGenerator $signal_generator;

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
        $this->obj_definition = $DIC['objDefinition'];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->favourites_manager = new ilFavouritesManager();
        $this->parent = $this->ctrl->getCurrentClassPath()[0] ?? '';
        $this->signal_generator = new SignalGenerator();
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
        $groupedCards = [];
        foreach ($this->loadData() as $title => $group) {
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

    final protected function isRepositoryObject(): bool
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

    protected function preloadData(array $data): void
    {
        $obj_ids = [];
        foreach ($data as $group) {
            foreach ($group as $datum) {
                $obj_ids[] = $datum->getObjId();
            }
        }
        ilLPStatus::preloadListGUIData($obj_ids);
        parent::preloadData($data);
    }

    public function getNoItemFoundContent(): string
    {
        return $this->emptyHandling();
    }

    public function getViewSettings(): ilPDSelectedItemsBlockViewSettings
    {
        return $this->view_settings;
    }

    public function init(): void
    {
        $this->lng->loadLanguageModule('dash');
        $this->lng->loadLanguageModule('pd');
        $this->initViewSettings();
        $this->view_settings->parse();
        $this->requested_item_ref_id = (int) ($this->http->request()->getQueryParams()['item_ref_id'] ?? 0);
        $this->initData();

        $this->ctrl->setParameter($this, 'view', $this->view_settings->getView());
        if ($this->view_settings->isTilePresentation()) {
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
            $this->lng->txt('dash_' . $this->view_settings->getViewName($this->view_settings->getView()))
        );

        if (!$this->data) {
            return $this->emptyHandling();
        }

        $this->addCommandActions();
        $this->setData($this->getItemGroups());

        $modal = $this->manual_sort_modal ? $this->ui->renderer()->render($this->manual_sort_modal) : '';
        return parent::getHTML() . $modal;
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

        foreach ($groups as $key => $group) {
            $group = $this->sortByTitle($group);
            if ($key !== 'not_dated') {
                $group = $this->sortByDate($group, $key === 'upcoming');
            }
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
        $object_types_by_container = $this->obj_definition->getGroupedRepositoryObjectTypes(
            ['cat', 'crs', 'grp', 'fold']
        );
        $grouped_items = [];
        $data = $this->getData();
        /** @var BlockDTO[] $data */
        $data = array_merge(...array_values($data));

        foreach ($object_types_by_container as $type_title => $type) {
            if (!$this->obj_definition->isPlugin($type_title)) {
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
        $grouped_items = array_map($this->sortByTitle(...), $grouped_items);
        return $grouped_items;
    }

    final protected function isRootNode(int $refId): bool
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
        $sortings = $this->view_settings->getSelectableSortingModes();
        if (count($sortings) > 1) {
            foreach ($sortings as $sorting) {
                if ($sorting === ilPDSelectedItemsBlockConstants::SORT_MANUALLY) {
                    global $DIC;
                    $signal = $this->signal_generator->create();
                    // $signal = $DIC['ui.signal_generator']->create();
                    $this->manual_sort_modal = $this->ui->factory()->modal()->roundtrip(
                        $this->lng->txt('dash_manual_sorting_title'),
                        [$this->manually()]
                    )->withAdditionalOnLoadCode(fn($id) => "document.getElementById('$id').addEventListener('close', () => {window.location = window.location;});");

                    $this->manual_sort_modal = $this->manual_sort_modal->withAdditionalOnLoadCode(fn($id) => (
                        "il.Dashboard.moveModalButtons($id)"
                    ));
                }
                $this->addSortOption(
                    $sorting,
                    '<span data-action="' . $sorting . '">' . $this->lng->txt(ilObjDashboardSettingsGUI::DASH_SORT_PREFIX . $sorting) . '</span>',
                    $sorting === $this->view_settings->getEffectiveSortingMode()
                );
            }
            $this->setSortTarget($this->ctrl->getLinkTarget($this, 'changePDItemSorting'));
        }

        $presentations = $this->view_settings->getSelectablePresentationModes();
        foreach ($presentations as $presentation) {
            $this->ctrl->setParameter($this, 'presentation', $presentation);
            $this->addPresentation(
                $this->ui->renderer()->render($this->ui->factory()->symbol()->glyph()->{$presentation . 'View'}()),
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
                $presentation === $this->view_settings->getEffectivePresentationMode()
            );
            $this->ctrl->setParameter($this, 'presentation', null);
        }

        if ($this->removeMultipleEnabled()) {
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'manage'),
                $this->lng->txt('dash_' . $this->getBlockType() . '_remove_multiple'),
                '',
                $this->getRemoveModal()
            );
        }
    }

    public function getRemoveModal(): RoundTrip
    {
        $items = $this->getManageFields();
        if ($items !== []) {
            $modal = $this->ui->factory()->modal()->roundtrip(
                $this->lng->txt('dash_' . $this->getBlockType() . '_remove_multiple'),
                [
                    $this->ui->factory()->messageBox()->confirmation($this->lng->txt('dash_' . $this->getBlockType() . '_remove_info')),
                    $this->ui->factory()->messageBox()->info($this->lng->txt('select_one')),
                ],
                $items,
                $this->ctrl->getLinkTargetByClass([ilDashboardGUI::class, $this::class], 'confirmedRemove')
            )->withSubmitLabel($this->lng->txt('dash_' . $this->getBlockType() . '_remove'));

            $modal = $modal->withOnLoadCode(static fn($id) => "il.Dashboard.confirmModal($id)");
        } else {
            $modal = $this->ui->factory()->modal()->roundtrip(
                $this->lng->txt('dash_' . $this->getBlockType() . '_remove_multiple'),
                $this->ui->factory()->messageBox()->info($this->lng->txt('dash_no_items_to_manage'))
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

    public function manually(): Ordering
    {
        $request = $this->http->request();
        $columns = [
            'title' => $this->factory->table()->column()->text('Title')
        ];

        $records = null;
        $proc = function ($b) use (&$records) {
            return yield from array_map(fn($x) => $b->buildOrderingRow((string) $x['id'], $x), $records);
        };

        $uri = new URI((string) $request->getUri());
        parse_str($uri->getQuery(), $query);
        $uri = $uri->withQuery(http_build_query(array_merge(
            $query,
            ['view' => $this->view_settings->getView()]
        )));
        $table = $this->factory->table()
            ->ordering(new \ILIAS\Dashboard\TableData($proc), $uri, '', $columns)
            ->withRequest($request);

        $int = $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->always(null)]);

        if ($request->getMethod() === 'POST' && $this->view_settings->getView() === $this->http->wrapper()->query()->retrieve('view', $int)) {
            $data = $table->getData();
            if ($data) {
                $this->view_settings->storeActorSortingMode('manually');
                $this->view_settings->storeActorSortingData(array_flip($data));
                $this->ctrl->redirectByClass(ilDashboardGUI::class);
            }
        }

        $icon_for = fn(int $obj_id) => $this->renderer->render(
            $this->ui->factory()->symbol()->icon()->custom(ilObject::_getIcon($obj_id), '')
        );

        $records = array_map(fn($x) => [
            'id' => $x->getRefId(),
            'title' => $icon_for($x->getObjId()) . $x->getTitle(),
        ], array_values($this->sortManually($this->getItemGroups())));

        return $table;
    }

    public function getViewControlsForPanel(): array
    {
        global $DIC;
        if (!$this->manual_sort_modal) {
            return parent::getViewControlsForPanel();
        }
        $show = $this->manual_sort_modal->getShowSignal();
        $modal_signals = json_encode(['manually' => (string) $show]);
        $url = json_encode($this->ctrl->getLinkTarget($this, 'changePDItemSorting'));
        $signal = $this->signal_generator->create();
        $code = fn($id) => "il.Dashboard.showModalOnSort($id, $url, '$signal', $modal_signals)";
        $compontents = array_map(
            fn($x) => $x instanceof Sortation ? $x->withOnSort($signal)->withAdditionalOnLoadCode($code) : $x,
            parent::getViewControlsForPanel()
        );

        return $compontents;
    }

    public function viewDashboardObject(): void
    {
        $this->initAndShow();
    }

    public function changePDItemSortingObject(): string
    {
        $this->view_settings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        return $this->initAndShow();
    }

    public function changePDItemPresentationObject(): string
    {
        $this->view_settings->storeActorPresentationMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        return $this->initAndShow();
    }

    /**
     * @return array<BlockDTO[]>
     */
    public function getItemGroups(): array
    {
        switch ($this->view_settings->getEffectiveSortingMode()) {
            case ilPDSelectedItemsBlockConstants::SORT_BY_ALPHABET:
                $data = $this->getData();
                $data = array_merge(...array_values($data));
                $data = $this->sortByTitle($data);
                return ['' => $data];
            case ilPDSelectedItemsBlockConstants::SORT_BY_START_DATE:
                return $this->groupItemsByStartDate();
            case ilPDSelectedItemsBlockConstants::SORT_MANUALLY:
                return ['' => $this->sortManually($this->getData())];
            case ilPDSelectedItemsBlockConstants::SORT_BY_TYPE:
                $groups = $this->groupItemsByType();
                ksort($groups, SORT_NATURAL);
                return $groups;
            case ilPDSelectedItemsBlockConstants::SORT_BY_LOCATION:
            default:
                $groups = $this->groupItemsByLocation();
                ksort($groups, SORT_NATURAL);
                return $groups;
        }
    }

    public function getPaginationViewControl(): null
    {
        return null;
    }

    public function addToDeskObject(): void
    {
        $this->favourites_manager->add($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('added_to_favourites'), true);
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'show');
    }

    public function removeFromDeskObject(): void
    {
        $this->favourites_manager->remove($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('removed_from_favourites'), true);
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'show');
    }

    public function removeMultipleEnabled(): bool
    {
        return false;
    }

    /**
     * @param int[] $ids
     */
    public function confirmedRemove(array $ids): void
    {
    }

    public function byType(string $a_type): ilObjectListGUI
    {
        $class = $this->obj_definition->getClassName($a_type);
        if (!$class) {
            throw new ilException(sprintf('Could not find a class for object type: %s', $a_type));
        }

        $location = $this->obj_definition->getLocation($a_type);
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
    private function sortByDate(array $data, bool $asc = true): array
    {
        usort(
            $data,
            static fn(BlockDTO $left, BlockDTO $right): int =>
            ($asc ? -1 : 1) *
            (($right->getStartDate()?->get(IL_CAL_UNIX) ?? 0) - ($left->getStartDate()?->get(IL_CAL_UNIX) ?? 0))
        );
        return $data;
    }

    /**
     * @param BlockDTO[] $data
     */
    private function sortByTitle(array $data): array
    {
        usort(
            $data,
            static fn(BlockDTO $left, BlockDTO $right): int => strcasecmp($left->getTitle(), $right->getTitle())
        );
        return $data;
    }

    private function sortManually(array $data): array
    {
        $data = array_merge(...array_values($data));
        $new_at_botton = 'bot' === ($this->view_settings->getEffectiveSortingOptions()['new_items'] ?? 'bot');
        $default = $new_at_botton ? INF : -INF;
        $manual_sorting = $this->view_settings->getEffectiveSortingData();
        usort($data, fn(BlockDTO $l, BlockDTO $r) => (
            ($manual_sorting[$l->getRefId()] ?? $default) <=> ($manual_sorting[$r->getRefId()] ?? $default)
        ));

        return $data;
    }
}
