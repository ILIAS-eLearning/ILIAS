<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once './Services/Container/classes/class.ilContainer.php';

/**
* @defgroup ServicesContainer Services/Container
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesContainer
*/
class ilContainerSortingSettings
{
    /**
     * @var ilTree
     */
    protected $tree;

    private static $instances = array();
    
    protected $obj_id;
    protected $sort_mode = ilContainer::SORT_TITLE;
    protected $sort_direction = ilContainer::SORT_DIRECTION_ASC;
    protected $new_items_position = ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM;
    protected $new_items_order = ilContainer::SORT_NEW_ITEMS_ORDER_TITLE;

    protected $db;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_obj_id = 0)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        
        $this->obj_id = $a_obj_id;
        $this->db = $ilDB;
        
        $this->read();
    }
    
    /**
     * Get singleton instance
     * @param type $a_obj_id
     * @return ilContainerSortingSettings
     */
    public static function getInstanceByObjId($a_obj_id)
    {
        if (self::$instances[$a_obj_id]) {
            return self::$instances[$a_obj_id];
        }
        return self::$instances[$a_obj_id] = new self($a_obj_id);
    }
    
    /**
     * Load inherited settings
     * @return ilContainerSortingSettings
     */
    public function loadEffectiveSettings()
    {
        if ($this->getSortMode() != ilContainer::SORT_INHERIT) {
            return $this;
        }

        $effective_settings = $this->getInheritedSettings($this->obj_id);
        $inherited = clone $this;
        
        if ($effective_settings->getSortMode() == ilContainer::SORT_INHERIT) {
            $inherited->setSortMode(ilContainer::SORT_TITLE);
        } else {
            $inherited->setSortMode($effective_settings->getSortMode());
            $inherited->setSortNewItemsOrder($effective_settings->getSortNewItemsOrder());
            $inherited->setSortNewItemsPosition($effective_settings->getSortNewItemsPosition());
        }
        return $inherited;
    }
    
    
    /**
     * Read inherited settings of course/group
     * @param int $a_container_obj_id
     */
    public function getInheritedSettings($a_container_obj_id)
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
            
            if ($parent_settings->getSortMode() == ilContainer::SORT_INHERIT) {
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


    public static function _readSortMode($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->sort_mode;
        }
        return ilContainer::SORT_INHERIT;
    }


    /**
     * lookup sort mode
     *
     * @access public
     * @static
     *
     * @param int obj_id
     */
    public static function _lookupSortMode($a_obj_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        $objDefinition = $DIC["objDefinition"];
        
        // Try to read from table
        $query = "SELECT * FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->sort_mode != ilContainer::SORT_INHERIT) {
                return $row->sort_mode;
            }
        }
        return self::lookupSortModeFromParentContainer($a_obj_id);
    }
    
    /**
     * Lookup sort mode from parent container
     * @param object $a_obj_id
     * @return
     */
    public static function lookupSortModeFromParentContainer($a_obj_id)
    {
        $settings = self::getInstanceByObjId($a_obj_id);
        $inherited_settings = $settings->getInheritedSettings($a_obj_id);
        return $inherited_settings->getSortMode();
    }
    
    /**
     * Clone settings
     *
     * @access public
     * @static
     *
     * @param int orig obj_id
     * @þaram int new obj_id
     */
    public static function _cloneSettings($a_old_id, $a_new_id)
    {
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
        return true;
    }
    
    /**
     * get sort mode
     *
     * @access public
     *
     */
    public function getSortMode()
    {
        return $this->sort_mode ? $this->sort_mode : 0;
    }
    
    /**
     * Get sort direction
     * @return type
     */
    public function getSortDirection()
    {
        return $this->sort_direction ? $this->sort_direction : ilContainer::SORT_DIRECTION_ASC;
    }

    /**
     * GET new item position
     * @return int position
     */
    public function getSortNewItemsPosition()
    {
        return $this->new_items_position;
    }

    /**
     * GET new item order
     * @return int position
     */
    public function getSortNewItemsOrder()
    {
        return $this->new_items_order;
    }

    /**
     * set sort mode
     *
     * @access public
     * @param int MODE_TITLE | MODE_MANUAL | MODE_ACTIVATION
     *
     */
    public function setSortMode($a_mode)
    {
        $this->sort_mode = (int) $a_mode;
    }
    
    /**
     * Set sort direction
     * @param type $a_direction
     */
    public function setSortDirection($a_direction)
    {
        $this->sort_direction = (int) $a_direction;
    }

    /**
     * SET new item position
     * @param int $a_position
     */
    public function setSortNewItemsPosition($a_position)
    {
        $this->new_items_position = (int) $a_position;
    }

    /**
     * SET new item order
     * @param int $a_order
     */
    public function setSortNewItemsOrder($a_order)
    {
        $this->new_items_order = (int) $a_order;
    }

    /**
     * Update
     *
     * @access public
     *
     */
    public function update()
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM container_sorting_set " .
            "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer');
        $res = $ilDB->manipulate($query);
        
        $this->save();
    }

    /**
     * save settings
     *
     * @access public
     *
     */
    public function save()
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
        $res = $ilDB->manipulate($query);
    }
    
    /**
     * Delete setting
     * @return
     */
    public function delete()
    {
        $ilDB = $this->db;
        
        $query = 'DELETE FROM container_sorting_set WHERE obj_id = ' . $ilDB->quote($this->obj_id, 'integer');
        $ilDB->query($query);
    }
    
    /**
     * read settings
     *
     * @access private
     * @param
     *
     */
    protected function read()
    {
        if (!$this->obj_id) {
            return true;
        }
        
        $query = "SELECT * FROM container_sorting_set " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " ";
            
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->sort_mode = $row->sort_mode;
            $this->sort_direction = $row->sort_direction;
            $this->new_items_position = $row->new_items_position;
            $this->new_items_order = $row->new_items_order;
            return true;
        }
    }
    
    /**
     * get String representation of sort mode
     * @param int $a_sort_mode
     * @return
     */
    public static function sortModeToString($a_sort_mode)
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
     *
     * @param ilXmlWriter $xml
     * @param $obj_id
     */
    public static function _exportContainerSortingSettings(ilXmlWriter $xml, $obj_id)
    {
        $settings = self::getInstanceByObjId($obj_id);

        $attr = array();
        switch ($settings->getSortMode()) {
            case ilContainer::SORT_MANUAL:
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

                $attr = array(
                    'direction' => $settings->getSortDirection() == ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'position' => $settings->getSortNewItemsPosition() == ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM ? "Bottom" : "Top",
                    'order' => $order,
                    'type' => 'Manual'
                );

                break;

            case ilContainer::SORT_CREATION:
                $attr = array(
                    'direction' => $settings->getSortDirection() == ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'type' => 'Creation'
                );
                break;

            case ilContainer::SORT_TITLE:
                $attr = array(
                    'direction' => $settings->getSortDirection() == ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'type' => 'Title'
                );
                break;
            case ilContainer::SORT_ACTIVATION:
                $attr = array(
                    'direction' => $settings->getSortDirection() == ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC",
                    'type' => 'Activation'
                );
                break;
            case ilContainer::SORT_INHERIT:
                $attr = array(
                    'type' => 'Inherit'
                );
        }
        $xml->xmlElement('Sort', $attr);
    }

    /**
     * sorting import for all container objects
     *
     * @param $attibs array (type, direction, position, order)
     * @param $obj_id
     */
    public static function _importContainerSortingSettings($attibs, $obj_id)
    {
        $settings = self::getInstanceByObjId($obj_id);

        switch ($attibs['type']) {
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

        switch ($attibs['direction']) {
            case 'ASC':
                $settings->setSortDirection(ilContainer::SORT_DIRECTION_ASC);
                break;
            case 'DESC':
                $settings->setSortDirection(ilContainer::SORT_DIRECTION_DESC);
                break;
        }

        switch ($attibs['position']) {
            case "Top":
                $settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_TOP);
                break;
            case "Bottom":
                $settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
                break;
        }

        switch ($attibs['order']) {
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
