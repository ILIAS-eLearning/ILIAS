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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerSortingSettings
{
    protected ilTree $tree;
    /** @var array<int, self>  */
    private static array $instances = [];
    protected int $obj_id;
    protected int $sort_mode = ilContainer::SORT_TITLE;
    protected int $sort_direction = ilContainer::SORT_DIRECTION_ASC;
    protected int $new_items_position = ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM;
    protected int $new_items_order = ilContainer::SORT_NEW_ITEMS_ORDER_TITLE;
    protected ilDBInterface $db;

    public function __construct(int $a_obj_id = 0)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        
        $this->obj_id = $a_obj_id;
        $this->db = $ilDB;
        
        $this->read();
    }
    
    public static function getInstanceByObjId(int $a_obj_id) : self
    {
        return self::$instances[$a_obj_id] ?? (self::$instances[$a_obj_id] = new self($a_obj_id));
    }
    
    /**
     * Load inherited settings
     */
    public function loadEffectiveSettings() : self
    {
        if ($this->getSortMode() !== ilContainer::SORT_INHERIT) {
            return $this;
        }

        $effective_settings = $this->getInheritedSettings($this->obj_id);
        $inherited = clone $this;
        
        if ($effective_settings->getSortMode() === ilContainer::SORT_INHERIT) {
            $inherited->setSortMode(ilContainer::SORT_TITLE);
        } else {
            $inherited->setSortMode($effective_settings->getSortMode());
            $inherited->setSortNewItemsOrder($effective_settings->getSortNewItemsOrder());
            $inherited->setSortNewItemsPosition($effective_settings->getSortNewItemsPosition());
        }
        return $inherited;
    }
    
    
    public function getInheritedSettings(int $a_container_obj_id) : self
    {
        $tree = $this->tree;
        
        if (!$a_container_obj_id) {
            $a_container_obj_id = $this->obj_id;
        }
        
        $ref_ids = ilObject::_getAllReferences($a_container_obj_id);
        $ref_id = current($ref_ids);
        
        if ($cont_ref_id = $tree->checkForParentType($ref_id, 'grp', true)) {
            $parent_obj_id = ilObject::_lookupObjId($cont_ref_id);
            $parent_settings = self::getInstanceByObjId($parent_obj_id);
            
            if ($parent_settings->getSortMode() === ilContainer::SORT_INHERIT) {
                return $this->getInheritedSettings($parent_obj_id);
            }
            return $parent_settings;
        }
        
        if ($cont_ref_id = $tree->checkForParentType($ref_id, 'crs', true)) {
            $parent_obj_id = ilObject::_lookupObjId($cont_ref_id);
            $parent_settings = self::getInstanceByObjId($parent_obj_id);
            return $parent_settings;
        }
        // no parent settings found => return current settings
        return $this;
    }


    public static function _readSortMode(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT sort_mode FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->sort_mode;
        }
        return ilContainer::SORT_INHERIT;
    }

    public static function _lookupSortMode(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        // Try to read from table
        $query = "SELECT sort_mode FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ((int) $row->sort_mode !== ilContainer::SORT_INHERIT) {
                return (int) $row->sort_mode;
            }
        }
        return self::lookupSortModeFromParentContainer($a_obj_id);
    }
    
    public static function lookupSortModeFromParentContainer(int $a_obj_id) : int
    {
        $settings = self::getInstanceByObjId($a_obj_id);
        $inherited_settings = $settings->getInheritedSettings($a_obj_id);
        return $inherited_settings->getSortMode();
    }
    
    public static function _cloneSettings(
        int $a_old_id,
        int $a_new_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT sort_mode,sort_direction,new_items_position,new_items_order " .
            "FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($a_old_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $query = "DELETE FROM container_sorting_set " .
                "WHERE obj_id = " . $ilDB->quote($a_new_id) . " ";
            $ilDB->manipulate($query);

            $query = "INSERT INTO container_sorting_set  " .
                "(obj_id,sort_mode, sort_direction, new_items_position, new_items_order) " .
                "VALUES( " .
                $ilDB->quote($a_new_id, 'integer') . ", " .
                $ilDB->quote($row["sort_mode"], 'integer') . ", " .
                $ilDB->quote($row["sort_direction"], 'integer') . ', ' .
                $ilDB->quote($row["new_items_position"], 'integer') . ', ' .
                $ilDB->quote($row["new_items_order"], 'integer') . ' ' .
                ")";
            $ilDB->manipulate($query);
        }
    }
    
    public function getSortMode() : int
    {
        return $this->sort_mode ?: 0;
    }
    
    public function getSortDirection() : int
    {
        return $this->sort_direction ?: ilContainer::SORT_DIRECTION_ASC;
    }

    public function getSortNewItemsPosition() : int
    {
        return $this->new_items_position;
    }

    public function getSortNewItemsOrder() : int
    {
        return $this->new_items_order;
    }

    /**
     * @param int $a_mode MODE_TITLE | MODE_MANUAL | MODE_ACTIVATION
     */
    public function setSortMode(int $a_mode) : void
    {
        $this->sort_mode = $a_mode;
    }
    
    public function setSortDirection(int $a_direction) : void
    {
        $this->sort_direction = $a_direction;
    }

    public function setSortNewItemsPosition(int $a_position) : void
    {
        $this->new_items_position = $a_position;
    }

    public function setSortNewItemsOrder(int $a_order) : void
    {
        $this->new_items_order = $a_order;
    }

    public function update() : void
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer');
        $ilDB->manipulate($query);
        
        $this->save();
    }

    public function save() : void
    {
        $ilDB = $this->db;

        $query = "INSERT INTO container_sorting_set " .
            "(obj_id,sort_mode, sort_direction, new_items_position, new_items_order) " .
            "VALUES ( " .
            $this->db->quote($this->obj_id, 'integer') . ", " .
            $this->db->quote($this->sort_mode, 'integer') . ", " .
            $this->db->quote($this->sort_direction, 'integer') . ', ' .
            $this->db->quote($this->new_items_position, 'integer') . ', ' .
            $this->db->quote($this->new_items_order, 'integer') . ' ' .
            ")";
        $ilDB->manipulate($query);
    }
    
    public function delete() : void
    {
        $ilDB = $this->db;
        
        $query = 'DELETE FROM container_sorting_set WHERE obj_id = ' . $ilDB->quote($this->obj_id, 'integer');
        $ilDB->query($query);
    }
    
    protected function read() : void
    {
        if (!$this->obj_id) {
            return;
        }
        
        $query = "SELECT * FROM container_sorting_set " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " ";
            
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->sort_mode = (int) $row->sort_mode;
            $this->sort_direction = (int) $row->sort_direction;
            $this->new_items_position = (int) $row->new_items_position;
            $this->new_items_order = (int) $row->new_items_order;
            return;
        }
    }
    
    /**
     * Get string representation of sort mode
     */
    public static function sortModeToString(int $a_sort_mode) : string
    {
        global $DIC;

        $lng = $DIC->language();
        
        $lng->loadLanguageModule('crs');
        switch ($a_sort_mode) {
            case ilContainer::SORT_ACTIVATION:
                return $lng->txt('crs_sort_activation');
                
            case ilContainer::SORT_MANUAL:
                return $lng->txt('crs_sort_manual');

            case ilContainer::SORT_TITLE:
                return $lng->txt('crs_sort_title');
                
            case ilContainer::SORT_CREATION:
                return $lng->txt('sorting_creation_header');
        }
        return '';
    }

    /**
     * sorting XML-export for all container objects
     */
    public static function _exportContainerSortingSettings(
        ilXmlWriter $xml,
        int $obj_id
    ) : void {
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
     * sorting import for all container objects
     */
    public static function _importContainerSortingSettings(
        array $attibs,
        int $obj_id
    ) : void {
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
