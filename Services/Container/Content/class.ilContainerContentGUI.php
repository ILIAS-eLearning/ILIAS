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

use ILIAS\Repository\Clipboard\ClipboardManager;
use ILIAS\Container\StandardGUIRequest;
use ILIAS\Container\Content\ItemManager;
use ILIAS\Container\Content\BlockSessionRepository;

/**
 * Parent class of all container content GUIs.
 *
 * These classes are responsible for displaying the content, i.e. the
 * side column and main column and its subitems in container objects.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilContainerContentGUI
{
    public const DETAILS_DEACTIVATED = 0;
    public const DETAILS_TITLE = 1;
    public const DETAILS_ALL = 2;

    public const VIEW_MODE_LIST = 0;
    public const VIEW_MODE_TILE = 1;

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilDBInterface $db;
    protected ilRbacSystem $rbacsystem;
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    protected int $details_level = self::DETAILS_DEACTIVATED;
    protected ilContainerRenderer $renderer;
    public ilContainerGUI $container_gui;
    public ilContainer $container_obj;
    public bool $adminCommands = false;
    protected ilLogger $log;
    protected int $view_mode;
    protected array $embedded_block = [];
    protected array $items = [];
    /** @var array<string, ilObjectListGUI> */
    protected array $list_gui = [];
    protected ClipboardManager $clipboard;
    protected StandardGUIRequest $request;
    protected ItemManager $item_manager;
    protected BlockSessionRepository $block_repo;

    public function __construct(ilContainerGUI $container_gui_obj)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->db = $DIC->database();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $tpl = $DIC["tpl"];

        $this->container_gui = $container_gui_obj;
        /** @var $obj ilContainer */
        $obj = $this->container_gui->getObject();
        $this->container_obj = $obj;

        $tpl->addJavaScript("./Services/Container/js/Container.js");

        $this->log = ilLoggerFactory::getLogger('cont');

        $this->view_mode = (ilContainer::_lookupContainerSetting($this->container_obj->getId(), "list_presentation") === "tile" && !$this->container_gui->isActiveAdministrationPanel() && !$this->container_gui->isActiveOrdering())
            ? self::VIEW_MODE_TILE
            : self::VIEW_MODE_LIST;

        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();
        $this->request = $DIC
            ->container()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->item_manager = $DIC
            ->container()
            ->internal()
            ->domain()
            ->content()
            ->items($this->container_obj);
        $this->block_repo = $DIC
            ->container()
            ->internal()
            ->repo()
            ->content()
            ->block();
    }

    protected function getViewMode(): int
    {
        return $this->view_mode;
    }

    protected function getDetailsLevel(int $a_item_id): int
    {
        return $this->details_level;
    }

    public function getContainerObject(): ilContainer
    {
        return $this->container_obj;
    }

    public function getContainerGUI(): ilContainerGUI
    {
        return $this->container_gui;
    }

    /**
     * This method sets the output of the right and main column
     * in the global standard template.
     */
    public function setOutput(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        // note: we do not want to get the center html in case of
        // asynchronous calls to blocks in the right column (e.g. news)
        // see #13012
        if ($ilCtrl->getNextClass() === "ilcolumngui" &&
            $ilCtrl->isAsynch()) {
            $tpl->setRightContent($this->getRightColumnHTML());
        }

        // BEGIN ChangeEvent: record read event.
        $ilUser = $this->user;

        $obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
        ilChangeEvent::_recordReadEvent(
            $this->getContainerObject()->getType(),
            $this->getContainerObject()->getRefId(),
            $obj_id,
            $ilUser->getId()
        );
        // END ChangeEvent: record read event.

        $html = $this->getCenterColumnHTML();
        if ($html !== '') {
            $tpl->setContent($html);
        }

        // see above, all other cases (this was the old position of setRightContent,
        // maybe the position above is ok and all ifs can be removed)
        if ($ilCtrl->getNextClass() !== "ilcolumngui" ||
            !$ilCtrl->isAsynch()) {
            $tpl->setRightContent($this->getRightColumnHTML());
        }
    }

    protected function getRightColumnHTML(): string
    {
        $ilCtrl = $this->ctrl;
        $html = "";

        $ilCtrl->saveParameterByClass("ilcolumngui", "col_return");

        $obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);

        if ($column_gui::getScreenMode() === IL_SCREEN_FULL) {
            return "";
        }

        $this->getContainerGUI()->setColumnSettings($column_gui);

        if ($ilCtrl->getNextClass() === "ilcolumngui" &&
            $column_gui::getCmdSide() === IL_COL_RIGHT &&
            $column_gui::getScreenMode() === IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = "";

                // user interface plugin slot + default rendering
                $uip = new ilUIHookProcessor(
                    "Services/Container",
                    "right_column",
                    ["container_content_gui" => $this]
                );
                if (!$uip->replaced()) {
                    $html = $ilCtrl->getHTML($column_gui);
                }
                $html = $uip->getHTML($html);
            }
        }

        return $html;
    }

    protected function getCenterColumnHTML(): string
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilDB = $this->db;

        $ilCtrl->saveParameterByClass("ilcolumngui", "col_return");

        $tpl->addOnLoadCode("il.Object.setRedrawListItemUrl('" .
            $ilCtrl->getLinkTarget($this->container_gui, "redrawListItem", "", true) . "');");

        $tpl->addOnLoadCode("il.Object.setRatingUrl('" .
            $ilCtrl->getLinkTargetByClass(
                [get_class($this->container_gui), "ilcommonactiondispatchergui", "ilratinggui"],
                "saveRating",
                "",
                true,
                false
            ) . "');");

        switch ($ilCtrl->getNextClass()) {
            case "ilcolumngui":
                $this->container_gui->setSideColumnReturn();
                $html = $this->forwardToColumnGUI();
                break;

            default:
                $ilDB->useSlave(true);
                $html = $this->getMainContent();
                $ilDB->useSlave(false);
                break;
        }

        return $html;
    }

    /**
     * Get content HTML for main column, this one must be
     * overwritten in derived classes.
     */
    abstract public function getMainContent(): string;

    /**
     * Init container renderer
     */
    protected function initRenderer(): void
    {
        $sorting = ilContainerSorting::_getInstance($this->getContainerObject()->getId());

        $this->renderer = new ilContainerRenderer(
            ($this->getContainerGUI()->isActiveAdministrationPanel() && !$this->clipboard->hasEntries()),
            $this->getContainerGUI()->isMultiDownloadEnabled(),
            $this->getContainerGUI()->isActiveOrdering() && (get_class($this) !== "ilContainerObjectiveGUI") // no block sorting in objective view
            ,
            $sorting->getBlockPositions(),
            $this->container_gui,
            $this->getViewMode()
        );
    }

    /**
     * Get columngui output
     */
    private function forwardToColumnGUI(): string
    {
        $ilCtrl = $this->ctrl;
        $html = "";

        // this gets us the subitems we need in setColumnSettings()
        // todo: this should be done in ilCourseGUI->getSubItems

        $obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        if (!$ilCtrl->isAsynch()) {
            if (ilColumnGUI::getScreenMode() !== IL_SCREEN_SIDE) {
                // right column wants center
                if (ilColumnGUI::getCmdSide() === IL_COL_RIGHT) {
                    $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
                    $this->getContainerGUI()->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
                // left column wants center
                if (ilColumnGUI::getCmdSide() === IL_COL_LEFT) {
                    $column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
                    $this->getContainerGUI()->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
            } else {
                $html = $this->getMainContent();
            }
        }

        return $html;
    }

    protected function clearAdminCommandsDetermination(): void
    {
        $this->adminCommands = false;
    }

    protected function determineAdminCommands(
        int $a_ref_id,
        bool $a_admin_com_included_in_list = false
    ): void {
        $rbacsystem = $this->rbacsystem;

        if (!$this->adminCommands) {
            if (!$this->getContainerGUI()->isActiveAdministrationPanel()) {
                if ($rbacsystem->checkAccess("delete", $a_ref_id)) {
                    $this->adminCommands = true;
                }
            } else {
                $this->adminCommands = $a_admin_com_included_in_list;
            }
        }
    }

    protected function getItemGUI(array $item_data): ilObjectListGUI
    {
        // get item list gui object
        if (!isset($this->list_gui[$item_data["type"]])) {
            $item_list_gui = ilObjectListGUIFactory::_getListGUIByType($item_data["type"]);
            $item_list_gui->setContainerObject($this->getContainerGUI());
            $this->list_gui[$item_data["type"]] = &$item_list_gui;
        } else {
            $item_list_gui = &$this->list_gui[$item_data["type"]];
        }

        // unique js-ids
        $item_list_gui->setParentRefId((int) ($item_data["parent"] ?? 0));

        $item_list_gui->setDefaultCommandParameters([]);
        $item_list_gui->disableTitleLink(false);
        $item_list_gui->resetConditionTarget();

        if ($this->container_obj->isClassificationFilterActive()) {
            $item_list_gui->enablePath(
                true,
                $this->container_obj->getRefId(),
                new ilSessionClassificationPathGUI()
            );
        }

        // activate common social commands
        $item_list_gui->enableComments(true);
        $item_list_gui->enableNotes(true);
        $item_list_gui->enableTags(true);
        $item_list_gui->enableRating(true);

        // reset
        $item_list_gui->forceVisibleOnly(false);

        // container specific modifications
        $this->getContainerGUI()->modifyItemGUI($item_list_gui, $item_data);

        return $item_list_gui;
    }

    /**
     * Determine all blocks that are embedded in the container page
     */
    public function determinePageEmbeddedBlocks(
        string $a_container_page_html
    ): void {
        $type_grps = $this->getGroupedObjTypes();

        // iterate all types
        foreach ($type_grps as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($a_container_page_html, "[list-" . $type . "]"))) {
                $this->addEmbeddedBlock("type", $type);
            }
        }

        // determine item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $a_container_page_html, $found)) {
            $this->addEmbeddedBlock("itgr", (int) $found[2]);

            $html = ''; // This was never defined before
            $a_container_page_html = preg_replace('~\[' . $found[1] . '\]~i', $html, $a_container_page_html);
        }
    }

    /**
     * Add embedded block
     * @param string $block_type
     * @param string|int $block_parameter
     */
    public function addEmbeddedBlock(
        string $block_type,
        $block_parameter
    ): void {
        $this->embedded_block[$block_type][] = $block_parameter;
    }

    public function getEmbeddedBlocks(): array
    {
        return $this->embedded_block;
    }

    public function renderPageEmbeddedBlocks(): void
    {
        // item groups
        if (isset($this->embedded_block["itgr"]) && is_array($this->embedded_block["itgr"])) {
            $item_groups = [];
            if (isset($this->items["itgr"]) && is_array($this->items["itgr"])) {
                foreach ($this->items["itgr"] as $ig) {
                    $item_groups[$ig["ref_id"]] = $ig;
                }
            }

            foreach ($this->embedded_block["itgr"] as $ref_id) {
                if (isset($item_groups[$ref_id])) {
                    $this->renderItemGroup($item_groups[$ref_id]);
                }
            }
        }

        // type specific blocks
        if (isset($this->embedded_block["type"]) && is_array($this->embedded_block["type"])) {
            foreach ($this->embedded_block["type"] as $type) {
                if (isset($this->items[$type]) && is_array($this->items[$type]) && $this->renderer->addTypeBlock($type)) {
                    // :TODO: obsolete?
                    if ($type === 'sess') {
                        $this->items['sess'] = ilArrayUtil::sortArray($this->items['sess'], 'start', 'ASC', true, true);
                    }

                    $position = 1;

                    foreach ($this->items[$type] as $item_data) {
                        if (!$this->renderer->hasItem($item_data["child"])) {
                            $html = $this->renderItem($item_data, $position++);
                            if ($html != "") {
                                $this->renderer->addItemToBlock($type, $item_data["type"], $item_data["child"], $html);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Render an item
     * @return \ILIAS\UI\Component\Card\RepositoryObject|string|null
     */
    public function renderItem(
        array $a_item_data,
        int $a_position = 0,
        bool $a_force_icon = false,
        string $a_pos_prefix = "",
        string $item_group_list_presentation = ""
    ) {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;

        // Pass type, obj_id and tree to checkAccess method to improve performance
        if (!$ilAccess->checkAccess('visible', '', $a_item_data['ref_id'], $a_item_data['type'], $a_item_data['obj_id'], $a_item_data['tree'])) {
            return '';
        }

        $view_mode = $this->getViewMode();
        if ($item_group_list_presentation != "") {
            $view_mode = ($item_group_list_presentation === "tile")
                ? self::VIEW_MODE_TILE
                : self::VIEW_MODE_LIST;
        }

        if ($view_mode === self::VIEW_MODE_TILE) {
            return $this->renderCard($a_item_data, $a_position, $a_force_icon, $a_pos_prefix);
        }

        $item_list_gui = $this->getItemGUI($a_item_data);
        if ($a_item_data["type"] === "sess" ||
            $a_force_icon ||
            $ilSetting->get("icon_position_in_lists") === "item_rows") {
            $item_list_gui->enableIcon(true);
        }

        if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$this->clipboard->hasEntries()) {
            $item_list_gui->enableCheckbox(true);
        } elseif ($this->getContainerGUI()->isMultiDownloadEnabled()) {
            // display multi download checkboxes
            $item_list_gui->enableDownloadCheckbox((int) $a_item_data["ref_id"]);
        }

        if ($this->getContainerGUI()->isActiveItemOrdering() && ($a_item_data['type'] !== 'sess' || get_class($this) !== 'ilContainerSessionsContentGUI')) {
            $item_list_gui->setPositionInputField(
                $a_pos_prefix . "[" . $a_item_data["ref_id"] . "]",
                sprintf('%d', $a_position * 10)
            );
        }

        if ($a_item_data['type'] === 'sess' && get_class($this) !== 'ilContainerObjectiveGUI') {
            switch ($this->getDetailsLevel($a_item_data['obj_id'])) {
                case self::DETAILS_TITLE:
                    $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_MINIMAL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->setExpanded(false);
                    $item_list_gui->enableDescription(false);
                    $item_list_gui->enableProperties(true);
                    break;

                case self::DETAILS_ALL:
                    $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->setExpanded(true);
                    $item_list_gui->enableDescription(true);
                    $item_list_gui->enableProperties(true);
                    break;

                default:
                    $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->enableDescription(true);
                    $item_list_gui->enableProperties(true);
                    break;
            }
        }

        if (method_exists($this, "addItemDetails")) {
            $this->addItemDetails($item_list_gui, $a_item_data);
        }

        // show subitems
        if ($a_item_data['type'] === 'sess' && (
            $this->getDetailsLevel($a_item_data['obj_id']) !== self::DETAILS_TITLE ||
            $this->getContainerGUI()->isActiveAdministrationPanel() ||
            $this->getContainerGUI()->isActiveItemOrdering()
        )) {
            $pos = 1;

            $items = ilObjectActivation::getItemsByEvent($a_item_data['obj_id']);
            $items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('sess', $a_item_data['obj_id'], $items);
            $items = ilContainer::getCompleteDescriptions($items);

            $item_readable = $ilAccess->checkAccess('read', '', $a_item_data['ref_id']);

            foreach ($items as $item) {
                // TODO: this should be removed and be handled by if(strlen($sub_item_html))
                // 	see mantis: 0003944
                if (!$ilAccess->checkAccess('visible', '', $item['ref_id'])) {
                    continue;
                }

                $item_list_gui2 = $this->getItemGUI($item);
                $item_list_gui2->enableIcon(true);
                $item_list_gui2->enableItemDetailLinks(false);

                // unique js-ids
                $item_list_gui2->setParentRefId((int) ($a_item_data['ref_id'] ?? 0));

                // @see mantis 10488
                if (!$item_readable && !$ilAccess->checkAccess('write', '', $item['ref_id'])) {
                    $item_list_gui2->forceVisibleOnly(true);
                }

                if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$this->clipboard->hasEntries()) {
                    $item_list_gui2->enableCheckbox(true);
                } elseif ($this->getContainerGUI()->isMultiDownloadEnabled()) {
                    // display multi download checkbox
                    $item_list_gui2->enableDownloadCheckbox((int) $item['ref_id']);
                }

                if ($this->getContainerGUI()->isActiveItemOrdering()) {
                    $item_list_gui2->setPositionInputField(
                        "[sess][" . $a_item_data['obj_id'] . "][" . $item["ref_id"] . "]",
                        sprintf('%d', $pos * 10)
                    );
                    $pos++;
                }

                // #10611
                ilObjectActivation::addListGUIActivationProperty($item_list_gui2, $item);

                $sub_item_html = $item_list_gui2->getListItemHTML(
                    $item['ref_id'],
                    $item['obj_id'],
                    $item['title'],
                    $item['description']
                );

                $this->determineAdminCommands($item["ref_id"], $item_list_gui2->adminCommandsIncluded());
                if ($sub_item_html !== '') {
                    $item_list_gui->addSubItemHTML($sub_item_html);
                }
            }
        }

        $asynch = false;
        $asynch_url = '';
        if ($ilSetting->get("item_cmd_asynch")) {
            $asynch = true;
            $ilCtrl->setParameter($this->container_gui, "cmdrefid", $a_item_data['ref_id']);
            $asynch_url = $ilCtrl->getLinkTarget(
                $this->container_gui,
                "getAsynchItemList",
                "",
                true,
                false
            );
            $ilCtrl->setParameter($this->container_gui, "cmdrefid", "");
        }

        ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_item_data);

        $html = $item_list_gui->getListItemHTML(
            $a_item_data['ref_id'],
            $a_item_data['obj_id'],
            $a_item_data['title'],
            (string) $a_item_data['description'],
            $asynch,
            false,
            $asynch_url
        );
        $this->determineAdminCommands(
            $a_item_data["ref_id"],
            $item_list_gui->adminCommandsIncluded()
        );


        return $html;
    }

    public function renderCard(
        array $a_item_data,
        int $a_position = 0,
        bool $a_force_icon = false,
        string $a_pos_prefix = ""
    ): ?\ILIAS\UI\Component\Card\RepositoryObject {
        $item_list_gui = $this->getItemGUI($a_item_data);
        $item_list_gui->setAjaxHash(ilCommonActionDispatcherGUI::buildAjaxHash(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $a_item_data['ref_id'],
            $a_item_data['type'],
            (int) $a_item_data['obj_id']
        ));
        $item_list_gui->initItem(
            $a_item_data['ref_id'],
            $a_item_data['obj_id'],
            $a_item_data['type'],
            $a_item_data['title'],
            $a_item_data['description']
        );

        // actions
        $item_list_gui->insertCommands();
        return $item_list_gui->getAsCard(
            $a_item_data['ref_id'],
            (int) $a_item_data['obj_id'],
            (string) $a_item_data['type'],
            (string) $a_item_data['title'],
            (string) $a_item_data['description']
        );
    }

    /**
     * Insert blocks into container page
     */
    public function insertPageEmbeddedBlocks(string $a_output_html): string
    {
        $this->determinePageEmbeddedBlocks($a_output_html);
        $this->renderPageEmbeddedBlocks();

        // iterate all types
        foreach ($this->getGroupedObjTypes() as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($a_output_html, "[list-" . $type . "]"))) {
                $a_output_html = preg_replace(
                    '~\[list-' . $type . '\]~i',
                    $this->renderer->renderSingleTypeBlock($type),
                    $a_output_html
                );
            }
        }

        // insert all item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $a_output_html, $found)) {
            $itgr_ref_id = (int) $found[2];

            $a_output_html = preg_replace(
                '~\[' . $found[1] . '\]~i',
                $this->renderer->renderSingleCustomBlock($itgr_ref_id),
                $a_output_html
            );
        }

        return $a_output_html;
    }

    /**
     * Render single block
     */
    public function getSingleTypeBlockAsynch(
        string $type
    ): string {
        $this->initRenderer();
        // get all sub items
        $this->items = $this->getContainerObject()->getSubItems(
            $this->getContainerGUI()->isActiveAdministrationPanel()
        );


        $ref_ids = $this->request->getAlreadyRenderedRefIds();

        // iterate all types
        if (is_array($this->items[$type]) &&
            $this->renderer->addTypeBlock($type)) {
            //$this->renderer->setBlockPosition($type, ++$pos);

            $position = 1;
            $counter = 1;
            foreach ($this->items[$type] as $item_data) {
                $item_ref_id = $item_data["child"];

                if (in_array($item_ref_id, $ref_ids)) {
                    continue;
                }

                if ($this->block_limit > 0 && $counter == $this->block_limit + 1) {
                    if ($counter == $this->block_limit + 1) {
                        // render more button
                        $this->renderer->addShowMoreButton($type);
                    }
                    continue;
                }

                if (!$this->renderer->hasItem($item_ref_id)) {
                    $html = $this->renderItem($item_data, $position++);
                    if ($html != "") {
                        $counter++;
                        $this->renderer->addItemToBlock($type, $item_data["type"], $item_ref_id, $html);
                    }
                }
            }
        }

        return $this->renderer->renderSingleTypeBlock($type);
    }

    /**
     * Get grouped repository object types.
     */
    public function getGroupedObjTypes(): array
    {
        $objDefinition = $this->obj_definition;

        if (empty($this->type_grps)) {
            $this->type_grps =
                $objDefinition::getGroupedRepositoryObjectTypes($this->getContainerObject()->getType());
        }
        return $this->type_grps;
    }

    public function getIntroduction(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("rep");

        $tpl = new ilTemplate("tpl.rep_intro.html", true, true, "Services/Repository");
        $tpl->setVariable("IMG_REP_LARGE", ilObject::_getIcon(0, "big", "root"));
        $tpl->setVariable("TXT_WELCOME", $lng->txt("rep_intro"));
        $tpl->setVariable("TXT_INTRO_1", $lng->txt("rep_intro1"));
        $tpl->setVariable("TXT_INTRO_2", $lng->txt("rep_intro2"));
        $tpl->setVariable("TXT_INTRO_3", sprintf($lng->txt("rep_intro3"), $lng->txt("add")));
        $tpl->setVariable("TXT_INTRO_4", sprintf($lng->txt("rep_intro4"), $lng->txt("cat_add")));
        $tpl->setVariable("TXT_INTRO_5", $lng->txt("rep_intro5"));
        $tpl->setVariable("TXT_INTRO_6", $lng->txt("rep_intro6"));

        return $tpl->get();
    }

    public function getItemGroupsHTML(int $a_pos = 0): int
    {
        if (isset($this->items["itgr"]) && is_array($this->items["itgr"])) {
            foreach ($this->items["itgr"] as $itgr) {
                if (!$this->renderer->hasCustomBlock($itgr["child"])) {
                    $this->renderItemGroup($itgr);

                    $this->renderer->setBlockPosition($itgr["ref_id"], ++$a_pos);
                }
            }
        }
        return $a_pos;
    }

    public function renderItemGroup(array $a_itgr): void
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;

        // #16493
        $perm_ok = ($ilAccess->checkAccess("visible", "", $a_itgr['ref_id']) &&
             $ilAccess->checkAccess("read", "", $a_itgr['ref_id']));

        $items = ilObjectActivation::getItemsByItemGroup($a_itgr['ref_id']);

        // get all valid ids (this is filtered)
        $all_ids = array_map(static function (array $i): int {
            return (int) $i["child"];
        }, $this->items["_all"]);

        // remove filtered items
        $items = array_filter($items, static function (array $i) use ($all_ids): bool {
            return in_array($i["ref_id"], $all_ids);
        });

        // if no permission is given, set the items to "rendered" but
        // do not display the whole block
        if (!$perm_ok) {
            foreach ($items as $item) {
                $this->renderer->hideItem($item["child"]);
            }
            return;
        }

        $item_list_gui = $this->getItemGUI($a_itgr);
        $item_list_gui->enableNotes(false);
        $item_list_gui->enableTags(false);
        $item_list_gui->enableComments(false);
        $item_list_gui->enableTimings(false);
        $item_list_gui->getListItemHTML(
            $a_itgr["ref_id"],
            $a_itgr["obj_id"],
            $a_itgr["title"],
            $a_itgr["description"]
        );
        $commands_html = $item_list_gui->getCommandsHTML();

        // determine behaviour
        $item_group = new ilObjItemGroup($a_itgr["ref_id"]);
        $beh = $item_group->getBehaviour();
        $stored_val = $this->block_repo->getProperty(
            "itgr_" . $a_itgr["ref_id"],
            $ilUser->getId(),
            "opened"
        );
        if ($stored_val !== "" && $beh !== ilItemGroupBehaviour::ALWAYS_OPEN) {
            $beh = ($stored_val === "1")
                ? ilItemGroupBehaviour::EXPANDABLE_OPEN
                : ilItemGroupBehaviour::EXPANDABLE_CLOSED;
        }

        $data = [
            "behaviour" => $beh,
            "store-url" => "./ilias.php?baseClass=ilcontainerblockpropertiesstoragegui&cmd=store" .
                "&cont_block_id=itgr_" . $a_itgr['ref_id']
        ];
        if (ilObjItemGroup::lookupHideTitle($a_itgr["obj_id"]) &&
            !$this->getContainerGUI()->isActiveAdministrationPanel()) {
            $this->renderer->addCustomBlock($a_itgr["ref_id"], "", $commands_html, $data);
        } else {
            $this->renderer->addCustomBlock($a_itgr["ref_id"], $a_itgr["title"], $commands_html, $data);
        }


        // render item group sub items

        $items = ilContainerSorting::_getInstance(
            $this->getContainerObject()->getId()
        )->sortSubItems('itgr', $a_itgr['obj_id'], $items);

        // #18285
        $items = ilContainer::getCompleteDescriptions($items);

        $position = 1;
        foreach ($items as $item) {
            // we are NOT using hasItem() here, because item might be in multiple item groups
            $html2 = $this->renderItem($item, $position++, false, "[itgr][" . $a_itgr['obj_id'] . "]", $item_group->getListPresentation());
            if ($html2 != "") {
                // :TODO: show it multiple times?
                $this->renderer->addItemToBlock($a_itgr["ref_id"], $item["type"], $item["child"], $html2, true);
            }
        }
    }

    protected function handleSessionExpand(): void
    {
        $expand = $this->request->getExpand();
        if ($expand > 0) {
            $this->item_manager->setExpanded(abs($expand), self::DETAILS_ALL);
        } elseif ($expand < 0) {
            $this->item_manager->setExpanded(abs($expand), self::DETAILS_TITLE);
        }
    }
}
