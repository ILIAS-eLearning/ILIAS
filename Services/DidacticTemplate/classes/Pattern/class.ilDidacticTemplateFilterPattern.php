<?php declare(strict_types=1);
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

    private int $pattern_type = 0;
    private int $pattern_sub_type = 0;
    private string $pattern;

    protected ilDBInterface $db;
    protected ilLogger $logger;

    /**
     * Constructor
     * @param int $a_pattern_id
     */
    public function __construct($a_pattern_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->otpl();

        $this->setPatternId($a_pattern_id);
        if ($this->getPatternId()) {
            $this->read();
        }
    }

    /**
     * Set pattern id
     * @param int $a_id
     */
    public function setPatternId($a_id) : void
    {
        $this->pattern_id = $a_id;
    }

    /**
     * Get pattern id
     * @return int
     */
    public function getPatternId() : int
    {
        return $this->pattern_id;
    }

    /**
     * Set parent id
     * @param int $a_id
     */
    public function setParentId(int $a_id) : void
    {
        $this->parent_id = $a_id;
    }

    /**
     * Get parent id
     * @return int
     */
    public function getParentId() : int
    {
        return $this->parent_id;
    }

    /**
     * Set parent type
     * @param string $a_type
     */
    public function setParentType(string $a_type) : void
    {
        $this->parent_type = $a_type;
    }

    /**
     * Get parent type
     * @return string
     */
    public function getParentType() : string
    {
        return $this->parent_type;
    }

    /**
     * Set pattern type
     * @param int $a_type
     */
    public function setPatternType(int $a_type) : void
    {
        $this->pattern_type = $a_type;
    }

    /**
     * Get pattern type
     * @return int
     */
    public function getPatternType() : int
    {
        return $this->pattern_type;
    }

    /**
     * Set pattern sub type
     * @param int $a_subtype
     */
    public function setPatternSubType(int $a_subtype) : void
    {
        $this->pattern_sub_type = $a_subtype;
    }

    /**
     * Get pattern sub type
     * @return int
     */
    public function getPatternSubType() : int
    {
        return $this->pattern_sub_type;
    }

    /**
     * Set pattern
     * @param string $a_pattern
     */
    public function setPattern(string $a_pattern) : void
    {
        $this->pattern = $a_pattern;
    }

    /**
     * Get pattern
     * @return string
     */
    public function getPattern() : string
    {
        return $this->pattern;
    }

    /**
     * Check if pattern matches
     * @param mixed
     * @return bool
     */
    abstract public function valid(string $a_source) : bool;

    /**
     * Get xml representation of pattern
     * @param ilXmlWriter $writer
     * @return string
     */
    abstract public function toXml(ilXmlWriter $writer) : void;

    /**
     * Update pattern definition
     */
    public function update() : void
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
    public function save() : int
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

    /**
     * Delete pattern
     * @return void
     */
    public function delete() : void
    {
        $query = 'DELETE FROM didactic_tpl_fp ' .
            'WHERE pattern_id = ' . $this->db->quote($this->getPatternId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Magic clone method
     */
    public function __clone()
    {
        $this->setParentId(0);
        $this->setPatternId(0);
    }

    /**
     * Read pattern definition from db
     * @return void
     */
    protected function read() : void
    {
        $query = 'SELECT * FROM didactic_tpl_fp ' .
            'WHERE pattern_id = ' . $this->db->quote($this->getPatternId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setPatternType($row->pattern_type);
            $this->setPatternSubType($row->pattern_sub_type);
            $this->setPattern($row->pattern);
        }
    }
}
