<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLinkResourceItems
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilLinkResourceItems
{
    protected ilDBInterface $db;

    protected int $webr_id;

    public function __construct($webr_id)
    {
        global $DIC;

        $this->webr_id = $webr_id;

        $this->db = $DIC->database();
    }
    
    public function cloneItems(int $source_id, int $a_new_id) : bool
    {
        $appender = new ilParameterAppender($source_id);
        
        foreach ($this->getAllItems() as $item) {
            $new_item = new ilLinkResourceItem($a_new_id);
            $new_item->setWebResourceId($this->getWebrId());
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
    
    public static function getAllItemIds(int $a_webr_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT link_id FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $link_ids[] = $row['link_id'];
        }
        return $link_ids ?? array();
    }
        
    public function getAllItems() : array
    {
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $this->db->quote($this->getWebrId(), ilDBConstants::T_INTEGER);

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
        return $items ?? array();
    }

    public function sortItems(array $a_items)
    {
        $mode = ilContainerSortingSettings::_lookupSortMode($this->getWebrId());
        
        if ($mode == ilContainer::SORT_TITLE) {
            $a_items = ilUtil::sortArray($a_items, 'title', 'asc', false, true);
            return $a_items;
        }

        if ($mode == ilContainer::SORT_MANUAL) {
            $pos = ilContainerSorting::lookupPositions($this->getWebrId());
            foreach ($a_items as $link_id => $item) {
                if (isset($pos[$link_id])) {
                    $sorted[$link_id] = $item;
                    $sorted[$link_id]['position'] = $pos[$link_id];
                } else {
                    $unsorted[$link_id] = $item;
                }
            }
            $sorted = ilUtil::sortArray($sorted ?? array(), 'position', 'asc', true, true);
            $unsorted = ilUtil::sortArray( $unsorted ?? array(), 'title', 'asc', false, true);
            $a_items = $sorted + $unsorted;
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
        return $active_items ?? array();
    }

    public function getCheckItems($a_offset = 0)
    {
        $time = time() - $a_offset;

        foreach ($this->getAllItems() as $id => $item_data) {
            if (!$item_data['disable_check']) {
                if (!$item_data['last_check'] or $item_data['last_check'] < $time) {
                    $check_items[$id] = $item_data;
                }
            }
        }
        return $check_items ?? array();
    }
        


    // STATIC
    public static function _deleteAll($webr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate("DELETE FROM webr_items WHERE webr_id = " . $ilDB->quote($webr_id, ilDBConstants::T_INTEGER));

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

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, ilDBConstants::T_INTEGER) . ' ' .
            "AND active = " . $ilDB->quote(1, ilDBConstants::T_INTEGER) . ' ';
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

        $ilDB = $DIC->database();
        
        $query = "SELECT COUNT(*) num FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->num;
    }

    /**
    * Get first link item
    * Check before with _isSingular() if there is more or less than one
    *
    * @param	int			$a_webr_id		object id of web resource
    * @return ilLinkResourceItem link item data
    *
    */
    public static function _getFirstLink($a_webr_id)
    {
        return ilObjLinkResourceAccess::_getFirstLink($a_webr_id);
    }

    public function toXML(ilXmlWriter $writer)
    {
        $items = $this->sortItems($this->getAllItems());
        
        $position = 0;
        foreach ($items as $item_id => $item) {
            ++$position;
            $link = new ilLinkResourceItem($item_id);
            
            $writer->xmlStartTag(
                'WebLink',
                array(
                    'id' => $link->getLinkId(),
                    'active' => $link->getActiveStatus(),
                    'valid' => $link->getValidStatus(),
                    'disableValidation' => $link->getDisableCheckStatus(),
                    'position' => $position,
                    'internal' => $link->getInternal()
                )
            );
            $writer->xmlElement('Title', array(), $link->getTitle());
            $writer->xmlElement('Description', array(), $link->getDescription());
            $writer->xmlElement('Target', array(), $link->getTarget());
            
            // Dynamic parameters
            foreach (ilParameterAppender::_getParams($link->getLinkId()) as $param_id => $param) {
                $value = '';
                switch ($param['value']) {
                    case ilParameterAppender::LINKS_USER_ID:
                        $value = 'userId';
                        break;
                    
                    case ilParameterAppender::LINKS_LOGIN:
                        $value = 'userName';
                        break;
                        
                    case ilParameterAppender::LINKS_MATRICULATION:
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

    public function getWebrId() : int
    {
        return $this->webr_id;
    }

    public function setWebrId(int $webr_id) : void
    {
        $this->webr_id = $webr_id;
    }
}
