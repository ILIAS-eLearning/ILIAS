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
 * Storage of course attributes for assignment rules
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseAttributes
{
    private static ?array $instances = null;

    private int $server_id ;
    private int $mid;

    private array $attributes = array();

    private ilDBInterface $db;

    /**
     * Constructor
     */
    public function __construct(int $a_server_id, int $a_mid)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->server_id = $a_server_id;
        $this->mid = $a_mid;

        $this->read();
    }

    /**
     * Get instance
     */
    public static function getInstance(int $a_server_id, int $a_mid): \ilECSCourseAttributes
    {
        $id = $a_server_id . '_' . $a_mid;
        return self::$instances[$id] ?? (self::$instances[$id] = new ilECSCourseAttributes($a_server_id, $a_mid));
    }

    /**
     * Get current attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get first defined attribute
     */
    public function getFirstAttribute(): ?\ilECSCourseAttribute
    {
        return $this->getAttributes()[0] ?? null;
    }

    /**
     * Get first attribute name
     */
    public function getFirstAttributeName(): string
    {
        if ($this->getFirstAttribute() instanceof ilECSCourseAttribute) {
            return $this->getFirstAttribute()->getName();
        }
        return '';
    }

    /**
     * Get attribute sequence
     */
    public function getAttributeSequence($a_last_attribute): array
    {
        if (!$a_last_attribute) {
            return [];
        }
        $sequence = [];
        foreach ($this->getAttributes() as $att) {
            $sequence[] = $att->getName();
            if ($a_last_attribute === $att->getName()) {
                break;
            }
        }
        return $sequence;
    }

    /**
     * Get upper attributes in hierarchy
     */
    public function getUpperAttributes($a_name): array
    {
        $reverse_attributes = array_reverse($this->getAttributes());

        $found = false;
        $upper = array();
        foreach ($reverse_attributes as $att) {
            if ($att->getName() === $a_name) {
                $found = true;
                continue;
            }
            if ($found) {
                $upper[] = $att->getName();
            }
        }
        return array_reverse($upper);
    }

    /**
     * Get next attribute name in sequence
     */
    public function getNextAttributeName(string $a_name): string
    {
        if (!$a_name) {
            return $this->getFirstAttributeName();
        }
        $found = false;
        foreach ($this->getAttributes() as $att) {
            if ($a_name === $att->getName()) {
                $found = true;
                continue;
            }
            if ($found) {
                return $att->getName();
            }
        }
        return '';
    }

    /**
     * Get next attribute name in sequence
     */
    public function getPreviousAttributeName(string $a_name): string
    {
        if (!$a_name) {
            return '';
        }
        $found = false;
        $reverse_attributes = array_reverse($this->getAttributes());
        foreach ($reverse_attributes as $att) {
            if ($a_name === $att->getName()) {
                $found = true;
                continue;
            }
            if ($found) {
                return $att->getName();
            }
        }
        return '';
    }

    /**
     * Get active attribute values
     */
    public function getAttributeValues(): array
    {
        $values = array();
        foreach ($this->getAttributes() as $att) {
            $values[] = $att->getName();
        }
        return $values;
    }

    /**
     * Delete all mappings
     */
    public function delete(): void
    {
        foreach ($this->getAttributes() as $att) {
            $att->delete();
        }
        $this->attributes = [];
    }


    /**
     * Read attributes
     */
    protected function read(): void
    {
        $this->attributes = [];

        $query = 'SELECT * FROM ecs_crs_mapping_atts ' .
                'WHERE sid = ' . $this->db->quote($this->server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($this->mid, 'integer') . ' ' .
                'ORDER BY id';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->attributes[] = new ilECSCourseAttribute((int) $row->id);
        }
    }
}
