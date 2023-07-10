<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Defines a system check group including different tasks of a component
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroup
{
    private int $id = 0;
    private string $component_id = '';
    private string $component_type = '';
    private ?ilDateTime $last_update = null;
    private int $status = 0;
    protected ilDBInterface $db;

    public function __construct(int $a_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->id = $a_id;
        $this->read();
    }

    public static function lookupComponent(int $a_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT component FROM sysc_groups ' .
            'WHERE id = ' . $ilDB->quote($a_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (string) $row->component;
        }
        return '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setComponentId(string $a_comp): void
    {
        $this->component_id = $a_comp;
    }

    public function getComponentId(): string
    {
        return $this->component_id;
    }

    public function setLastUpdate(ilDateTime $a_update): void
    {
        $this->last_update = $a_update;
    }

    public function getLastUpdate(): ilDateTime
    {
        if (!$this->last_update) {
            return $this->last_update = new ilDateTime();
        }
        return $this->last_update;
    }

    public function setStatus(int $a_status): void
    {
        $this->status = $a_status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function read(): bool
    {
        if (!$this->getId()) {
            return false;
        }

        $query = 'SELECT * FROM sysc_groups ' .
            'WHERE id = ' . $this->db->quote($this->getId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setComponentId((string) $row->component);
            $this->setLastUpdate(new ilDateTime($row->last_update, IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setStatus((int) $row->status);
        }
        return true;
    }

    public function create(): int
    {
        $this->id = $this->db->nextId('sysc_groups');

        $query = 'INSERT INTO sysc_groups (id,component,status) ' .
            'VALUES ( ' .
            $this->db->quote($this->getId(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getComponentId(), ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getStatus(), ilDBConstants::T_INTEGER) . ' ' .
            ')';
        $this->db->manipulate($query);
        return $this->getId();
    }
}
