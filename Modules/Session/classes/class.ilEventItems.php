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
 ********************************************************************
 */

/**
* class ilEventItems
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilEventItems.php 15697 2008-01-08 20:04:33Z hschottm $
*
*/
class ilEventItems
{
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected int $event_id = 0;
    protected array $items = [];


    public function __construct(int $a_event_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();

        $this->event_id = $a_event_id;
        $this->__read();
    }

    public function getEventId() : int
    {
        return $this->event_id;
    }

    public function setEventId(int $a_event_id) : void
    {
        $this->event_id = $a_event_id;
    }

    public function getItems() : array
    {
        return $this->items;
    }

    public function setItems(array $a_items) : void
    {
        $this->items = [];
        foreach ($a_items as $item_id) {
            $this->items[] = (int) $item_id;
        }
    }

    public function addItem(int $a_item_ref_id) : void
    {
        $this->items[] = $a_item_ref_id;
    }

    public function delete() : bool
    {
        return self::_delete($this->getEventId());
    }
    
    public static function _delete(int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM event_items " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }

    public function removeItems(array $a_items) : bool
    {
        $query = "DELETE FROM event_items WHERE " . $this->db->in('item_id', $a_items, false, 'integer') .
            " AND event_id = " . $this->db->quote($this->event_id, 'integer');

        $res = $this->db->manipulate($query);

        return true;
    }
    
    public function update() : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        
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
    
    public static function _getItemsOfContainer(int $a_ref_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        
        $session_nodes = $tree->getChildsByType($a_ref_id, 'sess');
        $session_ids = [];
        foreach ($session_nodes as $node) {
            $session_ids[] = $node['obj_id'];
        }
        $query = "SELECT item_id FROM event_items " .
            "WHERE " . $ilDB->in('event_id', $session_ids, false, 'integer');
            

        $res = $ilDB->query($query);
        $items = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_id;
        }
        return $items;
    }

    public static function _getItemsOfEvent(int $a_event_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM event_items " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer');
        $res = $ilDB->query($query);
        $items = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_id;
        }
        return $items;
    }

    public function _isAssigned(int $a_item_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_items " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " ";
        $res = $ilDB->query($query);

        return $res->numRows() ? true : false;
    }

    /**
     * @param int $item_ref_id
     * @return int[]
     */
    public static function getEventsForItemOrderedByStartingTime(int $item_ref_id) : array
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

    public function cloneItems(int $a_source_id, int $a_copy_id) : bool
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilLog = $DIC->logger()->root();
        
        $ilLog->debug('Begin cloning session materials ...');

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        
        $new_items = [];
        foreach (ilEventItems::_getItemsOfEvent($a_source_id) as $item_id) {
            if (isset($mappings[$item_id]) && $mappings[$item_id]) {
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

    protected function __read() : bool
    {
        global $DIC;

        $ilDB = $this->db;
        $tree = $this->tree;
        
        $query = "SELECT * FROM event_items " .
            "WHERE event_id = " . $ilDB->quote($this->getEventId(), 'integer') . " ";

        $res = $this->db->query($query);
        $this->items = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($tree->isDeleted((int) $row->item_id)) {
                continue;
            }
            if (!$tree->isInTree((int) $row->item_id)) {
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
