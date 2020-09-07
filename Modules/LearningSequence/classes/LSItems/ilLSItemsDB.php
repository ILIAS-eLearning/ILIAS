<?php

declare(strict_types=1);

/**
 * Class ilLSItemsDB
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSItemsDB
{
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilContainerSorting
     */
    protected $container_sorting;

    /**
     * @var ilLSPostConditionDB
     */
    protected $post_conditions_db;

    /**
     * @var LSItemOnlineStatus
     */
    protected $ls_item_online_status;

    public function __construct(
        ilTree $tree,
        ilContainerSorting $container_sorting,
        ilLSPostConditionDB $post_conditions_db,
        LSItemOnlineStatus $ls_item_online_status
    ) {
        $this->tree = $tree;
        $this->container_sorting = $container_sorting;
        $this->post_conditions_db = $post_conditions_db;
        $this->ls_item_online_status = $ls_item_online_status;
    }

    public function getLSItems(int $ref_id) : array
    {
        $children = $this->tree->getChilds($ref_id);

        $sorting_settings = $this->container_sorting->getSortingSettings();
        $sorting_settings->setSortMode(ilContainer::SORT_MANUAL);
        $sorted = $this->container_sorting->sortItems(array('lsitems' => $children));
        $children = $sorted['lsitems'];

        $conditions = $this->getConditionsForChildren($children);

        $items = [];
        foreach ($children as $position => $child) {
            $ref_id = (int) $child['child'];
            $icon_path = ilObject2::_getIcon("", "big", $child['type']);
            $items[] = new LSItem(
                $child['type'],
                $child['title'],
                $child['description'] ?? "",
                $icon_path = $this->getIconPathForType($child['type']),
                $this->ls_item_online_status->getOnlineStatus($ref_id),
                $position,
                $conditions[$ref_id],
                $ref_id
            );
        }

        return $items;
    }

    protected function getIconPathForType(string $type) : string
    {
        return ilObject2::_getIcon("", "big", $type);
    }

    /**
     * Collect all conditions at once.
     * @return array <int,ilLSPostCondition>
     */
    protected function getConditionsForChildren(array $children) : array
    {
        $ref_ids = array_map(
            function ($i) {
                return (int) $i['child'];
            },
            $children
        );

        $conditions = [];
        foreach ($this->post_conditions_db->select($ref_ids) as $condition) {
            $conditions[$condition->getRefId()] = $condition;
        }

        return $conditions;
    }

    protected function storeItemsOrder(array $ls_items)
    {
        $type_positions = [];
        foreach ($ls_items as $item) {
            $type_positions[$item->getRefId()] = $item->getOrderNumber();
        }
        $this->container_sorting->savePost($type_positions);
    }

    protected function storeOnlineStatus(array $ls_items)
    {
        foreach ($ls_items as $item) {
            $this->ls_item_online_status->setOnlineStatus(
                $item->getRefId(),
                $item->isOnline()
            );
        }
    }

    protected function storePostconditions(array $ls_items)
    {
        $conditions = [];
        foreach ($ls_items as $item) {
            $conditions[] = $item->getPostCondition();
        }
        $this->post_conditions_db->upsert($conditions);
    }

    /**
     * Use this to apply settings made in ContentGUI
     */
    public function storeItems(array $ls_items)
    {
        $this->storeOnlineStatus($ls_items);
        $this->storeItemsOrder($ls_items);
        $this->storePostconditions($ls_items);
    }

    protected function getObjectFor(int $ref_id) : ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id);
    }
}
