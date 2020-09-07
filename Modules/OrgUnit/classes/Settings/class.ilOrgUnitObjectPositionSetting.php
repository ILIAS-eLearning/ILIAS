<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores object activation status of orgunit position settings.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOrgUnitObjectPositionSetting
{

    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var int
     */
    private $obj_id;
    /**
     * @var null|bool
     */
    private $active;


    /**
     * Constructor
     *
     * @param int $a_obj_id
     */
    public function __construct($a_obj_id)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->obj_id = $a_obj_id;
        $this->readSettings();
    }


    /**
     * Lookup activation status
     *
     * @param int $a_obj_id
     *
     * @return bool active status
     */
    public function lookupActive($a_obj_id)
    {
        $db = $GLOBALS['DIC']->database();

        $query = 'select *  from orgu_obj_pos_settings ' . 'where obj_id = '
                 . $db->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->active;
        }
    }


    /**
     * Check if position access is active. This returns true or false if it is object specific or null if the object has no setting.
     *
     * @return null|bool
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * Set active for object
     *
     * @param bool $a_status
     */
    public function setActive($a_status)
    {
        $this->active = $a_status;
    }


    /**
     * Update object entry
     */
    public function update()
    {
        $this->db->replace('orgu_obj_pos_settings', [
                'obj_id' => [ 'integer', $this->obj_id ],
            ], [
                'active' => [ 'integer', (int) $this->isActive() ],
            ]);
    }


    /**
     * Delete record
     */
    public function delete()
    {
        $query = 'DELETE from orgu_obj_pos_settings ' . 'WHERE obj_id = '
                 . $this->db->quote($this->obj_id, 'integer');
        $this->db->manipulate($query);
    }


    /**
     * @return bool Returns true if the object has a specific setting false if there is no object specific setting, take the global setting in this
     * case.
     */
    public function hasObjectSpecificActivation()
    {
        return $this->active !== null;
    }


    /**
     * Read from db
     */
    protected function readSettings()
    {
        if (!$this->obj_id) {
            return;
        }
        $query = 'select * from orgu_obj_pos_settings ' . 'where obj_id = '
                 . $this->db->quote($this->obj_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->active = (bool) $row->active;
        }

        return;
    }
}
