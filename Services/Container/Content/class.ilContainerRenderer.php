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

/**
 * Class ilContainerRenderer
 *
 * @author Jörg Lützenkirchen  <luetzenkirchen@leifos.com>
 */
class ilContainerRenderer
{
    protected const UNIQUE_SEPARATOR = "-";
    protected ilObjUser $user;
    protected \ILIAS\Containter\Content\ObjectiveRenderer $objective_renderer;
    protected \ILIAS\Containter\Content\ItemRenderer $item_renderer;
    protected \ILIAS\Container\Content\ItemPresentationManager $item_presentation;

    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    protected ilContainerGUI $container_gui;

    // switches
    protected bool $enable_manage_select_all;
    protected bool $enable_multi_download;
    protected bool $active_block_ordering;

    // properties
    protected array $type_blocks = [];
    protected array $custom_blocks = [];
    protected array $items = [];
    protected array $hidden_items = [];
    protected array $block_items = [];
    protected array $details = [];
    protected array $item_ids = [];

    // block (unique) ids
    protected array $rendered_blocks = [];
    protected int $bl_cnt = 0;

    // ordering
    protected array $block_pos = [];
    protected array $block_custom_pos = [];
    protected int $order_cnt = 0;

    protected array $show_more = [];
    protected int $view_mode;
    protected \ILIAS\DI\UIServices $ui;
    protected ilCtrl $ctrl;
    protected ?Closure $block_prefix_closure = null;
    protected ?Closure $block_postfix_closure = null;
    protected ?Closure $item_hidden_closure = null;
    protected \ILIAS\Container\Content\BlockSessionRepository $block_repo;

    public function __construct(
        \ILIAS\Container\Content\ItemPresentationManager $item_presentation,
        bool $a_enable_manage_select_all = false,
        bool $a_enable_multi_download = false,
        bool $a_active_block_ordering = false,
        array $a_block_custom_positions = [],
        ?ilContainerGUI $container_gui_obj = null,
        int $a_view_mode = ilContainerContentGUI::VIEW_MODE_LIST
    ) {
        global $DIC;

        $this->item_presentation = $item_presentation;
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->ui = $DIC->ui();
        $this->obj_definition = $DIC["objDefinition"];
        $this->enable_manage_select_all = $a_enable_manage_select_all;
        $this->enable_multi_download = $a_enable_multi_download;
        $this->active_block_ordering = $a_active_block_ordering;
        $this->block_custom_pos = $a_block_custom_positions;
        $this->view_mode = $a_view_mode;
        /** @var $obj ilContainerGUI */
        $obj = $container_gui_obj;
        $this->container_gui = $obj;
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->item_renderer = $DIC->container()
            ->internal()
            ->gui()
            ->content()
            ->itemRenderer(
                $this->container_gui,
                $a_view_mode
            );
        $this->objective_renderer = $DIC->container()
            ->internal()
            ->gui()
            ->content()
            ->objectiveRenderer(
                $this->container_gui,
                $a_view_mode,
                clone $this
            );
        $this->block_repo = $DIC
            ->container()
            ->internal()
            ->repo()
            ->content()
            ->block();
    }

    public function setBlockPrefixClosure(Closure $f): void
    {
        $this->block_prefix_closure = $f;
    }

    public function setBlockPostfixClosure(Closure $f): void
    {
        $this->block_postfix_closure = $f;
    }

    public function setItemHiddenClosure(Closure $f): void
    {
        $this->item_hidden_closure = $f;
    }

    protected function getViewMode(): int
    {
        return $this->view_mode;
    }

    //
    // blocks
    //

    public function addTypeBlock(
        string $a_type,
        string $a_prefix = null,
        string $a_postfix = null
    ): bool {
        if ($a_type !== "itgr" &&
            !$this->hasTypeBlock($a_type)) {
            $this->type_blocks[$a_type] = [
                "prefix" => $a_prefix
                ,"postfix" => $a_postfix
            ];
            return true;
        }
        return false;
    }

