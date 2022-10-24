<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Meta Data class (element requirement)
 * @package ilias-core
 * @version $Id$
 */
class ilMDRequirement extends ilMDBase
{
    private int $or_composite_id = 0;
    private string $operating_system_name = '';
    private string $operating_system_minimum_version = '';
    private string $operating_system_maximum_version = '';
    private string $browser_name = '';
    private string $browser_minimum_version = '';
    private string $browser_maximum_version = '';

    // SET/GET
    public function setOrCompositeId(int $a_or_composite_id): void
    {
        $this->or_composite_id = $a_or_composite_id;
    }

    public function getOrCompositeId(): int
    {
        return $this->or_composite_id;
    }

    public function setOperatingSystemName(string $a_val): bool
    {
        switch ($a_val) {
            case 'PC-DOS':
            case 'MS-Windows':
            case 'MacOS':
            case 'Unix':
            case 'Multi-OS':
            case 'None':
                $this->operating_system_name = $a_val;
                return true;

            default:
                return false;
        }
    }

    public function getOperatingSystemName(): string
    {
        return $this->operating_system_name;
    }

    public function setOperatingSystemMinimumVersion(string $a_val): void
    {
        $this->operating_system_minimum_version = $a_val;
    }

    public function getOperatingSystemMinimumVersion(): string
    {
        return $this->operating_system_minimum_version;
    }

    public function setOperatingSystemMaximumVersion(string $a_val): void
    {
        $this->operating_system_maximum_version = $a_val;
    }

    public function getOperatingSystemMaximumVersion(): string
    {
        return $this->operating_system_maximum_version;
    }

    public function setBrowserName(string $a_val): bool
    {
        switch ($a_val) {
            case 'Any':
            case 'NetscapeCommunicator':
            case 'MS-InternetExplorer':
            case 'Opera':
            case 'Amaya':
            case 'Mozilla':
                $this->browser_name = $a_val;
                return true;

            default:
                return false;
        }
    }

    public function getBrowserName(): string
    {
        return $this->browser_name;
    }

    public function setBrowserMinimumVersion(string $a_val): void
    {
        $this->browser_minimum_version = $a_val;
    }

    public function getBrowserMinimumVersion(): string
    {
        return $this->browser_minimum_version;
    }

    public function setBrowserMaximumVersion(string $a_val): void
    {
        $this->browser_maximum_version = $a_val;
    }

    public function getBrowserMaximumVersion(): string
    {
        return $this->browser_maximum_version;
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_requirement_id'] = array('integer', $next_id = $this->db->nextId('il_meta_requirement'));

        if ($this->db->insert('il_meta_requirement', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_requirement',
            $this->__getFields(),
            array("meta_requirement_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_requirement " .
                "WHERE meta_requirement_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);
            return true;
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'parent_type' => array('text', $this->getParentType()),
            'parent_id' => array('integer', $this->getParentId()),
            'operating_system_name' => array('text', $this->getOperatingSystemName()),
            'os_min_version' => array('text', $this->getOperatingSystemMinimumVersion()),
            'os_max_version' => array('text', $this->getOperatingSystemMaximumVersion()),
            'browser_name' => array('text', $this->getBrowserName()),
            'browser_minimum_version' => array('text', $this->getBrowserMinimumVersion()),
            'browser_maximum_version' => array('text', $this->getBrowserMaximumVersion()),
            'or_composite_id' => array('integer', $this->getOrCompositeId())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_requirement " .
                "WHERE meta_requirement_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setOperatingSystemName($row->operating_system_name);
                $this->setOperatingSystemMinimumVersion($row->os_min_version);
                $this->setOperatingSystemMaximumVersion($row->os_max_version);
                $this->setBrowserName($row->browser_name);
                $this->setBrowserMinimumVersion($row->browser_minimum_version);
                $this->setBrowserMaximumVersion($row->browser_maximum_version);
                $this->setOrCompositeId((int) $row->or_composite_id);
            }
        }
        return true;
    }

    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('Requirement');
        $writer->xmlStartTag('Type');

        if ($this->getOperatingSystemName() !== '') {
            $writer->xmlElement('OperatingSystem', array(
                'Name' => $this->getOperatingSystemName() ?: 'None',
                'MinimumVersion' => $this->getOperatingSystemMinimumVersion(),
                'MaximumVersion' => $this->getOperatingSystemMaximumVersion()
            ));
        }
        if ($this->getBrowserName() !== '') {
            $writer->xmlElement('Browser', array(
                'Name' => $this->getBrowserName() ?: 'Any',
                'MinimumVersion' => $this->getBrowserMinimumVersion(),
                'MaximumVersion' => $this->getBrowserMaximumVersion()
            ));
        }
        $writer->xmlEndTag('Type');
        $writer->xmlEndTag('Requirement');
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(
        int $a_rbac_id,
        int $a_obj_id,
        int $a_parent_id,
        string $a_parent_type,
        int $a_or_composite_id = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_requirement_id FROM il_meta_requirement " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text') . " " .
            "AND or_composite_id = " . $ilDB->quote($a_or_composite_id, 'integer');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_requirement_id;
        }
        return $ids;
    }
}
