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

use ILIAS\Container\Sorting\Service\DomainService as SortingDomainService;

/**
 * @deprecated Please use the sorting domain service from the Container services. Will be removed with ILIAS 13.
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerSortingSettings
{
    protected SortingDomainService $sorting_domain;

    /** @var array<int, self>  */
    private static array $instances = [];
    protected int $obj_id;
    protected int $sort_mode = ilContainer::SORT_TITLE;
    protected int $sort_direction = ilContainer::SORT_DIRECTION_ASC;
    protected int $new_items_position = ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM;
    protected int $new_items_order = ilContainer::SORT_NEW_ITEMS_ORDER_TITLE;

    public function __construct(int $a_obj_id = 0)
    {
        global $DIC;

        $this->sorting_domain = $DIC->container()->internal()->domain()->sorting();

        $this->obj_id = $a_obj_id;

        $this->read();
    }

    public static function getInstanceByObjId(int $a_obj_id): self
    {
        return self::$instances[$a_obj_id] ?? (self::$instances[$a_obj_id] = new self($a_obj_id));
    }

    /**
     * Load inherited settings
     */
    public function loadEffectiveSettings(): self
    {
        $effective_settings = $this->sorting_domain->settings()->getEffectiveSettingsForObject($this->obj_id);

        $effective = clone $this;
        $effective->setSortMode($effective_settings->getSortMode());
        $effective->setSortDirection($effective_settings->getSortDirection());
        $effective->setSortNewItemsOrder($effective_settings->getSortNewItemsOrder());
        $effective->setSortNewItemsPosition($effective_settings->getSortNewItemsPosition());

        return $effective;
    }

    public static function _lookupSortMode(int $a_obj_id): int
    {
        global $DIC;

        return $DIC->container()->internal()->domain()->sorting()->settings()->lookupSortModeForObject($a_obj_id);
    }

    public static function lookupEffectiveSortMode(int $a_obj_id): int
    {
        $settings = self::getInstanceByObjId($a_obj_id);
        $inherited_settings = $settings->loadEffectiveSettings();
        return $inherited_settings->getSortMode();
    }

    public static function _cloneSettings(
        int $a_old_id,
        int $a_new_id
    ): void {
        global $DIC;

        $DIC->container()->internal()->domain()->sorting()->settings()->cloneSettings(
            $a_old_id,
            $a_new_id,
        );
    }

    public function getSortMode(): int
    {
        return $this->sort_mode;
    }

    public function getSortDirection(): int
    {
        return $this->sort_direction;
    }

    public function getSortNewItemsPosition(): int
    {
        return $this->new_items_position;
    }

    public function getSortNewItemsOrder(): int
    {
        return $this->new_items_order;
    }

    /**
     * @param int $a_mode MODE_TITLE | MODE_MANUAL | MODE_ACTIVATION
     */
    public function setSortMode(int $a_mode): void
    {
        $this->sort_mode = $a_mode;
    }

    public function setSortDirection(int $a_direction): void
    {
        $this->sort_direction = $a_direction;
    }

    public function setSortNewItemsPosition(int $a_position): void
    {
        $this->new_items_position = $a_position;
    }

    public function setSortNewItemsOrder(int $a_order): void
    {
        $this->new_items_order = $a_order;
    }

    public function update(): void
    {
        $this->sorting_domain->settings()->saveSettingsForObject(
            $this->obj_id,
            $this->getSortMode(),
            $this->getSortDirection(),
            $this->getSortNewItemsPosition(),
            $this->getSortNewItemsOrder()
        );
    }

    public function save(): void
    {
        $this->sorting_domain->settings()->saveSettingsForObject(
            $this->obj_id,
            $this->getSortMode(),
            $this->getSortDirection(),
            $this->getSortNewItemsPosition(),
            $this->getSortNewItemsOrder()
        );
    }

    public function delete(): void
    {
        $this->sorting_domain->settings()->deleteSettingsForObject($this->obj_id);
    }

    protected function read(): void
    {
        if (!$this->obj_id) {
            return;
        }

        $settings = $this->sorting_domain->settings()->getSettingsForObject($this->obj_id);
        $this->sort_mode = $settings->getSortMode();
        $this->sort_direction = $settings->getSortDirection();
        $this->new_items_position = $settings->getSortNewItemsPosition();
        $this->new_items_order = $settings->getSortNewItemsOrder();
    }

    /**
     * Get string representation of sort mode
     */
    public static function sortModeToString(int $a_sort_mode): string
    {
        global $DIC;

        return $DIC->container()->internal()->domain()->sorting()->settings()->sortModeToString($a_sort_mode);
    }

    /**
     * TODO still used in SOAP export of course/group
     * sorting XML-export for all container objects
     */
    public static function _exportContainerSortingSettings(
        ilXmlWriter $xml,
        int $obj_id
    ): void {
        $settings = self::getInstanceByObjId($obj_id);

        $attr = [];
        switch ($settings->getSortMode()) {
            case ilContainer::SORT_MANUAL:
                $order = 'Title';
                switch ($settings->getSortNewItemsOrder()) {
                    case ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION:
                        $order = 'Activation';
                        break;
                    case ilContainer::SORT_NEW_ITEMS_ORDER_CREATION:
                        $order = 'Creation';
                        break;
                    case ilContainer::SORT_NEW_ITEMS_ORDER_TITLE:
                        $order = 'Title';
                        break;
                }

                $attr = [
                    'direction' => $settings->getSortDirection() === ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'position' => $settings->getSortNewItemsPosition() === ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM ? "Bottom" : "Top",
                    'order' => $order,
                    'type' => 'Manual'
                ];

                break;

            case ilContainer::SORT_CREATION:
                $attr = [
                    'direction' => $settings->getSortDirection() === ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'type' => 'Creation'
                ];
                break;

            case ilContainer::SORT_TITLE:
                $attr = [
                    'direction' => $settings->getSortDirection() === ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'type' => 'Title'
                ];
                break;
            case ilContainer::SORT_ACTIVATION:
                $attr = [
                    'direction' => $settings->getSortDirection() === ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'type' => 'Activation'
                ];
                break;
            case ilContainer::SORT_INHERIT:
                $attr = [
                    'type' => 'Inherit'
                ];
        }
        $xml->xmlElement('Sort', $attr);
    }

    /**
     * TODO still used in legacy and SOAP import of category/folder/course/group
     * sorting import for all container objects
     */
    public static function _importContainerSortingSettings(
        array $attibs,
        int $obj_id
    ): void {
        $settings = self::getInstanceByObjId($obj_id);

        switch ($attibs['type'] ?? '') {
            case 'Manual':
                $settings->setSortMode(ilContainer::SORT_MANUAL);
                break;
            case 'Creation':
                $settings->setSortMode(ilContainer::SORT_CREATION);
                break;
            case 'Title':
                $settings->setSortMode(ilContainer::SORT_TITLE);
                break;
            case 'Activation':
                $settings->setSortMode(ilContainer::SORT_ACTIVATION);
                break;
        }

        switch ($attibs['direction'] ?? '') {
            case 'ASC':
                $settings->setSortDirection(ilContainer::SORT_DIRECTION_ASC);
                break;
            case 'DESC':
                $settings->setSortDirection(ilContainer::SORT_DIRECTION_DESC);
                break;
        }

        switch ($attibs['position'] ?? "") {
            case "Top":
                $settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_TOP);
                break;
            case "Bottom":
                $settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
                break;
        }

        switch ($attibs['order'] ?? "") {
            case 'Creation':
                $settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_CREATION);
                break;
            case 'Title':
                $settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_TITLE);
                break;
            case 'Activation':
                $settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION);
        }

        $settings->update();
    }
}