    public function hasTypeBlock(string $a_type): bool
    {
        return array_key_exists($a_type, $this->type_blocks);
    }

    /**
     * Add custom block
     * @param mixed $a_id
     */
    public function addCustomBlock(
        $a_id,
        string $a_caption,
        string $a_actions = null,
        array $a_data = []
    ): bool {
        if (!$this->hasCustomBlock($a_id)) {
            $this->custom_blocks[$a_id] = [
                "caption" => $a_caption
                ,"actions" => $a_actions
                ,"data" => $a_data
            ];
            return true;
        }
        return false;
    }

    /**
     * Custom block already exists?
     * @param mixed $a_id
     */
    public function hasCustomBlock($a_id): bool
    {
        return array_key_exists($a_id, $this->custom_blocks);
    }

    /**
     * Any block with id exists?
     * @param mixed $a_id
     */
    public function isValidBlock($a_id): bool
    {
        return ($this->hasTypeBlock($a_id) ||
            $this->hasCustomBlock($a_id));
    }


    //
    // items
    //

    /**
     * Mark item id as used, but do not render
     *
     * @param mixed $a_id
     */
    public function hideItem($a_id): void
    {
        // see hasItem();
        $this->hidden_items[$a_id] = true;

        // #16629 - do not remove hidden items from other blocks
        // $this->removeItem($a_id);
    }

