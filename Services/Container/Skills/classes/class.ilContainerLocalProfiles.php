<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Local competence profiles of a container
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilContainerLocalProfiles
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
    protected $id;

    /**
     * Constructor
     *
     * @param ilContainer $a_obj
     */
    public function __construct(ilContainer $a_obj)
    {
        global $DIC;

        $this->db = $DIC->database();

        if ($a_obj->getId() > 0) {
            $member_id = (int) $a_obj->getDefaultMemberRole();
            $this->setId($member_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param int $a_val object id
     */
    public function setId(int $a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return int object id
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Read
     */
    public function read()
    {
        $db = $this->db;

        $this->profiles = array();
        $set = $db->query("SELECT spr.profile_id, spr.role_id FROM skl_profile_role spr INNER JOIN skl_profile sp " .
            " ON spr.profile_id = sp.id " .
            " WHERE sp.ref_id <> 0 " .
            " AND role_id  = " . $db->quote($this->getId(), "integer"));
        while ($rec = $db->fetchAssoc($set)) {
            $this->profiles[$rec["profile_id"]] = $rec;
        }
    }
}
