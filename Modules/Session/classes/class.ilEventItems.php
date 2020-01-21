<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* class ilEvent
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilEventItems.php 15697 2008-01-08 20:04:33Z hschottm $
*
*/


class ilEventItems
{
    public $ilErr;
    public $ilDB;
    public $tree;
    public $lng;

    public $event_id = null;
    public $items = array();


    public function __construct($a_event_id)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $tree = $DIC['tree'];

        $this->ilErr = $ilErr;
        $this->db  = $ilDB;
        $this->lng = $lng;

        $this->event_id = $a_event_id;
        $this->__read();
    }

    public function getEventId()
    {
        return $this->event_id;
    }
    public function setEventId($a_event_id)
    {
        $this->event_id = $a_event_id;
    }
    
    /**
     * get assigned items
     * @return array	$items	Assigned items.
     */
    public function getItems()
    {
        return $this->items ? $this->items : array();
    }
    public function setItems($a_items)
    {
        $this->items = array();
        foreach ($a_items as $item_id) {
            $this->items[] = (int) $item_id;
        }
    }
    
    
    /**
     * Add one item
     * @param object $a_item_ref_id
     * @return
     */
    public function addItem($a_item_ref_id)
    {
        $this->items[] = (int) $a_item_ref_id;
    }
    
    
    public function delete()
    {
        return ilEventItems::_delete($this->getEventId());
    }
    
    public static function _delete($a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM event_items " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Remove specific items from the DB.
     * @param $a_items array
     * @return bool
     */
    public function removeItems($a_items)
    {
        $query = "DELETE FROM event_items WHERE " . $this->db->in('item_id', $a_items, false, 'integer') .
            " AND event_id = " . $this->db->quote($this->event_id, 'integer');

        $res = $this->db->manipulate($query);

        return true;
    }
    
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->delete();
        
        foreach ($this->items as $item) {
            $query = "INSERT INTO event_items (event_id,item_id) " .
                "VALUES( " .
                $ilDB->quote($this->getEventId(), 'integer') . ", " .
                $ilDB->quote($item, 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }
        return true;
    }
    
    public static function _getItemsOfContainer($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];
        
        $session_nodes = $tree->getChildsByType($a_ref_id, 'sess');
        $session_ids = [];
        foreach ($session_nodes as $node) {
            $session_ids[] = $node['obj_id'];
        }
        $query = "SELECT item_id FROM event_items " .
            "WHERE " . $ilDB->in('event_id', $session_ids, false, 'integer');
            

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_id;
        }
        return $items ? $items : array();
    }
    
    /**
     * Get items by event
     *
     * @access public
     * @static
     *
     * @param int event id
     */
    public static function _getItemsOfEvent($a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM event_items " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_id;
        }
        return $items ? $items : array();
    }

    public function _isAssigned($a_item_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_items " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " ";
        $res = $ilDB->query($query);

        return $res->numRows() ? true : false;
    }

    /**
     * @param int $item_ref_id
     * @return int[]
     */
    public static function getEventsForItemOrderedByStartingTime($item_ref_id)
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'SELECT e.event_id,e_start FROM event_items e ' .
            'JOIN event_appointment ea ON e.event_id = ea.event_id ' .
            'WHERE item_id = ' . $db->quote($item_ref_id, 'integer') . ' ' .
            'ORDER BY (e_start)';
        $res = $db->query($query);

        $events = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $dt = new ilDateTime($row->e_start, IL_CAL_DATETIME);
            $events[$row->event_id] = $dt->getUnixTime();
        }
        return $events;
    }

    
    /**
     * Clone items
     *
     * @access public
     *
     * @param int source event id
     * @param int copy id
     */
    public function cloneItems($a_source_id, $a_copy_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilLog = $DIC->logger()->sess();
        
        $ilLog->debug('Begin cloning session materials ...');
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        
        $new_items = array();
        foreach (ilEventItems::_getItemsOfEvent($a_source_id) as $item_id) {
            if (isset($mappings[$item_id]) and $mappings[$item_id]) {
                $ilLog->debug('Clone session material nr. ' . $item_id);
                $new_items[] = $mappings[$item_id];
            } else {
                $ilLog->debug('No mapping found for session material nr. ' . $item_id);
            }
        }
        $this->setItems($new_items);
        $this->update();
        $ilLog->debug('Finished cloning session materials ...');
        return true;
    }


    // PRIVATE
    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];
        
        $query = "SELECT * FROM event_items " .
            "WHERE event_id = " . $ilDB->quote($this->getEventId(), 'integer') . " ";

        $res = $this->db->query($query);
        $this->items = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($tree->isDeleted($row->item_id)) {
                continue;
            }
            if (!$tree->isInTree($row->item_id)) {
                $query = "DELETE FROM event_items " .
                    "WHERE item_id = " . $ilDB->quote($row->item_id, 'integer');
                $ilDB->manipulate($query);
                continue;
            }
            
            $this->items[] = (int) $row->item_id;
        }
        return true;
    }
}
