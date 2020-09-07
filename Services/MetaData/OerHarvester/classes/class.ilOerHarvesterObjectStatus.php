<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * OER harvester object status
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOerHarvesterObjectStatus
{
    private $obj_id = 0;

    private $harvest_ref_id = 0;

    private $blocked = false;

    private $db = null;


    /**
     * ilOerHarvesterObjectStatus constructor.
     * @param int $obj_id
     */
    public function __construct($obj_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->obj_id = $obj_id;
        if ($this->obj_id) {
            $this->read();
        }
    }

    /**
     * @return int[]
     * @throws ilDatabaseException
     */
    public static function lookupHarvested()
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'SELECT href_id FROM il_meta_oer_stat ';
        $res = $db->query($query);

        $hids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $hids[] = $row->href_id;
        }
        return $hids;
    }

    /**
     * @param $a_href_id
     */
    public static function lookupObjIdByHarvestingId($a_href_id)
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'SELECT obj_id FROM il_meta_oer_stat ' .
            'WHERE href_id = ' . $db->quote($a_href_id, 'integer');
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
    }

    public function setObjId($a_obj_id)
    {
        $this->obj_id = $a_obj_id;
    }

    public function getObjId()
    {
        return $this->obj_id;
    }

    public function setHarvestRefId($a_ref_id)
    {
        $this->harvest_ref_id = $a_ref_id;
    }

    public function getHarvestRefId()
    {
        return $this->harvest_ref_id;
    }

    public function setBlocked($a_stat)
    {
        $this->blocked = $a_stat;
    }

    public function isBlocked()
    {
        return $this->blocked;
    }

    public function isCreated()
    {
        return (bool) $this->harvest_ref_id;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->delete();
        $query = 'INSERT INTO il_meta_oer_stat ' .
            '(obj_id, href_id, blocked ) ' .
            'VALUES (' .
            $this->db->quote($this->getObjId(), 'integer') . ', ' .
            $this->db->quote($this->getHarvestRefId(), 'integer') . ', ' .
            $this->db->quote($this->isBlocked(), 'integer') .
            ')';
        $res = $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete by obj_id
     */
    public function delete()
    {
        $query = 'DELETE FROM il_meta_oer_stat ' .
            'WHERE obj_id = ' . $this->db->quote($this->getObjId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }


    /**
     * @throws ilDatabaseException
     */
    public function read()
    {
        $query = 'SELECT * FROM il_meta_oer_stat ' .
            'WHERE obj_id = ' . $this->db->quote($this->getObjId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setObjId($row->obj_id);
            $this->setHarvestRefId($row->href_id);
            $this->setBlocked((bool) $row->blocked);
        }
    }
}