    /**
     * Remove item (from any block)
     * @param mixed $a_id
     */
    public function removeItem($a_id): void
    {
        if (!$this->hasItem($a_id)) {
            return;
        }

        unset($this->item_ids[$a_id], $this->hidden_items[$a_id]);

        foreach (array_keys($this->items) as $item_id) {
            $parts = explode(self::UNIQUE_SEPARATOR, $item_id);
            if (array_pop($parts) == $a_id) {
                unset($this->items[$item_id]);
            }
        }

        foreach ($this->block_items as $block_id => $items) {
            foreach ($items as $idx => $item_id) {
                $parts = explode(self::UNIQUE_SEPARATOR, $item_id);
                if (array_pop($parts) == $a_id) {
                    unset($this->block_items[$block_id][$idx]);
                    if (!count($this->block_items[$block_id])) {
                        unset($this->block_items[$block_id]);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Item with id exists?
     *
     * @param mixed $a_id
     */
    public function hasItem($a_id): bool
    {
        return (array_key_exists($a_id, $this->item_ids) ||
            array_key_exists($a_id, $this->hidden_items));
    }

    /**
     * Add item to existing block
     *
     * @param mixed $a_block_id
     * @param mixed $a_item_id
     * @param mixed $a_item_html
     */
    public function addItemToBlock(
        $a_block_id,
        string $a_item_type,
        $a_item_id,
        $a_item_html,
        bool $a_force = false
    ): bool {
        if ($a_item_type !== "itgr" &&
            $this->isValidBlock($a_block_id) &&
            (!$this->hasItem($a_item_id) || $a_force)) {
            if (is_string($a_item_html) && trim($a_item_html) === "") {
                return false;
            }
            if (!$a_item_html) {
                return false;
            }


            // #16563 - item_id (== ref_id) is NOT unique, adding parent block id
            $uniq_id = $a_block_id . self::UNIQUE_SEPARATOR . $a_item_id;

            $this->items[$uniq_id] = [
                "type" => $a_item_type
                ,"html" => $a_item_html
            ];

            // #18326
            $this->addItemId($a_item_id);
            $this->block_items[$a_block_id][] = $uniq_id;
            return true;
        }
        return false;
    }

    /**
     * @param mixed $a_item_id
     */
    public function addItemId($a_item_id): void
    {
        $this->item_ids[$a_item_id] = true;
    }

    /**
     * Add show more button to a block
     * @param mixed $a_block_id
     */
    public function addShowMoreButton($a_block_id): void
    {
        $this->show_more[] = $a_block_id;
    }

    public function addDetailsLevel(
        int $a_level,
        string $a_url,
        bool $a_active = false
    ): void {
        $this->details[$a_level] = [
            "url" => $a_url
            ,"active" => $a_active
        ];
    }

    public function resetDetails(): void
    {
        $this->details = [];
    }


    //
    // render
    //

    /**
     * @param mixed $a_block_id
     */
    public function setBlockPosition(
        $a_block_id,
        int $a_pos
    ): void {
        if ($this->isValidBlock($a_block_id)) {
            $this->block_pos[$a_block_id] = $a_pos;
        }
    }

    public function getHTML(): string
    {
        $valid = false;

        $block_tpl = $this->initBlockTemplate();

        foreach ($this->processBlockPositions() as $block_id) {
            if (array_key_exists($block_id, $this->custom_blocks) && $this->renderHelperCustomBlock(
                $block_tpl,
                $block_id
            )) {
                $this->addSeparatorRow($block_tpl);
                $valid = true;
            }
            if (array_key_exists($block_id, $this->type_blocks) && $this->renderHelperTypeBlock(
                $block_tpl,
                $block_id
            )) {
                $this->addSeparatorRow($block_tpl);
                $valid = true;
            }
        }

        if ($valid) {
            $this->renderDetails($block_tpl);

            return $block_tpl->get();
        }
        return "";
    }

    public function renderSingleTypeBlock(string $a_type): string
    {
        $block_tpl = $this->initBlockTemplate();

        if ($this->renderHelperTypeBlock($block_tpl, $a_type, true)) {
            return $block_tpl->get();
        }
        return "";
    }

    /**
     * @param mixed $a_id
     */
    public function renderSingleCustomBlock($a_id): string
    {
        $block_tpl = $this->initBlockTemplate();

        if ($this->renderHelperCustomBlock($block_tpl, $a_id, true)) {
            return $block_tpl->get();
        }
        return "";
    }


    //
    // render (helper)
    //

    protected function processBlockPositions(): array
    {
        // manual order
        if (is_array($this->block_custom_pos) && count($this->block_custom_pos)) {
            $tmp = $this->block_pos;
            $this->block_pos = [];
            foreach ($this->block_custom_pos as $idx => $block_id) {
                if ($this->isValidBlock($block_id)) {
                    $this->block_pos[$block_id] = $idx;
                }
            }

            // at least some manual are valid
            if (count($this->block_pos)) {
                // append missing blocks from default order
                $last = max($this->block_pos);
                foreach (array_keys($tmp) as $block_id) {
                    if (!array_key_exists($block_id, $this->block_pos)) {
                        $this->block_pos[$block_id] = ++$last;
                    }
                }
            }
            // all manual invalid, use default
            else {
                $this->block_pos = $tmp;
            }
        }

        // add missing blocks to order
        $last = count($this->block_pos)
            ? max($this->block_pos)
            : 0;
        foreach (array_keys($this->custom_blocks) as $block_id) {
            if (!array_key_exists($block_id, $this->block_pos)) {
                $this->block_pos[$block_id] = ++$last;
            }
        }
        foreach (array_keys($this->type_blocks) as $block_id) {
            if (!array_key_exists($block_id, $this->block_pos)) {
                $this->block_pos[$block_id] = ++$last;
            }
        }

        asort($this->block_pos);
        return array_keys($this->block_pos);
    }

    /**
     * @param mixed $a_block_id
     */
    protected function renderHelperCustomBlock(
        ilTemplate $a_block_tpl,
        $a_block_id,
        bool $a_is_single = false,
        bool $is_exhausted = false
    ): bool {
        if ($this->hasCustomBlock($a_block_id)) {
            return $this->renderHelperGeneric($a_block_tpl, $a_block_id, $this->custom_blocks[$a_block_id], $a_is_single, $is_exhausted);
        }
        return false;
    }

    protected function renderHelperTypeBlock(
        ilTemplate $a_block_tpl,
        string $a_type,
        bool $a_is_single = false,
        bool $is_exhausted = false
    ): bool {
        if ($this->hasTypeBlock($a_type)) {
            $block = $this->type_blocks[$a_type];
            $block["type"] = $a_type;
            return $this->renderHelperGeneric($a_block_tpl, $a_type, $block, $a_is_single, $is_exhausted);
        }
        return false;
    }

    protected function getViewModeOfItemGroup(int $ref_id): int
    {
        $item_group = new ilObjItemGroup($ref_id);
        $view_mode = ilContainerContentGUI::VIEW_MODE_LIST;
        if ($item_group->getListPresentation() !== "") {
            $view_mode = ($item_group->getListPresentation() === "tile")
                ? ilContainerContentGUI::VIEW_MODE_TILE
                : ilContainerContentGUI::VIEW_MODE_LIST;
        }
        return $view_mode;
    }
    /**
     * @param mixed $a_block_id
     */
    protected function renderHelperGeneric(
        ilTemplate $a_block_tpl,
        $a_block_id,
        array $a_block,
        bool $a_is_single = false,
        bool $is_exhausted = false
    ): bool {
        $ctrl = $this->ctrl;
        if (!in_array($a_block_id, $this->rendered_blocks)) {
            $this->rendered_blocks[] = $a_block_id;
            $block_types = [];
            if (isset($this->block_items[$a_block_id]) && is_array($this->block_items[$a_block_id])) {
                foreach ($this->block_items[$a_block_id] as $item_id) {
                    if (isset($this->items[$item_id]["type"])) {
                        $block_types[] = $this->items[$item_id]["type"];
                    }
                }
            }

            // determine view mode and tile size
            $tile_size = ilContainer::TILE_SMALL;
            $view_mode = $this->getViewMode();
            if ($view_mode === ilContainerContentGUI::VIEW_MODE_TILE) {
                $tile_size = ilContainer::_lookupContainerSetting($this->container_gui->getObject()->getId(), "tile_size");
            }
            if (is_numeric($a_block_id)) {
                $item_group = new ilObjItemGroup($a_block_id);
                if ($item_group->getListPresentation() !== "") {
                    $view_mode = ($item_group->getListPresentation() === "tile")
                        ? ilContainerContentGUI::VIEW_MODE_TILE
                        : ilContainerContentGUI::VIEW_MODE_LIST;
                    $tile_size = $item_group->getTileSize();
                }
            }


            // #14610 - manage empty item groups
            if ((isset($this->block_items[$a_block_id]) && is_array($this->block_items[$a_block_id])) ||
                is_numeric($a_block_id)) {
                $cards = [];

                $order_id = (!$a_is_single && $this->active_block_ordering)
                    ? $a_block_id
                    : "";
                $this->addHeaderRow(
                    $a_block_tpl,
                    $a_block["type"] ?? '',
                    $a_block["caption"] ?? '',
                    array_unique($block_types),
                    $a_block["actions"] ?? '',
                    $order_id,
                    $a_block["data"] ?? []
                );

                if ($view_mode === ilContainerContentGUI::VIEW_MODE_LIST) {
                    if (isset($a_block["prefix"]) && $a_block["prefix"]) {
                        $this->addStandardRow($a_block_tpl, $a_block["prefix"]);
                    }
                }

                if (isset($this->block_items[$a_block_id])) {
                    foreach ($this->block_items[$a_block_id] as $item_id) {
                        if ($view_mode === ilContainerContentGUI::VIEW_MODE_LIST) {
                            $this->addStandardRow($a_block_tpl, $this->items[$item_id]["html"], (int) $item_id);
                        } else {
                            $cards[] = $this->items[$item_id]["html"];
                        }
                    }
                }

                if ($view_mode === ilContainerContentGUI::VIEW_MODE_LIST) {
                    if (isset($a_block["postfix"]) && $a_block["postfix"]) {
                        $this->addStandardRow($a_block_tpl, $a_block["postfix"]);
                    }
                }

                if ($view_mode === ilContainerContentGUI::VIEW_MODE_TILE) {
                    $f = $this->ui->factory();
                    $renderer = $this->ui->renderer();

                    //Create a deck with large cards
                    switch ($tile_size) {
                        case ilContainer::TILE_SMALL:
                            $deck = $f->deck($cards)->withSmallCardsSize();
                            break;

                        case ilContainer::TILE_LARGE:
                            $deck = $f->deck($cards)->withLargeCardsSize();
                            break;

                        case ilContainer::TILE_EXTRA_LARGE:
                            $deck = $f->deck($cards)->withExtraLargeCardsSize();
                            break;

                        case ilContainer::TILE_FULL:
                            $deck = $f->deck($cards)->withFullSizedCardsSize();
                            break;

                        default:
                            $deck = $f->deck($cards)->withNormalCardsSize();
                            break;
                    }

                    $html = $renderer->render($deck);
                    $a_block_tpl->setCurrentBlock("tile_rows");
                    $a_block_tpl->setVariable("TILE_ROWS", $html);
                    $a_block_tpl->parseCurrentBlock();
                }

                // show more
                if ($is_exhausted) {
                    $a_block_tpl->setCurrentBlock("show_more");

                    $ctrl->setParameter($this->container_gui, "type", $a_block_id);
                    $url = $ctrl->getLinkTarget($this->container_gui, "renderBlockAsynch", "", true);
                    $ctrl->setParameter($this->container_gui, "type", "");

                    $f = $this->ui->factory();
                    $renderer = $this->ui->renderer();
                    $button = $f->button()->standard($this->lng->txt("cont_show_more"), "")
                        ->withLoadingAnimationOnClick(true)
                        ->withOnLoadCode(function ($id) use ($a_block_id, $url) {
                            return "il.Container.initShowMore('$id', '$a_block_id', '" . $url . "');";
                        });
                    if ($ctrl->isAsynch()) {
                        $a_block_tpl->setVariable("SHOW_MORE_BUTTON", $renderer->renderAsync($button));
                    } else {
                        $a_block_tpl->setVariable("SHOW_MORE_BUTTON", $renderer->render($button));
                    }
                    $a_block_tpl->parseCurrentBlock();
                    $a_block_tpl->setCurrentBlock("show_more");
                    $a_block_tpl->parseCurrentBlock();
                }

                return true;
            }
        }

        return false;
    }

    protected function initBlockTemplate(): ilTemplate
    {
        return new ilTemplate("tpl.container_list_block.html", true, true, "Services/Container");
    }

    /**
     * Render block header
     * @param string     $a_order_id item group id or type, e.g. "crs"
     * @throws ilTemplateException
     */
    protected function addHeaderRow(
        ilTemplate $a_tpl,
        string $a_type = "",
        string $a_text = "",
        array $a_types_in_block = null,
        string $a_commands_html = "",
        string $a_order_id = "",
        array $a_data = []
    ): void {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $objDefinition = $this->obj_definition;

        $a_tpl->setVariable("CB_ID", ' id="bl_cntr_' . (++$this->bl_cnt) . '"');

        if ($this->enable_manage_select_all) {
            $this->renderSelectAllBlock($a_tpl);
        } elseif ($this->enable_multi_download) {
            if ($a_type) {
                $a_types_in_block = [$a_type];
            }
            foreach ($a_types_in_block as $type) {
                if (in_array($type, $this->getDownloadableTypes(), true)) {
                    $this->renderSelectAllBlock($a_tpl);
                    break;
                }
            }
        }

        if ($a_text === "" && $a_type !== "") {
            if (!$objDefinition->isPlugin($a_type)) {
                $title = $lng->txt("objs_" . $a_type);
            } else {
                $pl = ilObjectPlugin::getPluginObjectByType($a_type);
                $title = $pl->txt("objs_" . $a_type);
            }
        } else {
            $title = $a_text;
        }

        if (is_array($a_data)) {
            foreach ($a_data as $k => $v) {
                $a_tpl->setCurrentBlock("cb_data");
                $a_tpl->setVariable("DATA_KEY", $k);
                $a_tpl->setVariable("DATA_VALUE", $v);
                $a_tpl->parseCurrentBlock();

                if ($k === "behaviour" && $v == ilItemGroupBehaviour::EXPANDABLE_CLOSED) {
                    $a_tpl->touchBlock("container_items_hide");
                }
            }
        }

        if ($a_type !== "" && $ilSetting->get("icon_position_in_lists") !== "item_rows") {
            $icon = ilUtil::getImagePath("standard/icon_" . $a_type . ".svg");

            $a_tpl->setCurrentBlock("container_header_row_image");
            $a_tpl->setVariable("HEADER_IMG", $icon);
            $a_tpl->setVariable("HEADER_ALT", $title);
        } else {
            $a_tpl->setCurrentBlock("container_header_row");
        }

        if ($a_order_id !== "") {
            $a_tpl->setVariable("BLOCK_HEADER_ORDER_NAME", "position[blocks][" . $a_order_id . "]");
            $a_tpl->setVariable("BLOCK_HEADER_ORDER_NUM", (++$this->order_cnt) * 10);
        }

        $a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
        $a_tpl->setVariable("CHR_COMMANDS", $a_commands_html);
        $a_tpl->parseCurrentBlock();
    }

    protected function addStandardRow(
        ilTemplate $a_tpl,
        string $a_html,
        int $a_ref_id = 0
    ): void {
        if ($a_ref_id > 0) {
            $a_tpl->setCurrentBlock("row");
            $a_tpl->setVariable("ROW_ID", 'id="item_row_' . $a_ref_id . '"');
            $a_tpl->parseCurrentBlock();
        } else {
            $a_tpl->touchBlock("row");
        }

        $a_tpl->setCurrentBlock("container_standard_row");
        $a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
        $a_tpl->parseCurrentBlock();

        $a_tpl->touchBlock("container_row");
    }

    /**
     * Render "select all"
     */
    protected function renderSelectAllBlock(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $a_tpl->setCurrentBlock("select_all_row");
        $a_tpl->setVariable("CHECKBOXNAME", "bl_cb_" . $this->bl_cnt);
        $a_tpl->setVariable("SEL_ALL_PARENT", "bl_cntr_" . $this->bl_cnt);
        $a_tpl->setVariable("SEL_ALL_PARENT", "bl_cntr_" . $this->bl_cnt);
        $a_tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
        $a_tpl->parseCurrentBlock();
    }

    protected function addSeparatorRow(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock("container_block");
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get downloadable repository object types
     */
    protected function getDownloadableTypes(): array
    {
        return ["fold", "file"];
    }

    public function renderDetails(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        if (count($this->details)) {
            $a_tpl->setCurrentBlock('container_details_row');
            $a_tpl->setVariable('TXT_DETAILS', $lng->txt('details'));
            $a_tpl->parseCurrentBlock();
        }
    }

    ///
    /// Render Item Block Sequence
    ///

    public function getItemRenderer(): \ILIAS\Containter\Content\ItemRenderer
    {
        return $this->item_renderer;
    }

    protected function renderContainerPage(): string
    {
        return $this->container_gui->getContainerPageHTML();
    }

    public function renderItemBlockSequence(
        \ILIAS\Container\Content\ItemBlock\ItemBlockSequence $sequence
    ): string {
        $valid = false;

        $page_html = $this->renderContainerPage();
        $block_tpl = $this->initBlockTemplate();

        $embedded_block_ids = $this->item_presentation->getPageEmbeddedBlockIds();
        foreach ($sequence->getBlocks() as $block) {
            $block_id = "";
            $force_item_even_if_already_rendered = false;
            if ($block->getBlock() instanceof \ILIAS\Container\Content\ItemGroupBlock) {
                $block_id = (string) $block->getBlock()->getRefId();
                $force_item_even_if_already_rendered = true;
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\TypeBlock) {
                $block_id = $block->getBlock()->getType();
                if ($block->getPageEmbedded()) {
                    $force_item_even_if_already_rendered = true;
                }
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\SessionBlock) {
                $block_id = "sess";
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock) {
                $block_id = "_other";
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\ObjectivesBlock) {
                $block_id = "_lobj";
            }

            $position = 1;
            $pos_prefix = "";

            // (1) add block
            if ($block->getBlock() instanceof \ILIAS\Container\Content\ItemGroupBlock) {
                $this->addItemGroupBlock($block_id);
                $pos_prefix = "[itgr][" . \ilObject::_lookupObjId($block->getBlock()->getRefId()) . "]";
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock) {
                $title = $this->item_presentation->filteredSubtree()
                    ? $this->lng->txt("cont_found_objects")
                    : $this->lng->txt("content");
                $this->addCustomBlock($block_id, $title);
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\TypeBlock ||
                $block->getBlock() instanceof \ILIAS\Container\Content\SessionBlock) {
                $this->addTypeBlock(
                    $block_id,
                    $this->getBlockPrefix($block_id),
                    $this->getBlockPostfix($block_id)
                );
            }

            // (2) render and add items
            foreach ($block->getItemRefIds() as $ref_id) {
                if ($this->isItemHidden($block_id, $ref_id)) {
                    continue;
                }
                $item_data = $this->item_presentation->getRawDataByRefId($ref_id);
                $checkbox = \ILIAS\Containter\Content\ItemRenderer::CHECKBOX_NONE;
                if ($this->container_gui->isActiveAdministrationPanel()) {
                    $checkbox = \ILIAS\Containter\Content\ItemRenderer::CHECKBOX_ADMIN;
                }
                $item_group_list_presentation = "";
                if ($block->getBlock() instanceof \ILIAS\Container\Content\ItemGroupBlock) {
                    if ($this->getViewModeOfItemGroup((int) $block_id) === ilContainerContentGUI::VIEW_MODE_TILE) {
                        $item_group_list_presentation = "tile";
                    }
                }
                $html = $this->item_renderer->renderItem(
                    $item_data,
                    $position++,
                    false,
                    $pos_prefix,
                    $item_group_list_presentation,
                    $checkbox,
                    $this->item_presentation->isActiveItemOrdering()
                );
                if ($html != "") {
                    $this->addItemToBlock(
                        $block_id,
                        $item_data["type"],
                        $item_data["child"],
                        $html,
                        $force_item_even_if_already_rendered
                    );
                }
            }

            // (3) render blocks
            if ($block->getPageEmbedded()) {
                if ($block->getBlock() instanceof \ILIAS\Container\Content\TypeBlock ||
                    $block->getBlock() instanceof \ILIAS\Container\Content\SessionBlock) {
                    $page_html = preg_replace(
                        '~\[list-' . $block->getId() . '\]~i',
                        $this->renderSingleTypeBlock($block->getId()),
                        $page_html
                    );
                    $valid = true;
                } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\ItemGroupBlock) {
                    $page_html = preg_replace(
                        '~\[item-group-' . $block->getId() . '\]~i',
                        $this->renderSingleCustomBlock((int) $block->getId()),
                        $page_html
                    );
                    $valid = true;
                } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock) {
                    $page_html = preg_replace(
                        '~\[list-_other\]~i',
                        $this->renderSingleCustomBlock($block->getId()),
                        $page_html
                    );
                    $valid = true;
                } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\ObjectivesBlock) {
                    $page_html = preg_replace(
                        '~\[list-_lobj\]~i',
                        $this->objective_renderer->renderObjectives(),
                        $page_html
                    );
                    $valid = true;
                }
            } else {
                if ($block->getBlock() instanceof \ILIAS\Container\Content\ItemGroupBlock ||
                    $block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock) {
                    if ($this->renderHelperCustomBlock($block_tpl, $block_id, false, $block->getLimitExhausted())) {
                        $this->addSeparatorRow($block_tpl);
                        $valid = true;
                    }
                }
                if ($block->getBlock() instanceof \ILIAS\Container\Content\TypeBlock ||
                    $block->getBlock() instanceof \ILIAS\Container\Content\SessionBlock) {
                    if ($this->renderHelperTypeBlock($block_tpl, $block_id, false, $block->getLimitExhausted())) {
                        $this->addSeparatorRow($block_tpl);
                        $valid = true;
                    }
                }
                if ($block->getBlock() instanceof \ILIAS\Container\Content\ObjectivesBlock) {
                    $this->objective_renderer->renderObjectives();
                    $block_tpl->setVariable(
                        "CONTENT",
                        $this->objective_renderer->getContent()
                    );
                    $this->addSeparatorRow($block_tpl);
                    $valid = true;
                }
            }
        }

        // remove embedded, but unrendered blocks
        foreach ($this->item_presentation->getPageEmbeddedBlockIds() as $id) {
            $page_html = preg_replace(
                '~\[list-' . $id . '\]~i',
                "",
                $page_html
            );
        }

        if ($valid) {
            $this->renderDetails($block_tpl);
            return $page_html . $block_tpl->get();
        }
        return $page_html;
    }

    /**
     * replaces ilContainerContentGUI::renderItemGroup
     */
    protected function addItemGroupBlock(string $block_id, int $block_pos = 0): void
    {
        $item_data = $this->item_presentation->getRawDataByRefId((int) $block_id);
        $item_list_gui = $this->item_renderer->getItemGUI($item_data);

        $perm_ok = true;
        /*
        $ilAccess = $this->access;
        $ilUser = $this->user;

        // #16493
        $perm_ok = ($ilAccess->checkAccess("visible", "", $item_data['ref_id']) &&
            $ilAccess->checkAccess("read", "", $item_data['ref_id']));

        $items = ilObjectActivation::getItemsByItemGroup($item_data['ref_id']);

        // get all valid ids (this is filtered)
        $all_ids = array_map(static function (array $i) : int {
            return (int) $i["child"];
        }, $this->items["_all"]);

        // remove filtered items
        $items = array_filter($items, static function (array $i) use ($all_ids) : bool {
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
        */

        $item_list_gui->enableNotes(false);
        $item_list_gui->enableTags(false);
        $item_list_gui->enableComments(false);
        $item_list_gui->enableTimings(false);
        $item_list_gui->initItem(
            (int) $item_data["ref_id"],
            (int) $item_data["obj_id"],
            "itgr",
            $item_data["title"],
            $item_data["description"]
        );
        $commands_html = $item_list_gui->getCommandsHTML();

        // determine behaviour
        $item_group = new ilObjItemGroup($item_data["ref_id"]);
        $beh = $item_group->getBehaviour();
        $stored_val = $this->block_repo->getProperty(
            "itgr_" . $item_data["ref_id"],
            $this->user->getId(),
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
                "&cont_block_id=itgr_" . $item_data['ref_id']
        ];
        if (ilObjItemGroup::lookupHideTitle($item_data["obj_id"]) &&
            !$this->container_gui->isActiveAdministrationPanel()) {
            $this->addCustomBlock($block_id, "", $commands_html, $data);
        } else {
            $this->addCustomBlock($block_id, $item_data["title"], $commands_html, $data);
        }
    }

    protected function getBlockPrefix($block_id): string
    {
        if ($this->block_prefix_closure instanceof Closure) {
            $c = $this->block_prefix_closure;
            return (string) $c($block_id);
        }
        return "";
    }

    protected function getBlockPostfix($block_id): string
    {
        if ($this->block_postfix_closure instanceof Closure) {
            $c = $this->block_postfix_closure;
            return (string) $c($block_id);
        }
        return "";
    }

    protected function isItemHidden(string $block_id, int $ref_id): bool
    {
        if ($this->item_hidden_closure instanceof Closure) {
            $c = $this->item_hidden_closure;
            return (bool) $c($block_id, $ref_id);
        }
        return false;
    }

}
