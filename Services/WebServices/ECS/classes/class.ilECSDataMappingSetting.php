<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSDataMappingSetting
{
    public const MAPPING_EXPORT = 1;
    public const MAPPING_IMPORT_CRS = 2;
    public const MAPPING_IMPORT_RCRS = 3;

    private ilDBInterface $db;

    private int $server_id = 0;
    private int $mapping_type = 0;
    private string $ecs_field = '';
    private int $advmd_id = 0;

    public function __construct(int $a_server_id = 0, int $mapping_type = 0, string $ecs_field = '')
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->setServerId($a_server_id);
        $this->setMappingType($mapping_type);
        $this->setECSField($ecs_field);
    }

    /**
     * set server id
     */
    public function setServerId(int $a_server_id): void
    {
        $this->server_id = $a_server_id;
    }

    /**
     * Get server id
     */
    public function getServerId(): int
    {
        return $this->server_id;
    }

    public function setECSField(string $ecs_field): void
    {
        $this->ecs_field = $ecs_field;
    }

    /**
     * Get ecs field
     */
    public function getECSField(): string
    {
        return $this->ecs_field;
    }

    /**
     * Set mapping type
     */
    public function setMappingType(int $mapping_type): void
    {
        $this->mapping_type = $mapping_type;
    }

    /**
     * Get mapping type
     */
    public function getMappingType(): int
    {
        return $this->mapping_type;
    }

    public function getAdvMDId(): int
    {
        return $this->advmd_id;
    }

    public function setAdvMDId(int $a_id): void
    {
        $this->advmd_id = $a_id;
    }

    /**
     * Save mappings
     */
    public function save(): void
    {
        $query = 'SELECT * FROM ecs_data_mapping ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND mapping_type = ' . $this->db->quote($this->getMappingType(), 'integer') . ' ' .
            'AND ecs_field = ' . $this->db->quote($this->getECSField(), 'text');
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $this->update();
        } else {
            $this->create();
        }
    }

    /**
     * Update setting
     */
    protected function update(): void
    {
        $query = 'UPDATE ecs_data_mapping ' .
            'SET advmd_id = ' . $this->db->quote($this->getAdvMDId(), 'integer') . ' ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND mapping_type = ' . $this->db->quote($this->getMappingType(), 'integer') . ' ' .
            'AND ecs_field = ' . $this->db->quote($this->getECSField(), 'text');
        $this->db->manipulate($query);
    }

    protected function create(): bool
    {
        $query = 'INSERT INTO ecs_data_mapping (sid,mapping_type,ecs_field,advmd_id) ' .
            'VALUES(' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote($this->getMappingType(), 'integer') . ', ' .
            $this->db->quote($this->getECSField(), 'text') . ', ' .
            $this->db->quote($this->getAdvMDId(), 'integer') . ' ) ';
        $this->db->manipulate($query);
        return true;
    }


    /**
     * Read settings
     */
    private function read(): void
    {
        if ($this->getServerId() || $this->getMappingType() || $this->getECSField()) {
            $query = 'SELECT * FROM ecs_data_mapping ' .
                'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
                'AND mapping_type = ' . $this->db->quote($this->getMappingType(), 'integer') . ' ' .
                'AND ecs_field = ' . $this->db->quote($this->getECSField(), 'text');
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setAdvMDId($row->advmd_id);
            }
        }
    }
}
