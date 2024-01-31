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

use ILIAS\UI\Implementation\Component\ReplaceSignal;
use JetBrains\PhpStorm\NoReturn;
use ILIAS\UI\Component\Card\RepositoryObject;
use ILIAS\UI\Component\Item\Item;
use ILIAS\components\Dashboard\Block\BlockDTO;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @ilCtrl_IsCalledBy ilDashboardBlockGUI: ilColumnGUI
 * @ilCtrl_Calls ilDashboardBlockGUI: ilCommonActionDispatcherGUI
 */
abstract class ilDashboardBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    private string $content;
    private ilRbacSystem $rbacsystem;
    protected ilFavouritesManager $favourites_manager;
    protected int $requested_item_ref_id;
    private mixed $object_cache;
    private ilTree $tree;
    private mixed $objDefinition;
    protected ilSetting $settings;
    protected ilLogger $logging;
    protected ILIAS\HTTP\Services $http;
    private ILIAS\Refinery\Factory $refinery;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    /** @var array<string, BlockDTO[]> */
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
        $this->objDefinition = $DIC["objDefinition"];
        $this->new_rendering = true;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->favourites_manager = new ilFavouritesManager();
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

    public function init(): void
    {
        $this->lng->loadLanguageModule('dash');
        $this->lng->loadLanguageModule('rep');
        $this->initViewSettings();
        $this->main_tpl->addJavaScript('assets/js/ReplaceModalContent.js');
        $this->viewSettings->parse();
        $this->requested_item_ref_id = (int) ($this->http->request()->getQueryParams()["item_ref_id"] ?? 0);
        $this->initData();

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_SEC_LIST);
        }
    }

    #[NoReturn]
    protected function initAndShow(): void
    {
        $this->init();
        if ($this->ctrl->isAsynch()) {
            $responseStream = Streams::ofString($this->getHTML());
            $response = $this->http->response()->withBody($responseStream);
            $this->http->saveResponse($response);
            $this->http->sendResponse();
            $this->http->close();
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

    /**
     * @param array<string, BlockDTO[]> $a_data
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
     * @return array<string, BlockDTO[]>
     */
    public function getData(): array
    {
        return parent::getData();
    }

    /**
     * @return array<string, BlockDTO[]>
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

        // map keys to titles
        foreach ($groups as $key => $group) {
            $groups[$this->lng->txt('pd_' . $key)] = $group;
            unset($groups[$key]);
        }
        return $groups;
    }

    /**
     * @return array<string, BlockDTO[]>
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
        $provider = new ilPDSelectedItemsBlockMembershipsProvider($this->viewSettings->getActor());

        foreach ($data as $item) {
            if (isset($object_types_by_container[$item->getType()])) {
                $object_types_by_container[$item->getType()]['items'][] = $item;
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

    /**
     * @return array<string, BlockDTO[]>
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

        foreach ($data as $key => $item) {
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
                $this->lng->txt('pd_presentation_mode_' . $presentation),
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
                $presentation === $this->viewSettings->getEffectivePresentationMode()
            );
            $this->ctrl->setParameter($this, 'presentation', null);
        }

        if ($this->removeMultipleEnabled()) {
            $roundtrip_modal = $this->ui->factory()->modal()->roundtrip(
                $this->getRemoveMultipleActionText(),
                $this->ui->factory()->legacy('PH')
            );
            $roundtrip_modal = $roundtrip_modal->withAsyncRenderUrl(
                $this->ctrl->getLinkTarget(
                    $this,
                    'removeFromDeskRoundtrip'
                ) . '&page=manage&replaceSignal=' . $roundtrip_modal->getReplaceSignal()->getId()
            );
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'manage'),
                $this->getRemoveMultipleActionText(),
                '',
                $roundtrip_modal
            );
        }
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

    #[NoReturn]
    public function viewDashboardObject(): void
    {
        $this->initAndShow();
    }

    #[NoReturn]
    public function changePDItemSortingObject(): void
    {
        $this->viewSettings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        $this->initAndShow();
    }

    #[NoReturn]
    public function changePDItemPresentationObject(): void
    {
        $this->viewSettings->storeActorPresentationMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        $this->initAndShow();
    }

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function returnToContext(): void
    {
        if ($this->http->request()->getQueryParams()['manage'] ?? false) {
            $this->ctrl->redirect($this, 'manage');
        }
        $this->ctrl->redirectByClass('ildashboardgui', 'show');
    }

    /**
     * @return array<string, BlockDTO[]>
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
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("rep_added_to_favourites"), true);
        $this->returnToContext();
    }

    public function removeFromDeskObject(): void
    {
        $this->favourites_manager->remove($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("rep_removed_from_favourites"), true);
        $this->returnToContext();
    }

    #[NoReturn]
    public function removeFromDeskRoundtripObject(): void
    {
        $page = '';
        if ($this->http->wrapper()->query()->has('page')) {
            $page = $this->http->wrapper()->query()->retrieve('page', $this->refinery->kindlyTo()->string());
        }

        if ($this->http->wrapper()->query()->has('replaceSignal')) {
            $signalId = $this->http->wrapper()->query()->retrieve(
                'replaceSignal',
                $this->refinery->kindlyTo()->string()
            );
            $replace_signal = new ReplaceSignal($signalId);
        }

        switch ($page) {
            case 'manage':
                $modal = $this->ui->factory()->modal()->roundtrip(
                    $this->getRemoveMultipleActionText(),
                    $this->ui->factory()->legacy($this->manage($replace_signal ?? null))
                );
                $content = $modal->withAdditionalOnLoadCode(function ($id) {
                    return "
                    $('#$id').attr('data-modal-name', 'remove_modal_view_" . $this->viewSettings->getCurrentView() . "');
                    ";
                });
                break;
            case 'confirm':
            default:
                if ($this->viewSettings->isSelectedItemsViewActive()) {
                    $question = $this->lng->txt('dash_info_sure_remove_from_favs');
                } else {
                    $question = $this->lng->txt('mmbr_info_delete_sure_unsubscribe');
                }
                $content = [
                    $this->ui->factory()->messageBox()->confirmation($question),
                    $this->ui->factory()->legacy($this->confirmRemoveObject())
                ];
        }
        $responseStream = Streams::ofString($this->ui->renderer()->renderAsync($content));
        $this->http->saveResponse(
            $this->http->response()
                       ->withBody($responseStream)
                       ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    public function manage(ReplaceSignal $replace_signal = null): string
    {
        $page = '';
        if ($this->http->wrapper()->query()->has('page')) {
            $page = $this->http->wrapper()->query()->retrieve('page', $this->refinery->kindlyTo()->string());
        }
        $top_tb = new ilToolbarGUI();
        $top_tb->setFormAction($this->ctrl->getFormAction($this, 'confirmRemove'));
        $top_tb->setFormName('pd_remove_multiple_view_' . $this->viewSettings->getCurrentView());
        $top_tb->setId('pd_remove_multiple_view_' . $this->viewSettings->getCurrentView());
        $top_tb->setLeadingImage(ilUtil::getImagePath('nav/arrow_upright.svg'), $this->lng->txt('actions'));
        $this->ctrl->setParameter($this, 'page', 'confirm');
        $url = $this->ctrl->getLinkTarget(
            $this,
            'removeFromDeskRoundtrip',
            '',
            true
        );
        $this->ctrl->clearParameters($this);
        $button = $this->ui->factory()->button()->standard($this->getRemoveMultipleActionText(), '#')
            ->withOnLoadCode(function ($id) use ($url): string {
                return "
                        il.Dashboard.replaceModalContent('$id', " . $this->viewSettings->getCurrentView() . ", '$url');
                ";
            });

        $grouped_items = [];
        $item_groups = $this->getItemGroups();
        foreach ($item_groups as $key => $item_group) {
            $group = new ilPDSelectedItemsBlockGroup();
            $group->setLabel($key);
            $items = [];
            foreach ($item_group as $item) {
                if ($this->rbacsystem->checkAccess('leave', $item->getRefId())) {
                    if ($item->getType() === 'crs') {
                        $members_obj = ilParticipants::getInstance($item->getRefId());
                        if (!$members_obj->checkLastAdmin([$this->user->getId()])) {
                            continue;
                        }
                    }
                    $items[] = $item;
                }
            }
            $group->setItems(array_map(static fn(BlockDTO $item): array => $item->toArray(), $items));
            $grouped_items[] = $group;
        }
        $top_tb->addStickyItem($button);

        $top_tb->setCloseFormTag(false);

        $bot_tb = new ilToolbarGUI();
        $bot_tb->setLeadingImage(ilUtil::getImagePath('nav/arrow_downright.svg'), $this->lng->txt('actions'));
        $bot_tb->addStickyItem($button);
        $bot_tb->setOpenFormTag(false);

        $tpl = new ilTemplate('tpl.remove_multiple_modal_id_wrapper.html', true, true, 'components/ILIAS/Dashboard');
        $tpl->setVariable('CONTENT', $top_tb->getHTML() . $this->renderManageList($grouped_items) . $bot_tb->getHTML());
        $tpl->setVariable('VIEW', $this->viewSettings->getCurrentView());

        return $tpl->get();
    }

    protected function renderManageList(array $grouped_items): string
    {
        $this->ctrl->setParameter($this, "manage", "1");
        $title = '';
        if (
            $this->viewSettings->isSelectedItemsViewActive() ||
            $this->viewSettings->isRecommendedContentViewActive() ||
            $this->viewSettings->isMembershipsViewActive()
        ) {
            $title .= $this->lng->txt('remove');
        } else {
            $title .= $this->lng->txt('pd_unsubscribe_memberships');
        }
        $title .= ' ' . strtolower($this->lng->txt('from')) . ' ' .
            $this->lng->txt('dash_' . $this->viewSettings->getViewName($this->viewSettings->getCurrentView()));
        $this->main_tpl->setTitle($title);

        return (new ilDashObjectsTableRenderer($this))->render($grouped_items);
    }

    public function confirmRemoveObject(): string
    {
        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());

        $refIds = (array) ($this->http->request()->getParsedBody()['id'] ?? []);
        if ($refIds === []) {
            $message_box = $this->ui->factory()->messageBox()->info($this->lng->txt('select_one'));
            return $this->ui->renderer()->render($message_box);
        }

        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $question = $this->lng->txt('dash_info_sure_remove_from_favs');
        } else {
            $question = $this->lng->txt('mmbr_info_delete_sure_unsubscribe');
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($question);

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt('cancel'), 'viewDashboard');
        $cgui->setConfirm($this->lng->txt('confirm'), 'confirmedRemove');

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

    abstract public function removeMultipleEnabled(): bool;

    abstract public function getRemoveMultipleActionText(): string;

    abstract public function confirmedRemoveObject(): void;

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

    /**
     * @param BlockDTO[] $data
     */
    private function sortByTitle(array $data, bool $asc = true): array
    {
        uasort(
            $data,
            static fn(BlockDTO $left, BlockDTO $right): int => $asc ?
                strcmp($left->getTitle(), $right->getTitle()) :
                strcmp($right->getTitle(), $left->getTitle())
        );
        return $data;
    }
}
