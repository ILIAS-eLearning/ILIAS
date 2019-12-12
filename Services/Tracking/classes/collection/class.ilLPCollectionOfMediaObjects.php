<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Tracking/classes/collection/class.ilLPCollection.php";

/**
* LP collection of media objects
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPCollections.php 40326 2013-03-05 11:39:24Z jluetzen $
*
* @ingroup ServicesTracking
*/
class ilLPCollectionOfMediaObjects extends ilLPCollection
{
    protected static $possible_items = array();
        
    public function getPossibleItems()
    {
        if (!isset(self::$possible_items[$this->obj_id])) {
            $items = array();
                        
            include_once "Modules/MediaCast/classes/class.ilObjMediaCast.php";
            $cast = new ilObjMediaCast($this->obj_id, false);
            
            foreach ($cast->getSortedItemsArray() as $item) {
                $items[$item["mob_id"]] = array("title"=>$item["title"]);
            }
            
            self::$possible_items[$this->obj_id] = $items;
        }
        
        return self::$possible_items[$this->obj_id];
    }
    
    
    //
    // TABLE GUI
    //
    
    public function getTableGUIData($a_parent_ref_id)
    {
        $data = array();
        
        foreach ($this->getPossibleItems() as $mob_id => $item) {
            $tmp = array();
            $tmp['id'] = $mob_id;
            $tmp['ref_id'] = 0;
            $tmp['type'] = 'mob';
            $tmp['title'] = $item['title'];
            $tmp['status'] = $this->isAssignedEntry($mob_id);
                            
            $data[] = $tmp;
        }
    
        return $data;
    }

    /**
     * Scorm items are not copied, they are newly created by reading the manifest.
     * Therefore, they do not have a mapping. So we need to map them via the import_id/identifierref
     *
     * @param $a_target_id
     * @param $a_copy_id
     */
    public function cloneCollection($a_target_id, $a_copy_id, $mob_mapping = null)
    {
        global $DIC;

        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $new_collection = new static($target_obj_id, $this->mode);
        $possible_items = $new_collection->getPossibleItems();
        foreach ($this->items as $item_id) {
            if (isset($mob_mapping[$item_id]) && isset($possible_items[$mob_mapping[$item_id]])) {
                $new_collection->addEntry($mob_mapping[$item_id]);
            }
        }

        $DIC->logger()->root()->write(__METHOD__ . ': cloned learning progress collection.');
    }
}
