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
include_once('Services/Container/classes/class.ilContainerSortingSettings.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesContainer
*/
class ilContainerSorting
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var ilTree
     */
    protected $tree;

    protected static $instances = array();

    protected $obj_id;
    protected $db;
    
    protected $sorting_settings = null;
    const ORDER_DEFAULT = 999999;

    /**
     * Constructor
     *
     * @access private
     * @param int obj_id
     *
     */
    private function __construct($a_obj_id)
    {
        global $DIC;

        $this->log = $DIC["ilLog"];
        $this->tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        
        $this->db = $ilDB;
        $this->obj_id = $a_obj_id;
        
        $this->read();
    }
    
    /**
     * Get sorting settings
     * @return ilContainerSortingSettings
     */
    public function getSortingSettings()
    {
        return $this->sorting_settings;
    }
    
    /**
     * get instance by obj_id
     *
     * @access public
     * @param int obj_id
     * @return object ilContainerSorting
     * @static
     */
    public static function _getInstance($a_obj_id)
    {
        if (isset(self::$instances[$a_obj_id])) {
            return self::$instances[$a_obj_id];
        }
        return self::$instances[$a_obj_id] = new ilContainerSorting($a_obj_id);
    }
    
    /**
     * Get positions of subitems
     * @param int $a_obj_id
     * @return
     */
    public static function lookupPositions($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM container_sorting WHERE " .
            "obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sorted[$row->child_id] = $row->position;
        }
        return $sorted ? $sorted : array();
    }
    
    /**
     * clone sorting
     *
     * @return
     * @static
     */
    public function cloneSorting($a_target_id, $a_copy_id)
    {
        $ilDB = $this->db;

        $ilLog = ilLoggerFactory::getLogger("cont");
        $ilLog->debug("Cloning container sorting.");

        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        
        include_once('./Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $mappings = ilCopyWizardOptions::_getInstance($a_copy_id)->getMappings();


        // copy blocks sorting
        $set = $ilDB->queryF(
            "SELECT * FROM container_sorting_bl " .
            " WHERE obj_id = %s ",
            array("integer"),
            array($this->obj_id)
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["block_ids"] != "") {
                $ilLog->debug("Got block sorting for obj_id = " . $this->obj_id . ": " . $rec["block_ids"]);
                $new_ids = implode(";", array_map(function ($block_id) use ($mappings) {
                    if (is_numeric($block_id)) {
                        $block_id = $mappings[$block_id];
                    }
                    return $block_id;
                }, explode(";", $rec["block_ids"])));

                $ilDB->insert("container_sorting_bl", array(
                    "obj_id" => array("integer", $target_obj_id),
                    "block_ids" => array("text", $new_ids)
                ));

                $ilLog->debug("Write block sorting for obj_id = " . $target_obj_id . ": " . $new_ids);
            }
        }


        $ilLog->debug("Read container_sorting for obj_id = " . $this->obj_id);

        $query = "SELECT * FROM container_sorting " .
            "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer');

        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!isset($mappings[$row->child_id]) or !$mappings[$row->child_id]) {
                $ilLog->debug("No mapping found for child id:" . $row->child_id);
                continue;
            }


            $new_parent_id = 0;
            if ($row->parent_id) {
                // see bug #20347
                // at least in the case of sessions and item groups parent_ids in container sorting are object IDs but $mappings store references
                if (in_array($row->parent_type, array("sess", "itgr"))) {
                    $par_refs = ilObject::_getAllReferences($row->parent_id);
                    $par_ref_id = current($par_refs);			// should be only one
                    $ilLog->debug("Got ref id: " . $par_ref_id . " for obj_id " . $row->parent_id . " map ref id: " . $mappings[$par_ref_id] . ".");
                    if (isset($mappings[$par_ref_id])) {
                        $new_parent_ref_id = $mappings[$par_ref_id];
                        $new_parent_id = ilObject::_lookupObjectId($new_parent_ref_id);
                    }
                } else {		// not sure if this is still used for other cases that expect ref ids
                    $new_parent_id = $mappings[$row->parent_id];
                }
                if ((int) $new_parent_id == 0) {
                    $ilLog->debug("No mapping found for parent id:" . $row->parent_id . ", child_id: " . $row->child_id);
                    continue;
                }
            }

            $query = "DELETE FROM container_sorting " .
                "WHERE obj_id = " . $ilDB->quote($target_obj_id, 'integer') . " " .
                "AND child_id = " . $ilDB->quote($mappings[$row->child_id], 'integer') . " " .
                "AND parent_type = " . $ilDB->quote($row->parent_type, 'text') . ' ' .
                "AND parent_id = " . $ilDB->quote((int) $new_parent_id, 'integer');
            $ilLog->debug($query);
            $ilDB->manipulate($query);
            
            // Add new value
            $query = "INSERT INTO container_sorting (obj_id,child_id,position,parent_type,parent_id) " .
                "VALUES( " .
                $ilDB->quote($target_obj_id, 'integer') . ", " .
                $ilDB->quote($mappings[$row->child_id], 'integer') . ", " .
                $ilDB->quote($row->position, 'integer') . ", " .
                $ilDB->quote($row->parent_type, 'text') . ", " .
                $ilDB->quote((int) $new_parent_id, 'integer') .
                ")";
            $ilLog->debug($query);
            $ilDB->manipulate($query);
        }
        return true;
    }
    
    
    
    /**
     * sort subitems
     *
     * @access public
     * @param array item data
     * @return array sorted item data
     */
    public function sortItems($a_items)
    {
        $sorted = array();
        if ($this->getSortingSettings()->getSortMode() != ilContainer::SORT_MANUAL) {
            switch ($this->getSortingSettings()->getSortMode()) {
                case ilContainer::SORT_TITLE:
                    foreach ((array) $a_items as $type => $data) {
                        // #16311 - sorting will remove keys (prev/next)
                        if ($type == 'sess_link') {
                            $sorted[$type] = $data;
                            continue;
                        }
                    
                        // this line used until #4389 has been fixed (3.10.6)
                        // reanimated with 4.4.0
                        $sorted[$type] = ilUtil::sortArray(
                            (array) $data,
                            'title',
                            ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                            false
                        );

                        // the next line tried to use db sorting and has replaced sortArray due to bug #4389
                        // but leads to bug #12165. PHP should be able to do a proper sorting, if the locale
                        // is set correctly, so we witch back to sortArray (with 4.4.0) and see what
                        // feedback we get
                        // (next line has been used from 3.10.6 to 4.3.x)
//						$sorted[$type] = $data;
                    }
                    return $sorted ? $sorted : array();
                    
                case ilContainer::SORT_ACTIVATION:
                    foreach ((array) $a_items as $type => $data) {
                        // #16311 - sorting will remove keys (prev/next)
                        if ($type == 'sess_link') {
                            $sorted[$type] = $data;
                            continue;
                        }
                    
                        $sorted[$type] = ilUtil::sortArray(
                            (array) $data,
                            'start',
                            ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                            true
                        );
                    }
                    return $sorted ? $sorted : array();
                    
                    
                case ilContainer::SORT_CREATION:
                    foreach ((array) $a_items as $type => $data) {
                        // #16311 - sorting will remove keys (prev/next)
                        if ($type == 'sess_link') {
                            $sorted[$type] = $data;
                            continue;
                        }
                    
                        $sorted[$type] = ilUtil::sortArray(
                            (array) $data,
                            'create_date',
                            ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                            true
                        );
                    }
                    return $sorted ? $sorted : array();
            }
            return $a_items;
        }
        if (!is_array($a_items) || !count($a_items)) {
            return $a_items;
        }
        $sorted = array();
        foreach ((array) $a_items as $type => $data) {
            if ($type == 'sess_link') {
                $sorted[$type] = $data;
                continue;
            }
            
            // Add position
            $items = array();
            foreach ((array) $data as $key => $item) {
                $items[$key] = $item;
                if (is_array($this->sorting['all']) and isset($this->sorting['all'][$item['child']])) {
                    $items[$key]['position'] = $this->sorting['all'][$item['child']];
                } else {
                    $items[$key]['position'] = self::ORDER_DEFAULT;
                }
            }

            $items = $this->sortOrderDefault($items);

            switch ($type) {
                case '_all':
                    $sorted[$type] = ilUtil::sortArray((array) $items, 'position', 'asc', true);
                    break;
                
                case '_non_sess':
                    $sorted[$type] = ilUtil::sortArray((array) $items, 'position', 'asc', true);
                    break;
                
                default:
                    $sorted[$type] = ilUtil::sortArray((array) $items, 'position', 'asc', true);
                    break;
            }
        }
        return $sorted ? $sorted : array();
    }
    
    /**
     * sort subitems (items of sessions or learning objectives)
     *
     * @access public
     * @param
     * @return
     */
    public function sortSubItems($a_parent_type, $a_parent_id, $a_items)
    {
        switch ($this->getSortingSettings()->getSortMode()) {
            case ilContainer::SORT_MANUAL:
                $items = array();
                foreach ($a_items as $key => $item) {
                    $items[$key] = $item;
                    $items[$key]['position'] = isset($this->sorting[$a_parent_type][$a_parent_id][$item['child']]) ?
                                                    $this->sorting[$a_parent_type][$a_parent_id][$item['child']] : self::ORDER_DEFAULT;
                }

                $items = $this->sortOrderDefault($items);
                return ilUtil::sortArray((array) $items, 'position', 'asc', true);
                

            case ilContainer::SORT_ACTIVATION:
                return ilUtil::sortArray(
                    (array) $a_items,
                    'start',
                    ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );

            case ilContainer::SORT_CREATION:
                return ilUtil::sortArray(
                    (array) $a_items,
                    'create_date',
                    ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );

            default:
            case ilContainer::SORT_TITLE:
                return ilUtil::sortArray(
                    (array) $a_items,
                    'title',
                    ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    false
                );
        }
    }
    
    /**
     * Save post
     *
     * @access public
     * @param array of positions e.g array(crs => array(1,2,3),'lres' => array(3,5,6))
     *
     */
    public function savePost($a_type_positions)
    {
        if (!is_array($a_type_positions)) {
            return false;
        }
        $items = [];
        foreach ($a_type_positions as $key => $position) {
            if ($key == "blocks") {
                $this->saveBlockPositions($position);
            } elseif (!is_array($position)) {
                $items[$key] = $position * 100;
            } else {
                foreach ($position as $parent_id => $sub_items) {
                    $this->saveSubItems($key, $parent_id, $sub_items ? $sub_items : array());
                }
            }
        }
        
        if (!count($items)) {
            return $this->saveItems(array());
        }
        
        asort($items);
        $new_indexed = [];
        $position = 0;
        foreach ($items as $key => $null) {
            $new_indexed[$key] = ++$position;
        }
        
        $this->saveItems($new_indexed);
    }
    
    
    /**
     * save items
     *
     * @access protected
     * @param string parent_type only used for sessions and objectives in the moment. Otherwise empty
     * @param int parent id
     * @param array array of items
     * @return
     */
    protected function saveItems($a_items)
    {
        $ilDB = $this->db;
        
        foreach ($a_items as $child_id => $position) {
            $ilDB->replace(
                'container_sorting',
                array(
                    'obj_id'	=> array('integer',$this->obj_id),
                    'child_id'	=> array('integer',$child_id),
                    'parent_id'	=> array('integer',0)
                ),
                array(
                    'parent_type' => array('text',''),
                    'position'	  => array('integer',$position)
                )
            );
        }
        return true;
    }
    
    /**
     * Save subitem ordering (sessions, learning objectives)
     * @param string $a_parent_type
     * @param integer $a_parent_id
     * @param array $a_items
     * @return
     */
    protected function saveSubItems($a_parent_type, $a_parent_id, $a_items)
    {
        $ilDB = $this->db;

        foreach ($a_items as $child_id => $position) {
            $ilDB->replace(
                'container_sorting',
                array(
                    'obj_id'	=> array('integer',$this->obj_id),
                    'child_id'	=> array('integer',$child_id),
                    'parent_id'	=> array('integer',$a_parent_id)
                ),
                array(
                    'parent_type' => array('text',$a_parent_type),
                    'position'	  => array('integer',$position)
                )
            );
        }
        return true;
    }
        
    /**
     * Save block custom positions (for current object id)
     *
     * @param array $a_values
     */
    protected function saveBlockPositions(array $a_values)
    {
        $ilDB = $this->db;
        
        asort($a_values);
        $ilDB->replace(
            'container_sorting_bl',
            array(
                'obj_id'	=> array('integer',$this->obj_id)
            ),
            array(
                'block_ids' => array('text', implode(";", array_keys($a_values)))
            )
        );
    }
    
    /**
     * Read block custom positions (for current object id)
     *
     * @return array
     */
    public function getBlockPositions()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT block_ids" .
            " FROM container_sorting_bl" .
            " WHERE obj_id = " . $ilDB->quote($this->obj_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($row["block_ids"]) {
            return explode(";", $row["block_ids"]);
        }
    }
    
    
    /**
     * Read
     *
     * @access private
     *
     */
    private function read()
    {
        $tree = $this->tree;
        
        if (!$this->obj_id) {
            $this->sorting_settings = new ilContainerSortingSettings();
            return true;
        }
        
        $sorting_settings = ilContainerSortingSettings::getInstanceByObjId($this->obj_id);
        $this->sorting_settings = $sorting_settings->loadEffectiveSettings();
        $query = "SELECT * FROM container_sorting " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " ORDER BY position";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->parent_id) {
                $this->sorting[$row->parent_type][$row->parent_id][$row->child_id] = $row->position;
            } else {
                $this->sorting['all'][$row->child_id] = $row->position;
            }
        }
        return true;
    }

    /**
     * Position and order sort order for new object without position in manual sorting type
     *
     * @param $items
     * @return array
     */
    private function sortOrderDefault($items)
    {
        $no_position = array();

        foreach ($items as $key => $item) {
            if ($item["position"] == self::ORDER_DEFAULT) {
                $no_position[]= array("key" => $key, "title" => $item["title"], "create_date" => $item["create_date"],
                    "start" => $item["start"]);
            }
        }

        if (!count($no_position)) {
            return $items;
        }

        switch ($this->getSortingSettings()->getSortNewItemsOrder()) {
            case ilContainer::SORT_NEW_ITEMS_ORDER_TITLE:
                $no_position = ilUtil::sortArray(
                    (array) $no_position,
                    'title',
                    ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );
                break;
            case ilContainer::SORT_NEW_ITEMS_ORDER_CREATION:
                $no_position = ilUtil::sortArray(
                    (array) $no_position,
                    'create_date',
                    ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );
                break;
            case ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION:
                $no_position = ilUtil::sortArray(
                    (array) $no_position,
                    'start',
                    ($this->getSortingSettings()->getSortDirection() == ilContainer::SORT_DIRECTION_ASC) ? 'asc' : 'desc',
                    true
                );

        }
        $count = $this->getSortingSettings()->getSortNewItemsPosition()
            == ilContainer::SORT_NEW_ITEMS_POSITION_TOP ? -900000 : 900000;

        foreach ($no_position as $values) {
            $items[$values["key"]]["position"] = $count;
            $count++;
        }
        return $items;
    }
}
