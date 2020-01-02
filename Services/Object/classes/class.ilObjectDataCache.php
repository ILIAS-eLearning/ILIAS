<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class ilObjectDataCache
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* This class caches some properties of the object_data table. Like title description owner obj_id
*
*/
class ilObjectDataCache
{
    public $db = null;
    public $reference_cache = array();
    public $object_data_cache = array();
    public $description_trans = array();

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->db = $ilDB;
    }

    public function deleteCachedEntry($a_obj_id)
    {
        unset($this->object_data_cache[$a_obj_id]);
    }

    public function lookupObjId($a_ref_id)
    {
        if (!$this->__isReferenceCached($a_ref_id)) {
            //echo"-objidmissed-$a_ref_id-";
            $obj_id = $this->__storeReference($a_ref_id);
            $this->__storeObjectData($obj_id);
        }
        return (int) @$this->reference_cache[$a_ref_id];
    }

    public function lookupTitle($a_obj_id)
    {
        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }
        return @$this->object_data_cache[$a_obj_id]['title'];
    }

    public function lookupType($a_obj_id)
    {
        if (!$this->__isObjectCached($a_obj_id)) {
            //echo"-typemissed-$a_obj_id-";
            $this->__storeObjectData($a_obj_id);
        }
        return @$this->object_data_cache[$a_obj_id]['type'];
    }

    public function lookupOwner($a_obj_id)
    {
        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }
        return @$this->object_data_cache[$a_obj_id]['owner'];
    }

    public function lookupDescription($a_obj_id)
    {
        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }
        return @$this->object_data_cache[$a_obj_id]['description'];
    }

    public function lookupLastUpdate($a_obj_id)
    {
        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }
        return @$this->object_data_cache[$a_obj_id]['last_update'];
    }

    /**
     * Check if supports centralized offline handling and is offline
     * @param $a_obj_id
     * @return bool
     */
    public function lookupOfflineStatus($a_obj_id)
    {
        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }
        return (bool) $this->object_data_cache[$a_obj_id]['offline'];
    }

    // PRIVATE

    /**
    * checks whether an reference id is already in cache or not
    *
    * @access	private
    * @param	int			$a_ref_id				reference id
    * @return	boolean
    */
    public function __isReferenceCached($a_ref_id)
    {
        #return false;
        #static $cached = 0;
        #static $not_cached = 0;

        if (@$this->reference_cache[$a_ref_id]) {
            #echo "Reference ". ++$cached ."cached<br>";
            return true;
        }
        #echo "Reference ". ++$not_cached ." not cached<br>";
        return false;
    }

    /**
    * checks whether an object is aleady in cache or not
    *
    * @access	private
    * @param	int			$a_obj_id				object id
    * @return	boolean
    */
    public function __isObjectCached($a_obj_id)
    {
        static $cached = 0;
        static $not_cached = 0;
            

        if (@$this->object_data_cache[$a_obj_id]) {
            #echo "Object ". ++$cached ."cached<br>";
            return true;
        }
        #echo "Object ". ++$not_cached ." not cached<br>";
        return false;
    }


    /**
    * Stores Reference in cache.
    * Maybe it could be useful to find all references of that object andd store them also in the cache.
    * But this would be an extra query.
    *
    * @access	private
    * @param	int			$a_ref_id				reference id
    * @return	int			$obj_id
    */
    public function __storeReference($a_ref_id)
    {
        $ilDB = $this->db;
        
        $query = "SELECT obj_id FROM object_reference WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->reference_cache[$a_ref_id] = $row['obj_id'];
        }
        return (int) @$this->reference_cache[$a_ref_id];
    }

    /**
    * Stores object data in cache
    *
    * @access	private
    * @param	int			$a_obj_id				object id
    * @return	bool
    */
    public function __storeObjectData($a_obj_id, $a_lang = "")
    {
        global $DIC;

        $ilDB = $this->db;
        $objDefinition = $DIC["objDefinition"];
        $ilUser = $DIC["ilUser"];
        
        if (is_object($ilUser) && $a_lang == "") {
            $a_lang = $ilUser->getLanguage();
        }

        $query = "SELECT * FROM object_data WHERE obj_id = " .
            $ilDB->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->object_data_cache[$a_obj_id]['title'] = $row->title;
            $this->object_data_cache[$a_obj_id]['description'] = $row->description;
            $this->object_data_cache[$a_obj_id]['type'] = $row->type;
            $this->object_data_cache[$a_obj_id]['owner'] = $row->owner;
            $this->object_data_cache[$a_obj_id]['last_update'] = $row->last_update;
            $this->object_data_cache[$a_obj_id]['offline'] = $row->offline;
            
            if (is_object($objDefinition)) {
                $translation_type = $objDefinition->getTranslationType($row->type);
            }

            if ($translation_type == "db") {
                if (!$this->trans_loaded[$a_obj_id]) {
                    $q = "SELECT title,description FROM object_translation " .
                         "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
                         "AND lang_code = " . $ilDB->quote($a_lang, 'text') . " " .
                         "AND NOT lang_default = 1";
                    $r = $ilDB->query($q);

                    $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
                    if ($row) {
                        $this->object_data_cache[$a_obj_id]['title'] = $row->title;
                        $this->object_data_cache[$a_obj_id]['description'] = $row->description;
                        $this->description_trans[] = $a_obj_id;
                    }
                    $this->trans_loaded[$a_obj_id] = true;
                }
            }
        }
        
        return true;
    }
    
    public function isTranslatedDescription($a_obj_id)
    {
        return (is_array($this->description_trans) &&
            in_array($a_obj_id, $this->description_trans));
    }
    
    /**
    * Stores object data in cache
    *
    * @access	private
    * @param	int			$a_obj_id				object id
    * @return	bool
    */
    public function preloadObjectCache($a_obj_ids, $a_lang = "")
    {
        global $DIC;

        $ilDB = $this->db;
        $objDefinition = $DIC["objDefinition"];
        $ilUser = $DIC["ilUser"];

        if (is_object($ilUser) && $a_lang == "") {
            $a_lang = $ilUser->getLanguage();
        }
        if (!is_array($a_obj_ids)) {
            return;
        }
        if (count($a_obj_ids) == 0) {
            return;
        }
        
        
        $query = "SELECT * FROM object_data " .
            "WHERE " . $ilDB->in('obj_id', $a_obj_ids, false, 'integer');
        $res = $ilDB->query($query);
        $db_trans = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {

            // this if fixes #9960
            if (!$this->trans_loaded[$row->obj_id]) {
                $this->object_data_cache[$row->obj_id]['title'] = $row->title;
                $this->object_data_cache[$row->obj_id]['description'] = $row->description;
            }
            $this->object_data_cache[$row->obj_id]['type'] = $row->type;
            $this->object_data_cache[$row->obj_id]['owner'] = $row->owner;
            $this->object_data_cache[$row->obj_id]['last_update'] = $row->last_update;
            $this->object_data_cache[$row->obj_id]['offline'] = $row->offline;

            if (is_object($objDefinition)) {
                $translation_type = $objDefinition->getTranslationType($row->type);
            }

            if ($translation_type == "db") {
                $db_trans[$row->obj_id] = $row->obj_id;
            }
        }
        if (count($db_trans) > 0) {
            $this->preloadTranslations($db_trans, $a_lang);
        }
    }

    /**
     * Preload translation informations
     *
     * @param	array	$a_obj_ids		array of object ids
     */
    public function preloadTranslations($a_obj_ids, $a_lang)
    {
        $ilDB = $this->db;

        $obj_ids = array();
        foreach ($a_obj_ids as $id) {
            // do not load an id more than one time
            if (!$this->trans_loaded[$id]) {
                $obj_ids[] = $id;
                $this->trans_loaded[$id] = true;
            }
        }
        if (count($obj_ids) > 0) {
            $q = "SELECT obj_id, title, description FROM object_translation " .
                 "WHERE " . $ilDB->in('obj_id', $obj_ids, false, 'integer') . " " .
                 "AND lang_code = " . $ilDB->quote($a_lang, 'text') . " " .
                 "AND NOT lang_default = 1";
            $r = $ilDB->query($q);
            while ($row2 = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->object_data_cache[$row2->obj_id]['title'] = $row2->title;
                $this->object_data_cache[$row2->obj_id]['description'] = $row2->description;
                $this->description_trans[] = $row2->obj_id;
            }
        }
    }

    public function preloadReferenceCache($a_ref_ids, $a_incl_obj = true)
    {
        $ilDB = $this->db;
        
        if (!is_array($a_ref_ids)) {
            return;
        }
        if (count($a_ref_ids) == 0) {
            return;
        }
        
        $query = "SELECT ref_id, obj_id FROM object_reference " .
            "WHERE " . $ilDB->in('ref_id', $a_ref_ids, false, 'integer');
        $res = $ilDB->query($query);
        
        $obj_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->reference_cache[$row['ref_id']] = $row['obj_id'];
            //echo "<br>store_ref-".$row['ref_id']."-".$row['obj_id']."-";
            $obj_ids[] = $row['obj_id'];
        }
        if ($a_incl_obj) {
            $this->preloadObjectCache($obj_ids);
        }
    }
}
