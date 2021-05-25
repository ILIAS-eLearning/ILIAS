<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Global competence profiles of a container
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilContainerGlobalProfiles
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected $profiles = array();

    /**
     * @var int object id
     */
    protected $obj_id;

    /**
     * @var int $mem_rol_id
     */
    protected $mem_rol_id;

    /**
     * Constructor
     *
     * @param int $a_obj_id
     */
    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setObjId($a_obj_id);

        if ($this->getObjId() > 0) {
            $this->setMemberRoleId();
            $this->read();
        }
    }

    /**
     * Set object id
     *
     * @param int $a_obj_id object id
     */
    protected function setObjId(int $a_obj_id)
    {
        $this->obj_id = $a_obj_id;
    }

    /**
     * Get object id
     *
     * @return int object id
     */
    protected function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Set member role id of object
     *
     * @param int $a_obj_id object id
     */
    protected function setMemberRoleId()
    {
        $refs = ilObject::_getAllReferences($this->getObjId());
        $ref_id = end($refs);
        $this->mem_rol_id = ilParticipants::getDefaultMemberRole($ref_id);
    }

    /**
     * Get member role id of object
     *
     * @return int member role id
     */
    protected function getMemberRoleId() : int
    {
        return $this->mem_rol_id;
    }

    /**
     * Reset profiles
     */
    public function resetProfiles()
    {
        $this->profiles = array();
    }

    /**
     * Add profile
     *
     * @param int $a_profile_id
     */
    public function addProfile(int $a_profile_id)
    {
        $this->profiles[$a_profile_id] = array(
            "profile_id" => $a_profile_id
        );
    }

    /**
     * Remove profile
     *
     * @param int $a_profile_id
     */
    public function removeProfile(int $a_profile_id)
    {
        unset($this->profiles[$a_profile_id]);
    }

    /**
     * Get profiles
     *
     * @return array
     */
    public function getProfiles() : array
    {
        return $this->profiles;
    }

    /**
     * Read
     */
    protected function read()
    {
        $db = $this->db;

        $this->profiles = array();
        $set = $db->query(
            "SELECT spr.profile_id, spr.role_id, sp.title FROM skl_profile_role spr INNER JOIN skl_profile sp " .
            " ON spr.profile_id = sp.id " .
            " WHERE sp.ref_id = 0 " .
            " AND role_id  = " . $db->quote($this->getMemberRoleId(), "integer")
        );
        while ($rec = $db->fetchAssoc($set)) {
            $this->profiles[$rec["profile_id"]] = $rec;
        }
    }

    /**
     * Delete
     */
    protected function delete()
    {
        $db = $this->db;

        $db->manipulate(
            "DELETE spr FROM skl_profile_role spr INNER JOIN skl_profile sp " .
            " ON spr.profile_id = sp.id " .
            " WHERE sp.ref_id = 0 " .
            " AND role_id = " . $db->quote($this->getMemberRoleId(), "integer")
        );
    }

    /**
     * Save
     */
    public function save()
    {
        $db = $this->db;

        $this->delete();
        foreach ($this->profiles as $p) {
            $db->manipulate("INSERT INTO skl_profile_role " .
                "(role_id, profile_id) VALUES (" .
                $db->quote($this->getMemberRoleId(), "integer") . "," .
                $db->quote($p["profile_id"], "integer") . ")");
        }
    }
}
