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

namespace ILIAS\Container\Content\ItemBlock;

use ILIAS\Container\Content\DataService;
use ILIAS\Container\Content\BlockSequence;
use ILIAS\Container\Content\BlockSequencePart;
use ILIAS\Container\Content\ItemSetManager;
use ILIAS\Container\Content;
use ILIAS\Container\InternalDomainService;

/**
 * Generates concrete blocks with items
 * for the view
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemBlockSequenceGenerator
{
    protected bool $include_empty_blocks;
    protected Content\ModeManager $mode_manager;
    protected \ilAccessHandler $access;
    protected int $block_limit;
    protected DataService $data_service;
    protected BlockSequence $block_sequence;
    protected ItemSetManager $item_set_manager;
    protected InternalDomainService $domain_service;
    protected \ilContainer $container;
    protected ?ItemBlockSequence $sequence = null;
    protected bool $has_other_block = false;
    /** @var int[] */
    protected array $accumulated_ref_ids = [];
    /** @var array<int, int[]> */
    protected static array $item_group_ref_ids = [];
    /** @var int[] */
    protected array $all_item_group_item_ref_ids = [];
    protected array $all_ref_ids = [];

    public function __construct(
        DataService $data_service,
        InternalDomainService $domain_service,
        \ilContainer $container,
        BlockSequence $block_sequence,
        ItemSetManager $item_set_manager,
        bool $include_empty_blocks = true
    ) {
        $this->access = $domain_service->access();
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->block_sequence = $block_sequence;
        $this->item_set_manager = $item_set_manager;
        $this->container = $container;
        $this->block_limit = (int) \ilContainer::_lookupContainerSetting($container->getId(), "block_limit");
        $this->mode_manager = $this->domain_service->content()->mode($container);
        $this->include_empty_blocks = $include_empty_blocks;
    }

    public function getSequence(): ItemBlockSequence
    {
        if (is_null($this->sequence)) {
            $this->preloadSessionandItemGroupItemData();
            $item_blocks = [];
            $sorted_blocks = [];

            // get blocks from block sequence parts (item groups, by type, objective)
            foreach ($this->block_sequence->getParts() as $part) {
                foreach ($this->getBlocksForPart($part) as $block) {
                    $item_blocks[$block->getId()] = $block;
                }
            }

            // get blocks of page, put them to the start
            $embedded_ids = $this->getPageEmbeddedBlockIds();
            $other_is_page_embedded = false;
            foreach ($embedded_ids as $id) {
                if (isset($item_blocks[$id])) {
                    $item_blocks[$id]->setPageEmbedded(true);
                    $sorted_blocks[] = $item_blocks[$id];
                    unset($item_blocks[$id]);
                } elseif (!is_numeric($id)) {
                    // add item blocks of page, even if originally not in the block set
                    if ($id === "_other") {
                        $this->has_other_block = true;
                        $other_is_page_embedded = true;
                    } else {
                        $ref_ids = $this->item_set_manager->getRefIdsOfType($id);
                        $this->accumulateRefIds($ref_ids);
                        $block_items = $this->determineBlockItems($ref_ids, true);
                        // we remove this check to prevent [list-cat] stuff from appearing in the list
                        // this will output a message (is empty) in editing mode and
                        // remove the block (empty string) in presentation mode
                        if ($this->include_empty_blocks || count($block_items->getRefIds()) > 0) {
                            $block = $this->data_service->itemBlock(
                                $id,
                                $this->data_service->typeBlock($id),
                                $block_items->getRefIds(),
                                $block_items->getLimitExhausted()
                            );
                            $block->setPageEmbedded(true);
                            $sorted_blocks[] = $block;
                        }
                    }
                } else {
                    // e.g. deleted item group
                    //throw new \ilException("Missing item group data.");
                }
            }

            // get other block
            $other_block = $this->getOtherBlock();
            if (!is_null($other_block)) {
                if ($other_is_page_embedded) {
                    $other_block->setPageEmbedded(true);
                }
                $item_blocks["_other"] = $other_block;
            }

            // manual sorting
            $pos = 10;
            $sorting = \ilContainerSorting::_getInstance($this->container->getId());
            foreach ($sorting->getBlockPositions() as $id) {
                if (isset($item_blocks[$id])) {
                    $item_blocks[$id]->setPosition($pos);
                    $sorted_blocks[] = $item_blocks[$id];
                    unset($item_blocks[$id]);
                    $pos += 10;
                }
            }

            // rest in order of base block sequence
            foreach ($item_blocks as $block) {
                $block->setPosition($pos);
                $sorted_blocks[] = $block;
                $pos += 10;
            }
            $this->sequence = new ItemBlockSequence($sorted_blocks);
        }
        return $this->sequence;
    }

    protected function preloadSessionandItemGroupItemData(): void
    {
        foreach ($this->block_sequence->getParts() as $part) {
            // SessionBlock
            if ($part instanceof Content\SessionBlock) {
                /*
                $ref_ids = $this->item_set_manager->getRefIdsOfType("sess");
                $block_items = $this->determineBlockItems($ref_ids);
                if (count($block_items->getRefIds()) > 0) {
                    yield $this->data_service->itemBlock(
                        "sess",
                        $this->data_service->sessionBlock(),
                        $block_items->getRefIds(),
                        $block_items->getLimitExhausted()
                    );
                }*/
            }
            // ItemGroupBlock
            if ($part instanceof Content\ItemGroupBlock) {
                $item_ref_ids = $this->getItemGroupItemRefIds($part->getRefId());
                $this->all_item_group_item_ref_ids = array_unique(array_merge($this->all_item_group_item_ref_ids, $item_ref_ids));
            }
            // ItemGroupBlocks
            if ($part instanceof Content\ItemGroupBlocks) {
                foreach ($this->item_set_manager->getRefIdsOfType("itgr") as $item_group_ref_id) {
                    $item_ref_ids = $this->getItemGroupItemRefIds($item_group_ref_id);
                    $this->all_item_group_item_ref_ids = array_unique(array_merge($this->all_item_group_item_ref_ids, $item_ref_ids));
                }
            }
        }
    }

    /**
     * @return ItemBlock[]
     */
    protected function getBlocksForPart(BlockSequencePart $part): \Iterator
    {
        // TypeBlocks
        if ($part instanceof Content\TypeBlocks) {
            foreach ($this->getGroupedObjTypes() as $type) {
                $ref_ids = $this->item_set_manager->getRefIdsOfType($type);
                $this->accumulateRefIds($ref_ids);
                $block_items = $this->determineBlockItems($ref_ids, true);
                if ($type !== "itgr" && count($block_items->getRefIds()) > 0) {
                    yield $this->data_service->itemBlock(
                        $type,
                        $this->data_service->typeBlock($type),
                        $block_items->getRefIds(),
                        $block_items->getLimitExhausted()
                    );
                }
            }
        }
        // TypeBlock
        if ($part instanceof Content\TypeBlock) {
            $ref_ids = $this->item_set_manager->getRefIdsOfType($part->getType());
            $this->accumulateRefIds($ref_ids);
            $block_items = $this->determineBlockItems($ref_ids, true);
            if (count($block_items->getRefIds()) > 0) {
                yield $this->data_service->itemBlock(
                    $part->getType(),
                    $this->data_service->typeBlock($part->getType()),
                    $block_items->getRefIds(),
                    $block_items->getLimitExhausted()
                );
            }
        }
        // SessionBlock
        if ($part instanceof Content\SessionBlock) {
            $ref_ids = $this->item_set_manager->getRefIdsOfType("sess");
            $this->accumulateRefIds($ref_ids);
            $block_items = $this->determineBlockItems($ref_ids);
            if (count($block_items->getRefIds()) > 0) {
                yield $this->data_service->itemBlock(
                    "sess",
                    $this->data_service->sessionBlock(),
                    $block_items->getRefIds(),
                    $block_items->getLimitExhausted()
                );
            }
        }
        // ItemGroupBlock
        if ($part instanceof Content\ItemGroupBlock) {
            $block = $this->getItemGroupBlock($part->getRefId());
            if (!is_null($block)) {
                yield $block;
            }
        }
        // ObjectivesBlock
        if ($part instanceof Content\ObjectivesBlock) {
            // in admin mode, we do not include the objectives block
            // -> all items will be presented in item group/other block
            if (!$this->mode_manager->isAdminMode()) {
                $objective_ids = \ilCourseObjective::_getObjectiveIds($this->container->getId(), true);
                $ref_ids = [];
                foreach ($objective_ids as $objective_id) {
                    foreach (\ilObjectActivation::getItemsByObjective((int) $objective_id) as $data) {
                        $ref_ids[] = (int) $data["ref_id"];
                    }
                }
                yield $this->data_service->itemBlock(
                    "_lobj",
                    $part,
                    $ref_ids,
                    false,
                    $objective_ids
                );
            }
        }
        // ItemGroupBlocks
        if ($part instanceof Content\ItemGroupBlocks) {
            foreach ($this->item_set_manager->getRefIdsOfType("itgr") as $item_group_ref_id) {
                $block = $this->getItemGroupBlock($item_group_ref_id);
                if (!is_null($block)) {
                    yield $block;
                }
            }
        }
        // ItemGroupBlocks
        if ($part instanceof Content\OtherBlock) {
            $this->has_other_block = true;
        }
    }

    protected function determineBlockItems(
        array $ref_ids,
        $filter_session_and_item_group_items = false,
        bool $prevent_duplicats = false
    ): BlockItemsInfo {
        $exhausted = false;
        $accessible_ref_ids = [];
        foreach ($ref_ids as $ref_id) {
            if ($prevent_duplicats && in_array($ref_id, $this->all_ref_ids)) {
                continue;
            }
            if (\ilObject::_lookupType(\ilObject::_lookupObjId($ref_id)) === "itgr") {
                continue;
            }
            $this->all_ref_ids[] = $ref_id;
            if ($exhausted) {
                break;
            }
            if ($this->access->checkAccess('visible', '', $ref_id)) {
                if ($this->block_limit > 0 && count($accessible_ref_ids) >= $this->block_limit) {
                    $exhausted = true;
                } elseif (!$filter_session_and_item_group_items || !in_array($ref_id, $this->all_item_group_item_ref_ids)) {
                    $accessible_ref_ids[] = $ref_id;
                }
            }
        }
        return $this->data_service->blockItemsInfo(
            $accessible_ref_ids,
            $exhausted
        );
    }

    protected function accumulateRefIds(array $ref_ids): void
    {
        foreach ($ref_ids as $ref_id) {
            $this->accumulated_ref_ids[$ref_id] = $ref_id;
        }
    }

    protected function getOtherBlock(): ?ItemBlock
    {
        if (!$this->has_other_block) {
            return null;
        }
        $remaining_ref_ids = array_filter(
            $this->item_set_manager->getAllRefIds(),
            fn($i) => (!isset($this->accumulated_ref_ids[$i]) && !$this->item_set_manager->isSideBlockItem($i))
        );
        $block_items = $this->determineBlockItems($remaining_ref_ids, true, true);
        // we remove this check to prevent [list-_other] stuff from appearing in the list
        // this will output a message (is empty) in editing mode and
        // remove the block (empty string) in presentation mode
        if ($this->include_empty_blocks || count($block_items->getRefIds()) > 0) {
            return $this->data_service->itemBlock(
                "_other",
                $this->data_service->otherBlock(),
                $block_items->getRefIds(),
                $block_items->getLimitExhausted()
            );
        }
        return null;
    }

    /**
     * Get grouped repository object types
     * @todo from ilContainerContentGUI; remove
     */
    protected function getGroupedObjTypes(): \Iterator
    {
        foreach (\ilObjectDefinition::getGroupedRepositoryObjectTypes($this->container->getType())
            as $key => $type) {
            yield $key;
        }
    }

    protected function getItemGroupItemRefIds(int $item_group_ref_id): array
    {
        if (!isset(self::$item_group_ref_ids[$item_group_ref_id])) {
            $items = \ilContainerSorting::_getInstance(
                $this->container->getId()
            )->sortSubItems('itgr', \ilObject::_lookupObjId($item_group_ref_id), \ilObjectActivation::getItemsByItemGroup($item_group_ref_id));

            self::$item_group_ref_ids[$item_group_ref_id] = array_map(static function ($i) {
                return (int) $i["child"];
            }, $items);
        }
        return self::$item_group_ref_ids[$item_group_ref_id];
    }

    protected function getItemGroupBlock(int $item_group_ref_id): ?ItemBlock
    {
        $ref_ids = $this->getItemGroupItemRefIds($item_group_ref_id);
        $this->accumulateRefIds($ref_ids);
        $block_items = $this->determineBlockItems($ref_ids);
        // #16493
        if (!$this->access->checkAccess("visible", "", $item_group_ref_id) ||
            !$this->access->checkAccess("read", "", $item_group_ref_id)) {
            return null;
        }
        // otherwise empty item groups will simply "vanish" from the repository
        if (count($block_items->getRefIds()) > 0 || $this->access->checkAccess('write', '', $item_group_ref_id)) {
            return $this->data_service->itemBlock(
                (string) $item_group_ref_id,
                $this->data_service->itemGroupBlock($item_group_ref_id),
                $block_items->getRefIds(),
                $block_items->getLimitExhausted()
            );
        }
        return null;
    }

    /**
     * @todo determinePageEmbeddedBlocks from ilContainerContent GUI, remove
     */
    public function getPageEmbeddedBlockIds(): array
    {
        $ids = [];
        $page = $this->domain_service->page($this->container);
        $container_page_html = $page->getHtml();

        $type_grps = $this->getGroupedObjTypes();
        // iterate all types
        foreach ($type_grps as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($container_page_html, "[list-" . $v . "]"))) {
                $ids[] = $v;
            }
        }

        $type = "_other";
        if (is_int(strpos($container_page_html, "[list-" . $type . "]"))) {
            $ids[] = $type;
        }
        $type = "_lobj";
        if (is_int(strpos($container_page_html, "[list-" . $type . "]"))) {
            $ids[] = $type;
        }
        // determine item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $container_page_html, $found)) {
            $ids[] = $found[2];
            $container_page_html = preg_replace(
                '~\[' . $found[1] . '\]~i',
                "",
                $container_page_html
            );
        }
        return $ids;
    }
}
