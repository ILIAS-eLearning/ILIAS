<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LP collection base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
abstract class ilLPCollection
{
    protected int $obj_id;
    protected int $mode;
    protected array $items = [];

    protected ilDBInterface $db;
    protected ilLogger $logger;

    public function __construct(int $a_obj_id, int $a_mode)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->trac();

        $this->obj_id = $a_obj_id;
        $this->mode = $a_mode;

        if ($a_obj_id) {
            $this->read($a_obj_id);
        }
    }

    public static function getInstanceByMode(
        int $a_obj_id,
        int $a_mode
    ) : ?ilLPCollection {
        $path = "Services/Tracking/classes/collection/";

        switch ($a_mode) {
            case ilLPObjSettings::LP_MODE_COLLECTION:
            case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
                return new ilLPCollectionOfRepositoryObjects(
                    $a_obj_id,
                    $a_mode
                );

            case ilLPObjSettings::LP_MODE_OBJECTIVES:
                return new ilLPCollectionOfObjectives($a_obj_id, $a_mode);

            case ilLPObjSettings::LP_MODE_SCORM:
                return new ilLPCollectionOfSCOs($a_obj_id, $a_mode);

            case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
            case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
                return new ilLPCollectionOfLMChapters($a_obj_id, $a_mode);

            case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                return new ilLPCollectionOfMediaObjects($a_obj_id, $a_mode);
        }
        return null;
    }

    public static function getCollectionModes() : array
    {
        return array(
            ilLPObjSettings::LP_MODE_COLLECTION
            ,
            ilLPObjSettings::LP_MODE_COLLECTION_TLT
            ,
            ilLPObjSettings::LP_MODE_COLLECTION_MANUAL
            ,
            ilLPObjSettings::LP_MODE_SCORM
            ,
            ilLPObjSettings::LP_MODE_OBJECTIVES
            ,
            ilLPObjSettings::LP_MODE_COLLECTION_MOBS
        );
    }

    public function hasSelectableItems() : bool
    {
        return true;
    }

    public function cloneCollection(int $a_target_id, int $a_copy_id) : void
    {
        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        // #12067
        $new_collection = new static($target_obj_id, $this->mode);
        foreach ($this->items as $item) {
            if (!isset($mappings[$item]) or !$mappings[$item]) {
                continue;
            }

            $new_collection->addEntry($mappings[$item]);
        }
        $this->logger->debug('cloned learning progress collection.');
    }

    public function getItems() : array
    {
        return $this->items;
    }

    protected function read(int $a_obj_id) : void
    {
        $items = array();
        $res = $this->db->query(
            "SELECT * FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($a_obj_id, "integer")
        );
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($this->validateEntry((int) $row->item_id)) {
                $items[] = $row->item_id;
            } else {
                $this->deleteEntry($row->item_id);
            }
        }
        $this->items = $items;
    }

    public function delete() : void
    {
        $query = "DELETE FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer");
        $this->db->manipulate($query);

        $query = "DELETE FROM ut_lp_coll_manual" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer");
        $this->db->manipulate($query);
        // #15462 - reset internal data
        $this->items = array();
    }

    //
    // ENTRIES
    //

    protected function validateEntry(int $a_item_id) : bool
    {
        return true;
    }

    public function isAssignedEntry(int $a_item_id) : bool
    {
        if (is_array($this->items)) {
            return in_array($a_item_id, $this->items);
        }
        return false;
    }

    protected function addEntry(int $a_item_id) : bool
    {
        if (!$this->isAssignedEntry($a_item_id)) {
            $query = "INSERT INTO ut_lp_collections" .
                " (obj_id, lpmode, item_id)" .
                " VALUES (" . $this->db->quote($this->obj_id, "integer") .
                ", " . $this->db->quote($this->mode, "integer") .
                ", " . $this->db->quote($a_item_id, "integer") .
                ")";
            $this->db->manipulate($query);
            $this->items[] = $a_item_id;
        }
        return true;
    }

    protected function deleteEntry(int $a_item_id) : bool
    {
        $query = "DELETE FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($this->obj_id, "integer") .
            " AND item_id = " . $this->db->quote($a_item_id, "integer");
        $this->db->manipulate($query);
        return true;
    }

    /**
     * @param int[] $a_item_ids
     */
    public function deactivateEntries(array $a_item_ids) : void
    {
        foreach ($a_item_ids as $item_id) {
            $this->deleteEntry($item_id);
        }
    }

    /**
     * @param int[] $a_item_ids
     */
    public function activateEntries(array $a_item_ids) : void
    {
        foreach ($a_item_ids as $item_id) {
            $this->addEntry($item_id);
        }
    }
}
