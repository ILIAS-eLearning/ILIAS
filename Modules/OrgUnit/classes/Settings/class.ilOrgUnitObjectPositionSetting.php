<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores object activation status of orgunit position settings.
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOrgUnitObjectPositionSetting
{
    protected ilDBInterface $db;
    private int $obj_id;
    private ?bool $active = null;

    public function __construct(int $a_obj_id)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->obj_id = $a_obj_id;
        $this->readSettings();
    }

    /**
     * Lookup activation status
     */
    final public function lookupActive(int $a_obj_id) : bool
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
     */
    final public function isActive() : ?bool
    {
        return $this->active;
    }

    /**
     * Set active for object
     */
    final public function setActive(bool $a_status) : void
    {
        $this->active = $a_status;
    }

    final public function update() : void
    {
        $this->db->replace('orgu_obj_pos_settings', [
            'obj_id' => ['integer', $this->obj_id],
        ], [
            'active' => ['integer', (int) $this->isActive()],
        ]);
    }

    final public function delete() : void
    {
        $query = 'DELETE from orgu_obj_pos_settings ' . 'WHERE obj_id = '
            . $this->db->quote($this->obj_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * @return bool Returns true if the object has a specific setting false if there is no object specific setting, take the global setting in this
     * case.
     */
    final public function hasObjectSpecificActivation() : bool
    {
        return $this->active !== null;
    }

    private function readSettings() : void
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
