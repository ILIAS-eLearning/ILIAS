<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjLinkResourceGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilLinkResourceItems
{
    /**
    * Constructor
    * @access public
    */
    public function __construct($webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->webr_ref_id = 0;
        $this->webr_id = $webr_id;

        $this->db = $ilDB;
    }
    
    // BEGIN PATCH Lucene search
    public static function lookupItem($a_webr_id, $a_link_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . " " .
            "AND link_id = " . $ilDB->quote($a_link_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title'] = $row->title;
            $item['description'] = $row->description;
            $item['target'] = $row->target;
            $item['active'] = (bool) $row->active;
            $item['disable_check'] = $row->disable_check;
            $item['create_date'] = $row->create_date;
            $item['last_update'] = $row->last_update;
            $item['last_check'] = $row->last_check;
            $item['valid'] = $row->valid;
            $item['link_id'] = $row->link_id;
            $item['internal'] = $row->internal;
        }
        return $item ? $item : array();
    }
    // END PATCH Lucene Search
    
    /**
     * Update title
     * @param type $a_link_id
     * @param type $a_title
     */
    public static function updateTitle($a_link_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'UPDATE webr_items SET ' .
                'title = ' . $ilDB->quote($a_title, 'text') . ' ' .
                'WHERE link_id = ' . $ilDB->quote($a_link_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    
    

    // SET GET
    public function setLinkResourceRefId($a_ref_id)
    {
        $this->webr_ref_id = $a_ref_id;
    }
    public function getLinkResourceRefId()
    {
        return $this->webr_ref_id;
    }
    public function setLinkResourceId($a_id)
    {
        $this->webr_id = $a_id;
    }
    public function getLinkResourceId()
    {
        return $this->webr_id;
    }
    public function setLinkId($a_id)
    {
        $this->id = $a_id;
    }
    public function getLinkId()
    {
        return $this->id;
    }
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setTarget($a_target)
    {
        $this->target = $a_target;
    }
    public function getTarget()
    {
        return $this->target;
    }
    public function setActiveStatus($a_status)
    {
        $this->status = (int) $a_status;
    }
    public function getActiveStatus()
    {
        return (bool) $this->status;
    }
    public function setDisableCheckStatus($a_status)
    {
        $this->check = (int) $a_status;
    }
    public function getDisableCheckStatus()
    {
        return (bool) $this->check;
    }
    // PRIVATE
    public function __setCreateDate($a_date)
    {
        $this->c_date = $a_date;
    }
    public function getCreateDate()
    {
        return $this->c_date;
    }
    // PRIVATE
    public function __setLastUpdateDate($a_date)
    {
        $this->m_date = $a_date;
    }
    public function getLastUpdateDate()
    {
        return $this->m_date;
    }
    public function setLastCheckDate($a_date)
    {
        $this->last_check = $a_date;
    }
    public function getLastCheckDate()
    {
        return $this->last_check;
    }
    public function setValidStatus($a_status)
    {
        $this->valid = (int) $a_status;
    }
    public function getValidStatus()
    {
        return (bool) $this->valid;
    }
    public function setInternal($a_status)
    {
        $this->internal = (bool) $a_status;
    }
    public function getInternal()
    {
        return (bool) $this->internal;
    }
    
    /**
     * Copy web resource items
     *
     * @access public
     * @param int obj_id of new object
     *
     */
    public function cloneItems($a_new_id)
    {
        include_once 'Modules/WebResource/classes/class.ilParameterAppender.php';
        $appender = new ilParameterAppender($this->getLinkResourceId());
        
        foreach ($this->getAllItems() as $item) {
            $new_item = new ilLinkResourceItems($a_new_id);
            $new_item->setTitle($item['title']);
            $new_item->setDescription($item['description']);
            $new_item->setTarget($item['target']);
            $new_item->setActiveStatus($item['active']);
            $new_item->setDisableCheckStatus($item['disable_check']);
            $new_item->setLastCheckDate($item['last_check']);
            $new_item->setValidStatus($item['valid']);
            $new_item->setInternal($item['internal']);
            $new_item->add(true);

            // Add parameters
            foreach (ilParameterAppender::_getParams($item['link_id']) as $param_id => $data) {
                $appender->setName($data['name']);
                $appender->setValue($data['value']);
                $appender->add($new_item->getLinkId());
            }

            unset($new_item);
        }
        return true;
    }

    public function delete($a_item_id, $a_update_history = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $item = $this->getItem($a_item_id);
        
        $query = "DELETE FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer') . " " .
            "AND link_id = " . $ilDB->quote($a_item_id, 'integer');
        $res = $ilDB->manipulate($query);

        if ($a_update_history) {
            include_once("./Services/History/classes/class.ilHistory.php");
            ilHistory::_createEntry(
                $this->getLinkResourceId(),
                "delete",
                $item['title']
            );
        }

        return true;
    }

    public function update($a_update_history = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getLinkId()) {
            return false;
        }

        $this->__setLastUpdateDate(time());
        $query = "UPDATE webr_items " .
            "SET title = " . $ilDB->quote($this->getTitle(), 'text') . ", " .
            "description = " . $ilDB->quote($this->getDescription(), 'text') . ", " .
            "target = " . $ilDB->quote($this->getTarget(), 'text') . ", " .
            "active = " . $ilDB->quote($this->getActiveStatus(), 'integer') . ", " .
            "valid = " . $ilDB->quote($this->getValidStatus(), 'integer') . ", " .
            "disable_check = " . $ilDB->quote($this->getDisableCheckStatus(), 'integer') . ", " .
            "internal = " . $ilDB->quote($this->getInternal(), 'integer') . ", " .
            "last_update = " . $ilDB->quote($this->getLastUpdateDate(), 'integer') . ", " .
            "last_check = " . $ilDB->quote($this->getLastCheckDate(), 'integer') . " " .
            "WHERE link_id = " . $ilDB->quote($this->getLinkId(), 'integer') . " " .
            "AND webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer');
        $res = $ilDB->manipulate($query);
        
        if ($a_update_history) {
            include_once("./Services/History/classes/class.ilHistory.php");
            ilHistory::_createEntry(
                $this->getLinkResourceId(),
                "update",
                $this->getTitle()
            );
        }

        return true;
    }

    public function updateValid($a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE webr_items " .
            "SET valid = " . $ilDB->quote($a_status, 'integer') . " " .
            "WHERE link_id = " . $ilDB->quote($this->getLinkId(), 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function updateActive($a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE webr_items " .
            "SET active = " . $ilDB->quote($a_status, 'integer') . " " .
            "WHERE link_id = " . $ilDB->quote($this->getLinkId(), 'integer');

        $this->db->query($query);

        return true;
    }
    public function updateDisableCheck($a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE webr_items " .
            "SET disable_check = " . $ilDB->quote($a_status, 'integer') . " " .
            "WHERE link_id = " . $ilDB->quote($this->getLinkId(), 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function updateLastCheck($a_offset = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($a_offset) {
            $period = $a_offset ? $a_offset : 0;
            $time = time() - $period;
            
            
            $query = "UPDATE webr_items " .
                "SET last_check = " . $ilDB->quote(time(), 'integer') . " " .
                "WHERE webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer') . " " .
                "AND disable_check = '0' " .
                "AND last_check < " . $ilDB->quote($time, 'integer');
            $res = $ilDB->manipulate($query);
        } else {
            $query = "UPDATE webr_items " .
                "SET last_check = " . $ilDB->quote(time(), 'integer') . " " .
                "WHERE webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer') . " " .
                "AND disable_check = '0' ";
            $res = $ilDB->manipulate($query);
        }
        return true;
    }

    public function updateValidByCheck($a_offset = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($a_offset) {
            $period = $a_offset ? $a_offset : 0;
            $time = time() - $period;
            
            
            $query = "UPDATE webr_items " .
                "SET valid = '1' " .
                "WHERE disable_check = '0' " .
                "AND webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer') . " " .
                "AND last_check < " . $ilDB->quote($time, 'integer');
            $res = $ilDB->manipulate($query);
        } else {
            $query = "UPDATE webr_items " .
                "SET valid = '1' " .
                "WHERE disable_check = '0' " .
                "AND webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer');
            $res = $ilDB->manipulate($query);
        }
        return true;
    }


    public function add($a_update_history = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->__setLastUpdateDate(time());
        $this->__setCreateDate(time());

        $next_id = $ilDB->nextId('webr_items');
        $query = "INSERT INTO webr_items (link_id,title,description,target,active,disable_check," .
            "last_update,create_date,webr_id,valid,internal) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getTitle(), 'text') . ", " .
            $ilDB->quote($this->getDescription(), 'text') . ", " .
            $ilDB->quote($this->getTarget(), 'text') . ", " .
            $ilDB->quote($this->getActiveStatus(), 'integer') . ", " .
            $ilDB->quote($this->getDisableCheckStatus(), 'integer') . ", " .
            $ilDB->quote($this->getLastUpdateDate(), 'integer') . ", " .
            $ilDB->quote($this->getCreateDate(), 'integer') . ", " .
            $ilDB->quote($this->getLinkResourceId(), 'integer') . ", " .
            $ilDB->quote($this->getValidStatus(), 'integer') . ', ' .
            $ilDB->quote($this->getInternal(), 'integer') . ' ' .
            ")";
        $res = $ilDB->manipulate($query);

        $link_id = $next_id;
        $this->setLinkId($link_id);
        
        if ($a_update_history) {
            include_once("./Services/History/classes/class.ilHistory.php");
            ilHistory::_createEntry(
                $this->getLinkResourceId(),
                "add",
                $this->getTitle()
            );
        }

        return $link_id;
    }
    public function readItem($a_link_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM webr_items " .
            "WHERE link_id = " . $ilDB->quote($a_link_id, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setTarget($row->target);
            $this->setActiveStatus($row->active);
            $this->setDisableCheckStatus($row->disable_check);
            $this->__setCreateDate($row->create_date);
            $this->__setLastUpdateDate($row->last_update);
            $this->setLastCheckDate($row->last_check);
            $this->setValidStatus($row->valid);
            $this->setLinkId($row->link_id);
            $this->setInternal($row->internal);
        }
        return true;
    }


    public function getItem($a_link_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer') . " " .
            "AND link_id = " . $ilDB->quote($a_link_id, 'integer');
            
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title'] = $row->title;
            $item['description'] = $row->description;
            $item['target'] = $row->target;
            $item['active'] = (bool) $row->active;
            $item['disable_check'] = $row->disable_check;
            $item['create_date'] = $row->create_date;
            $item['last_update'] = $row->last_update;
            $item['last_check'] = $row->last_check;
            $item['valid'] = $row->valid;
            $item['link_id'] = $row->link_id;
            $item['internal'] = $row->internal;
        }
        return $item ? $item : array();
    }
    
    /**
     * Get all link ids
     * @param int $a_webr_id
     * @return
     */
    public static function getAllItemIds($a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT link_id FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $link_ids[] = $row['link_id'];
        }
        return (array) $link_ids;
    }
        
    public function getAllItems()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($this->getLinkResourceId(), 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[$row->link_id]['title'] = $row->title;
            $items[$row->link_id]['description'] = $row->description;
            $items[$row->link_id]['target'] = $row->target;
            $items[$row->link_id]['active'] = (bool) $row->active;
            $items[$row->link_id]['disable_check'] = $row->disable_check;
            $items[$row->link_id]['create_date'] = $row->create_date;
            $items[$row->link_id]['last_update'] = $row->last_update;
            $items[$row->link_id]['last_check'] = $row->last_check;
            $items[$row->link_id]['valid'] = $row->valid;
            $items[$row->link_id]['link_id'] = $row->link_id;
            $items[$row->link_id]['internal'] = $row->internal;
        }
        return $items ? $items : array();
    }
    
    /**
     * Sort items (sorting mode depends on sorting setting)
     * @param object $a_items
     * @return
     */
    public function sortItems($a_items)
    {
        include_once './Services/Container/classes/class.ilContainer.php';
        include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
        $mode = ilContainerSortingSettings::_lookupSortMode($this->getLinkResourceId());
        
        if ($mode == ilContainer::SORT_TITLE) {
            $a_items = ilUtil::sortArray($a_items, 'title', 'asc', false, true);
            return $a_items;
        }
    
    
        if ($mode == ilContainer::SORT_MANUAL) {
            include_once './Services/Container/classes/class.ilContainerSorting.php';
            $pos = ilContainerSorting::lookupPositions($this->getLinkResourceId());
            foreach ($a_items as $link_id => $item) {
                if (isset($pos[$link_id])) {
                    $sorted[$link_id] = $item;
                    $sorted[$link_id]['position'] = $pos[$link_id];
                } else {
                    $unsorted[$link_id] = $item;
                }
            }
            $sorted = ilUtil::sortArray((array) $sorted, 'position', 'asc', true, true);
            $unsorted = ilUtil::sortArray((array) $unsorted, 'title', 'asc', false, true);
            $a_items = (array) $sorted + (array) $unsorted;
            return $a_items;
        }
        return $a_items;
    }
    
    
    
    public function getActivatedItems()
    {
        foreach ($this->getAllItems() as $id => $item_data) {
            if ($item_data['active']) {
                $active_items[$id] = $item_data;
            }
        }
        return $active_items ? $active_items : array();
    }

    public function getCheckItems($a_offset = 0)
    {
        $period = $a_offset ? $a_offset : 0;
        $time = time() - $period;

        foreach ($this->getAllItems() as $id => $item_data) {
            if (!$item_data['disable_check']) {
                if (!$item_data['last_check'] or $item_data['last_check'] < $time) {
                    $check_items[$id] = $item_data;
                }
            }
        }
        return $check_items ? $check_items : array();
    }
        


    // STATIC
    public static function _deleteAll($webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $ilDB->manipulate("DELETE FROM webr_items WHERE webr_id = " . $ilDB->quote($webr_id, 'integer'));

        return true;
    }

    /**
    * Check whether there is only one active link in the web resource.
    * In this case this link is shown in a new browser window
    *
    * @param	int			$a_webr_id		object id of web resource
    * @return   boolean		success status
    *
    */
    public static function _isSingular($a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . ' ' .
            "AND active = " . $ilDB->quote(1, 'integer') . ' ';
        $res = $ilDB->query($query);
        return $res->numRows() == 1 ? true : false;
    }
    
    /**
     * Get number of assigned links
     * @param int $a_webr_id
     * @return
     */
    public static function lookupNumberOfLinks($a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT COUNT(*) num FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->num;
    }

    /**
    * Get first link item
    * Check before with _isSingular() if there is more or less than one
    *
    * @param	int			$a_webr_id		object id of web resource
    * @return array link item data
    *
    */
    public static function _getFirstLink($a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once("./Modules/WebResource/classes/class.ilObjLinkResourceAccess.php");
        return ilObjLinkResourceAccess::_getFirstLink($a_webr_id);
    }
    
    /**
     * Validate required settings
     * @return
     */
    public function validate()
    {
        return $this->getTarget() and $this->getTitle();
    }


    /**
     * Write link XML
     * @param ilXmlWriter $writer
     * @return
     */
    public function toXML(ilXmlWriter $writer)
    {
        $items = $this->sortItems($this->getAllItems());
        
        $position = 0;
        foreach ((array) $items as $item_id => $item) {
            ++$position;
            $link = self::lookupItem($this->getLinkResourceId(), $item_id);
            
            $writer->xmlStartTag(
                'WebLink',
                array(
                    'id' => $link['link_id'],
                    'active' => $link['active'] ? 1 : 0,
                    'valid' => $link['valid'] ? 1 : 0,
                    'disableValidation' => $link['disable_check'] ? 1 : 0,
                    'position' => $position,
                    'internal' => $link['internal']
                )
            );
            $writer->xmlElement('Title', array(), $link['title']);
            $writer->xmlElement('Description', array(), $link['description']);
            $writer->xmlElement('Target', array(), $link['target']);
            
            // Dynamic parameters
            include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
            foreach (ilParameterAppender::_getParams($link_id) as $param_id => $param) {
                $value = '';
                switch ($param['value']) {
                    case LINKS_USER_ID:
                        $value = 'userId';
                        break;
                    
                    case LINKS_LOGIN:
                        $value = 'userName';
                        break;
                        
                    case LINKS_MATRICULATION:
                        $value = 'matriculation';
                        break;
                }
                
                if (!$value) {
                    // Fix for deprecated LINKS_SESSION
                    continue;
                }

                $writer->xmlElement(
                    'DynamicParameter',
                    array(
                        'id' => $param_id,
                        'name' => $param['name'],
                        'type' => $value
                    )
                );
            }
            
            $writer->xmlEndTag('WebLink');
        }
        return true;
    }
}
