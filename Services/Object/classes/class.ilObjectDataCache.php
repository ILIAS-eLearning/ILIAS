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
    /** @var array<int, bool> */
    protected array $trans_loaded = [];

    public ilDBInterface $db;
    public array $reference_cache = [];
    public array $object_data_cache = [];
    public array $description_trans = [];

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->db = $ilDB;
    }

    public function deleteCachedEntry(int $a_obj_id) : void
    {
        if (isset($this->object_data_cache[$a_obj_id])) {
            unset($this->object_data_cache[$a_obj_id]);
        }
    }

    public function lookupObjId($a_ref_id) : int
    {
        $a_ref_id = (int) $a_ref_id;

        if (!$this->__isReferenceCached($a_ref_id)) {
            $obj_id = $this->__storeReference($a_ref_id);
            $this->__storeObjectData($obj_id);
        }

        return (int) ($this->reference_cache[$a_ref_id] ?? 0);
    }

    public function lookupTitle($a_obj_id) : string
    {
        $a_obj_id = (int) $a_obj_id;

        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }

        return (string) ($this->object_data_cache[$a_obj_id]['title'] ?? '');
    }

    public function lookupType($a_obj_id) : string
    {
        $a_obj_id = (int) $a_obj_id;

        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }

        return (string) ($this->object_data_cache[$a_obj_id]['type'] ?? '');
    }

    public function lookupOwner($a_obj_id)
    {
        $a_obj_id = (int) $a_obj_id;

        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }

        return @$this->object_data_cache[$a_obj_id]['owner'];
    }

    public function lookupDescription($a_obj_id) : string
    {
        $a_obj_id = (int) $a_obj_id;

        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }

        return (string) ($this->object_data_cache[$a_obj_id]['description'] ?? '');
    }

    public function lookupLastUpdate($a_obj_id)
    {
        $a_obj_id = (int) $a_obj_id;

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
    public function lookupOfflineStatus($a_obj_id) : bool
    {
        $a_obj_id = (int) $a_obj_id;

        if (!$this->__isObjectCached($a_obj_id)) {
            $this->__storeObjectData($a_obj_id);
        }

        return (bool) ($this->object_data_cache[$a_obj_id]['offline'] ?? false);
    }

    // PRIVATE

    /**
     * checks whether an reference id is already in cache or not
     * @param int $a_ref_id
     * @return bool
     */
    private function __isReferenceCached(int $a_ref_id) : bool
    {
        if (isset($this->reference_cache[$a_ref_id])) {
            return true;
        }

        return false;
    }

    /**
     * checks whether an object is aleady in cache or not
     * @param int $a_obj_id
     * @return bool
     */
    private function __isObjectCached(int $a_obj_id) : bool
    {
        if (isset($this->object_data_cache[$a_obj_id])) {
            return true;
        }

        return false;
    }

    /**
     * Stores Reference in cache.
     * Maybe it could be useful to find all references of that object andd store them also in the cache.
     * But this would be an extra query.
     * @param int $a_ref_id
     * @return int
     */
    private function __storeReference(int $a_ref_id) : int
    {
        $ilDB = $this->db;

        $query = "SELECT obj_id FROM object_reference WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->reference_cache[$a_ref_id] = (int) $row['obj_id'];
        }

        return (int) ($this->reference_cache[$a_ref_id] ?? 0);
    }

    /**
     * Stores object data in cache
     * @param int $a_obj_id
     * @param string $a_lang
     * @return bool
     */
    private function __storeObjectData(int $a_obj_id, string $a_lang = "") : bool
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

            $translation_type = '';
            if (is_object($objDefinition)) {
                $translation_type = $objDefinition->getTranslationType($row->type);
            }

            if ($translation_type === "db" && !isset($this->trans_loaded[$a_obj_id])) {
                $q = "SELECT title, description FROM object_translation " .
                    "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
                    "AND lang_code = " . $ilDB->quote($a_lang, 'text') . " " .
                    "AND NOT lang_default = 1";
                $trans_res = $ilDB->query($q);

                $trans_row = $trans_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
                if ($trans_row) {
                    $this->object_data_cache[$a_obj_id]['title'] = $trans_row->title;
                    $this->object_data_cache[$a_obj_id]['description'] = $trans_row->description;
                    $this->description_trans[] = $a_obj_id;
                }
                $this->trans_loaded[$a_obj_id] = true;
            }
        }

        return true;
    }

    public function isTranslatedDescription($a_obj_id) : bool
    {
        return is_array($this->description_trans) && in_array($a_obj_id, $this->description_trans);
    }

    /**
     * Stores object data in cache
     * @param int[] $a_obj_ids
     * @param string $a_lang
     */
    public function preloadObjectCache(array $a_obj_ids, string $a_lang = '') : void
    {
        global $DIC;

        $ilDB = $this->db;
        $objDefinition = $DIC["objDefinition"];
        $ilUser = $DIC["ilUser"];

        if (is_object($ilUser) && $a_lang == "") {
            $a_lang = $ilUser->getLanguage();
        }

        if ($a_obj_ids === []) {
            return;
        }


        $query = "SELECT * FROM object_data WHERE " . $ilDB->in('obj_id', $a_obj_ids, false, 'integer');
        $res = $ilDB->query($query);
        $db_trans = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_id = (int) $row->obj_id;

            // this if fixes #9960
            if (!isset($this->trans_loaded[$obj_id])) {
                $this->object_data_cache[$obj_id]['title'] = $row->title;
                $this->object_data_cache[$obj_id]['description'] = $row->description;
            }
            $this->object_data_cache[$obj_id]['type'] = $row->type;
            $this->object_data_cache[$obj_id]['owner'] = $row->owner;
            $this->object_data_cache[$obj_id]['last_update'] = $row->last_update;
            $this->object_data_cache[$obj_id]['offline'] = $row->offline;

            $translation_type = '';
            if (is_object($objDefinition)) {
                $translation_type = $objDefinition->getTranslationType($row->type);
            }

            if ($translation_type === "db") {
                $db_trans[$obj_id] = $obj_id;
            }
        }
        if (count($db_trans) > 0) {
            $this->preloadTranslations($db_trans, $a_lang);
        }
    }

    /**
     * Preload translation informations
     * @param int[] $a_obj_ids
     * @param string $a_lang
     */
    public function preloadTranslations(array $a_obj_ids, string $a_lang) : void
    {
        $ilDB = $this->db;

        $obj_ids = [];
        foreach ($a_obj_ids as $id) {
            // do not load an id more than one time
            if (!isset($this->trans_loaded[$id])) {
                $obj_ids[] = $id;
                $this->trans_loaded[$id] = true;
            }
        }

        if ($obj_ids !== []) {
            $q = "SELECT obj_id, title, description FROM object_translation " .
                "WHERE " . $ilDB->in('obj_id', $obj_ids, false, 'integer') . " " .
                "AND lang_code = " . $ilDB->quote($a_lang, 'text') . " " .
                "AND NOT lang_default = 1";
            $r = $ilDB->query($q);
            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $obj_id = (int) $row->obj_id;

                $this->object_data_cache[$obj_id]['title'] = $row->title;
                $this->object_data_cache[$obj_id]['description'] = $row->description;
                $this->description_trans[] = $obj_id;
            }
        }
    }

    /**
     * @param int[] $a_ref_ids
     * @param bool $a_incl_obj
     */
    public function preloadReferenceCache(array $a_ref_ids, bool $a_incl_obj = true) : void
    {
        $ilDB = $this->db;

        if ($a_ref_ids === []) {
            return;
        }

        $query = "SELECT ref_id, obj_id FROM object_reference " .
            "WHERE " . $ilDB->in('ref_id', $a_ref_ids, false, 'integer');
        $res = $ilDB->query($query);

        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->reference_cache[(int) $row['ref_id']] = (int) $row['obj_id'];
            $obj_ids[] = (int) $row['obj_id'];
        }

        if ($a_incl_obj) {
            $this->preloadObjectCache($obj_ids);
        }
    }
}
