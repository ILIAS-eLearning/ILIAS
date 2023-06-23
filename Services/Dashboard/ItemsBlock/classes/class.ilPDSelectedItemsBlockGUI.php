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

use ILIAS\DI\UIServices;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Card\RepositoryObject;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Component\Item\Item;

/**
* @ilCtrl_IsCalledBy ilPDSelectedItemsBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilPDSelectedItemsBlockGUI: ilCommonActionDispatcherGUI
*/
class ilPDSelectedItemsBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    /** @var bool */
    protected $new_rendering = true;

    /** @var ilRbacSystem */
    protected $rbacsystem;

    /** @var ilSetting */
    protected $settings;

    /** @var ilObjectDefinition */
    protected $obj_definition;

    /** @var string */
    public static $block_type = 'pditems';

    /** @var ilPDSelectedItemsBlockViewSettings */
    protected $viewSettings;

    /** @var ilPDSelectedItemsBlockViewGUI */
    protected $blockView;

    /** @var bool */
    protected $manage = false;

    /** @var string */
    protected $content = '';

    /** @var ilLanguage */
    protected $lng;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilObjUser */
    protected $user;

    /** @var UIServices */
    protected $ui;
    
    /** @var GlobalHttpState */
    protected $http;

    /** @var ilObjectService */
    protected $objectService;

    /** @var ilFavouritesManager */
    protected $favourites;

    /** @var ilTree */
    protected $tree;

    /** @var ilPDSelectedItemsBlockListGUIFactory */
    protected $list_factory;

    /** @var ilLogger */
    protected $logging;

    public function __construct()
    {
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
        $this->logging = $DIC->logger()->root();

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
        $this->list_factory = new ilPDSelectedItemsBlockListGUIFactory($this, $this->blockView);

        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }
    }

    protected function initViewSettings()
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_SELECTED_ITEMS
        );

        $this->viewSettings->parse();

        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function getViewSettings(): ilPDSelectedItemsBlockViewSettings
    {
        return $this->viewSettings;
    }

    /**
     * @inheritDoc
     */
    public function addToDeskObject()
    {
        $this->favourites->add($this->user->getId(), (int) $_GET["item_ref_id"]);
        ilUtil::sendSuccess($this->lng->txt("rep_added_to_favourites"), true);
        $this->returnToContext();
    }

    /**
     * @inheritDoc
     */
    protected function returnToContext()
    {
        $this->ctrl->setParameterByClass('ildashboardgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ildashboardgui', 'show');
    }

    /**
     * @inheritDoc
     */
    public function removeFromDeskObject()
    {
        $this->lng->loadLanguageModule("rep");
        $this->favourites->remove($this->user->getId(), (int) $_GET["item_ref_id"]);
        ilUtil::sendSuccess($this->lng->txt("rep_removed_from_favourites"), true);
        $this->returnToContext();
    }

    /**
     * @inheritDoc
     */
    public function getBlockType(): string
    {
        return static::$block_type;
    }

    /**
     * @inheritDoc
     */
    public static function getScreenMode()
    {
        $cmd = $_GET['cmd'];
        if ($cmd === 'post') {
            global $DIC;
            $cmd = key($DIC->http()->request()->getParsedBody()['cmd']);
        }

        switch ($cmd) {
            case 'confirmRemove':
            case 'manage':
                return IL_SCREEN_FULL;

            default:
                return IL_SCREEN_SIDE;
        }
    }

    /**
     * @inheritDoc
     */
    protected function isRepositoryObject(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getViewTitle()
    {
        return $this->blockView->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        global $DIC;

        $this->setTitle($this->getViewTitle());
        $DIC->database()->useSlave(true);
        $this->setData([['dummy']]);
        $DIC['ilHelp']->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, $this->blockView->getScreenId());
        $this->ctrl->clearParameters($this);
        $DIC->database()->useSlave(false);

        return parent::getHTML();
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd('getHTML');

        switch ($next_class) {
            case 'ilcommonactiondispatchergui':
                include_once('Services/Object/classes/class.ilCommonActionDispatcherGUI.php');
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
    }

    protected function getContent(): string
    {
        return $this->content;
    }

    protected function setContent(string $a_content)
    {
        $this->content = $a_content;
    }

    /**
     * @inheritDoc
     */
    public function fillDataSection()
    {
        if ($this->getContent() === '') {
            $this->setDataSection($this->blockView->getIntroductionHtml());
        } else {
            $this->tpl->setVariable('BLOCK_ROW', $this->getContent());
        }
    }


    /**
     * @inheritDoc
     */
    public function fillFooter(): void
    {
    }

    protected function getGroupedCommandsForView($manage = false): array
    {
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

    protected function setFooterLinks()
    {
        if ('' === $this->getContent()) {
            $this->setEnableNumInfo(false);
            return;
        }

        if ($this->blockView->isInManageMode()) {
            return;
        }

        // @todo: handle $command['active']
        $groupedCommands = $this->getGroupedCommandsForView();
        foreach ($groupedCommands as $group) {
            foreach ($group as $command) {
                $this->addBlockCommand(
                    $command['url'],
                    $command['txt'],
                    $command['asyncUrl']
                );
            }
        }
    }

    /**
     * @param ilPDSelectedItemsBlockGroup[] $grouped_items
     */
    protected function renderGroupedItems(array $grouped_items, bool $showHeader = true): string
    {
        if (0 === count($grouped_items)) {
            return '';
        }

        if ($this->viewSettings->isTilePresentation()) {
            $renderer = new ilPDObjectsTileRenderer(
                $this->blockView,
                $this->ui->factory(),
                $this->ui->renderer(),
                $this->list_factory,
                $this->user,
                $this->lng,
                $this->objectService,
                $this->ctrl
            );
            
            return $renderer->render($grouped_items, $showHeader);
        }

        $renderer = new ilPDObjectsListRenderer(
            $this->blockView,
            $this->ui->factory(),
            $this->ui->renderer(),
            $this->list_factory,
            $this->user,
            $this->lng,
            $this->objectService,
            $this->ctrl
        );

        return $renderer->render($grouped_items, $showHeader);
    }

    protected function getViewBlockHtml(): string
    {
        return $this->renderGroupedItems(
            $this->blockView->getItemGroups()
        );
    }

    public function changePDItemPresentation()
    {
        $this->viewSettings->storeActorPresentationMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        $this->initAndShow();
    }

    public function changePDItemSorting()
    {
        $this->viewSettings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        $this->initAndShow();
    }

    protected function initAndShow()
    {
        $this->initViewSettings();

        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        }

        if ($_GET["manage"]) {
            $this->ctrl->redirect($this, 'manage');
        }

        $this->returnToContext();
    }

    public function manageObject()
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
                        if (
                            $item['type'] !== 'crs' ||
                            ilParticipants::getInstance($item['ref_id'])->checkLastAdmin([$this->user->getId()])
                        ) {
                            $group->pushItem($item);
                        }
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

    protected function cancel()
    {
        $this->ctrl->returnToParent($this);
    }
    
    public function confirmRemoveObject()
    {
        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());

        $refIds = (array) ($this->http->request()->getParsedBody()['id'] ?? []);
        if (0 === count($refIds)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'manage');
        }

        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $question = $this->lng->txt('dash_info_sure_remove_from_favs');
            $cmd = 'confirmedRemove';
        } else {
            $question = $this->lng->txt('mmbr_info_delete_sure_unsubscribe');
            $cmd = 'confirmedUnsubscribe';
        }

        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
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
        
    public function confirmedRemove()
    {
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if (0 === count($refIds)) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            $this->favourites->remove($this->user->getId(), (int) $ref_id);
        }

        // #12909
        ilUtil::sendSuccess($this->lng->txt('pd_remove_multi_confirm'), true);
        $this->ctrl->redirect($this, 'manage');
    }
    
    public function confirmedUnsubscribe()
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
                            $members->NOTIFY_UNSUBSCRIBE,
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
        
                include_once './Modules/Forum/classes/class.ilForumNotification.php';
                ilForumNotification::checkForumsExistsDelete($ref_id, $this->user->getId());
            }
        }

        ilUtil::sendSuccess($this->lng->txt('mmbr_unsubscribed_from_objs'), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * @inheritDoc
     */
    protected function getListItemGroups(): array
    {
        $groupedItems = $this->blockView->getItemGroups();
        $groupedCommands = $this->getGroupedCommandsForView();
        foreach ($groupedCommands as $group) {
            foreach ($group as $command) {
                $this->addBlockCommand(
                    (string) $command['url'],
                    (string) $command['txt'],
                    (string) $command['asyncUrl']
                );
            }
        }

        $item_groups = [];
        $list_items = [];

        foreach ($groupedItems as $group) {
            $list_items = [];

            foreach ($group->getItems() as $item) {
                try {
                    $list_items[] = $this->getListItemForData($item);
                } catch (ilException $e) {
                    $this->logging->warning('Listing failed for item with ID ' . $item['obj_id'] . ': ' . $e->getMessage());
                    continue;
                }
            }
            if (count($list_items) > 0) {
                $item_groups[] = $this->ui->factory()->item()->group((string) $group->getLabel(), $list_items);
            }
        }
        
        return $item_groups;
    }

    /**
     * @inheritDoc
     */
    protected function getListItemForData(array $data): Item
    {
        $itemListGui = $this->list_factory->byType($data['type']);
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

    protected function getCardForData(array $item): Card
    {
        $itemListGui = $this->list_factory->byType($item['type']);
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

    /**
     * @inheritDoc
     */
    protected function getLegacyContent(): string
    {
        $groupedCommands = $this->getGroupedCommandsForView();
        foreach ($groupedCommands as $group) {
            foreach ($group as $command) {
                $this->addBlockCommand(
                    (string) $command['url'],
                    (string) $command['txt'],
                    (string) $command['asyncUrl']
                );
            }
        }

        $groupedItems = $this->blockView->getItemGroups();

        $subs = [];
        foreach ($groupedItems as $group) {
            $cards = [];

            foreach ($group->getItems() as $item) {
                try {
                    $cards[] = $this->getCardForData($item);
                } catch (ilException $e) {
                    $this->logging->warning('Listing failed for item with ID ' . $item['obj_id'] . ': ' . $e->getMessage());
                    continue;
                }
            }
            if (count($cards) > 0) {
                $subs[] = $this->ui->factory()->panel()->sub(
                    $group->getLabel(),
                    $this->ui->factory()->deck($cards)->withNormalCardsSize()
                );
            }
        }

        if (count($subs) > 0) {
            return $this->ui->renderer()->render($subs);
        }

        return $this->getNoItemFoundContent();
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
                    (string) $command['asyncUrl']
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

    /**
     * @ineritdoc
     */
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

    protected function getRepositoryTitle()
    {
        $nd = $this->tree->getNodeData($this->tree->getRootId());
        $title = $nd['title'];

        if ($title === 'ILIAS') {
            $title = $this->lng->txt('repository');
        }

        return $title;
    }
}
