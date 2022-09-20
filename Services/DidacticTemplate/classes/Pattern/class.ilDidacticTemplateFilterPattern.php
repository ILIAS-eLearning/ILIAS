<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents a filter pattern for didactic template actions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
abstract class ilDidacticTemplateFilterPattern
{
    public const PATTERN_INCLUDE = 1;
    public const PATTERN_EXCLUDE = 2;

    public const PATTERN_SUBTYPE_REGEX = 1;

    private int $pattern_id = 0;

    private int $parent_id = 0;
    private string $parent_type = '';

    private string $pattern = '';
    private int $pattern_type = 0;
    private int $pattern_sub_type = 0;

    protected ilDBInterface $db;
    protected ilLogger $logger;

    public function __construct(int $a_pattern_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->otpl();

        $this->setPatternId($a_pattern_id);
        if ($this->getPatternId()) {
            $this->read();
        }
    }

    public function setPatternId(int $a_id): void
    {
        $this->pattern_id = $a_id;
    }

    public function getPatternId(): int
    {
        return $this->pattern_id;
    }

    public function setParentId(int $a_id): void
    {
        $this->parent_id = $a_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setParentType(string $a_type): void
    {
        $this->parent_type = $a_type;
    }

    public function getParentType(): string
    {
        return $this->parent_type;
    }

    public function setPatternType(int $a_type): void
    {
        $this->pattern_type = $a_type;
    }

    public function getPatternType(): int
    {
        return $this->pattern_type;
    }

    public function setPatternSubType(int $a_subtype): void
    {
        $this->pattern_sub_type = $a_subtype;
    }

    public function getPatternSubType(): int
    {
        return $this->pattern_sub_type;
    }

    public function setPattern(string $a_pattern): void
    {
        $this->pattern = $a_pattern;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Check if pattern matches
     * @param mixed
     * @return bool
     */
    abstract public function valid(string $a_source): bool;

    /**
     * Get xml representation of pattern
     * @param ilXmlWriter $writer
     * @return string
     */
    abstract public function toXml(ilXmlWriter $writer): void;

    /**
     * Update pattern definition
     */
    public function update(): void
    {
        $query = 'UPDATE didactic_tpl_fp ' .
            'SET ' .
            'pattern_type = ' . $this->db->quote($this->getPatternType(), 'integer') . ', ' .
            'pattern_sub_type = ' . $this->db->quote($this->getPatternSubType(), 'integer') . ' ' .
            'pattern = ' . $this->db->quote($this->getPattern(), 'text') . ' ' .
            'parent_id = ' . $this->db->quote($this->getParentId(), 'integer') . ', ' .
            'parent_type = ' . $this->db->quote($this->getParentType(), 'text') . ', ' .
            'WHERE pattern_id = ' . $this->db->quote($this->getPatternId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Create new pattern
     * Returns new pattern id
     * @return int
     */
    public function save(): int
    {
        $this->setPatternId($this->db->nextId('didactic_tpl_fp'));
        $query = 'INSERT INTO didactic_tpl_fp (pattern_id,pattern_type,pattern_sub_type,pattern,parent_id,parent_type) ' .
            'VALUES ( ' .
            $this->db->quote($this->getPatternId(), 'integer') . ', ' .
            $this->db->quote($this->getPatternType(), 'integer') . ', ' .
            $this->db->quote($this->getPatternSubType(), 'integer') . ', ' .
            $this->db->quote($this->getPattern(), 'text') . ', ' .
            $this->db->quote($this->getParentId(), 'integer') . ', ' .
            $this->db->quote($this->getParentType(), 'text') . ' ' .
            ')';
        $this->db->manipulate($query);

        return $this->getPatternId();
    }

    public function delete(): void
    {
        $query = 'DELETE FROM didactic_tpl_fp ' .
            'WHERE pattern_id = ' . $this->db->quote($this->getPatternId(), 'integer');
        $this->db->manipulate($query);
    }

    public function __clone()
    {
        $this->setParentId(0);
        $this->setPatternId(0);
    }

    /**
     * Read pattern definition from db
     * @return void
     */
    protected function read(): void
    {
        $query = 'SELECT * FROM didactic_tpl_fp ' .
            'WHERE pattern_id = ' . $this->db->quote($this->getPatternId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setPatternType((int) $row->pattern_type);
            $this->setPatternSubType((int) $row->pattern_sub_type);
            $this->setPattern($row->pattern);
        }
    }
}
