<?php declare(strict_types=1);

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
 * Class ilLSItemsDB
 */
class ilLSItemsDB
{
    protected ilTree $tree;
    protected ilContainerSorting $container_sorting;
    protected ilLSPostConditionDB $post_conditions_db;
    protected LSItemOnlineStatus $ls_item_online_status;

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

    /**
     * @return LSItem[]
     */
    public function getLSItems(int $ref_id) : array
    {
        $children = $this->tree->getChilds($ref_id);

        $sorting_settings = $this->container_sorting->getSortingSettings();
        $sorting_settings->setSortMode(ilContainer::SORT_MANUAL);
        $sorted = $this->container_sorting->sortItems(['lsitems' => $children]);
        $children = $sorted['lsitems'];

        $conditions = $this->getConditionsForChildren($children);

        $items = [];
        foreach ($children as $position => $child) {
            $ref_id = (int) $child['child'];
            $items[] = new LSItem(
                $child['type'],
                $child['title'],
                $child['description'] ?? "",
                $this->getIconPathForType($child['type']),
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
        return ilObject2::_getIcon(0, "big", $type);
    }

    /**
     * Collect all conditions at once.
     * @return array <int,ilLSPostCondition>
     */
    protected function getConditionsForChildren(array $children) : array
    {
        $ref_ids = array_map(
            fn ($i) => (int) $i['child'],
            $children
        );

        $conditions = [];
        foreach ($this->post_conditions_db->select($ref_ids) as $condition) {
            $conditions[$condition->getRefId()] = $condition;
        }

        return $conditions;
    }

    protected function storeItemsOrder(array $ls_items) : void
    {
        $type_positions = [];
        foreach ($ls_items as $item) {
            $type_positions[$item->getRefId()] = $item->getOrderNumber();
        }
        $this->container_sorting->savePost($type_positions);
    }

    protected function storeOnlineStatus(array $ls_items) : void
    {
        foreach ($ls_items as $item) {
            $this->ls_item_online_status->setOnlineStatus(
                $item->getRefId(),
                $item->isOnline()
            );
        }
    }

    protected function storePostconditions(array $ls_items) : void
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
    public function storeItems(array $ls_items) : void
    {
        $this->storeOnlineStatus($ls_items);
        $this->storeItemsOrder($ls_items);
        $this->storePostconditions($ls_items);
    }

    // The typehint on ilObject is intentional, we expect this to return some object
    // or need to error instead.
    protected function getObjectFor(int $ref_id) : \ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id);
    }
}
