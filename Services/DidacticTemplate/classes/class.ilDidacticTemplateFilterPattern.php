<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Represents a filter pattern for didactic template actions
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
abstract class ilDidacticTemplateFilterPattern
{
    const PATTERN_INCLUDE = 1;
    const PATTERN_EXCLUDE = 2;

    const PATTERN_SUBTYPE_REGEX = 1;

    private $pattern_id = 0;

    private $parent_id = 0;
    private $parent_type = '';

    private $pattern_type = 0;
    private $pattern_sub_type = 0;



    /**
     * Constructor
     * @param int $a_pattern_id
     */
    public function __construct($a_pattern_id = 0)
    {
        $this->setPatternId($a_pattern_id);
        if ($this->getPatternId()) {
            $this->read();
        }
    }

    /**
     * set pattern id
     * @param int $a_id
     */
    public function setPatternId($a_id)
    {
        $this->pattern_id = $a_id;
    }

    /**
     * Get pattern id
     * @return int
     */
    public function getPatternId()
    {
        return $this->pattern_id;
    }

    /**
     * Set parent id
     * @param int $a_id
     */
    public function setParentId($a_id)
    {
        $this->parent_id = $a_id;
    }

    /**
     * Get parent id
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Set parent type
     * @param string $a_type
     */
    public function setParentType($a_type)
    {
        $this->parent_type = $a_type;
    }

    /**
     * Get parent type
     * @return string
     */
    public function getParentType()
    {
        return $this->parent_type;
    }

    /**
     * Set pattern type
     * @param int $a_type
     */
    public function setPatternType($a_type)
    {
        $this->pattern_type = $a_type;
    }

    /**
     * Get pattern type
     * @return int
     */
    public function getPatterType()
    {
        return $this->pattern_type;
    }

    /**
     * Set pattern sub type
     * @param int $a_subtype
     */
    public function setPatternSubType($a_subtype)
    {
        $this->pattern_sub_type = $a_subtype;
    }

    /**
     * Get pattern sub type
     * @return int
     */
    public function getPatternSubType()
    {
        return $this->pattern_sub_type;
    }

    /**
     * Set pattern
     * @param string $a_pattern
     */
    public function setPattern($a_pattern)
    {
        $this->pattern = $a_pattern;
    }

    /**
     * Get pattern
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Check if pattern matches
     *
     * @param mixed
     * @return bool
     */
    abstract public function valid($a_source);


    /**
     * Get xml representation of pattern
     * @param ilXmlWriter $writer
     * @return void
     */
    abstract public function toXml(ilXmlWriter $writer);


    /**
     * Update pattern definition
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'UPDATE didactic_tpl_fp ' .
            'SET ' .
            'pattern_type = ' . $ilDB->quote($this->getPatterType(), 'integer') . ', ' .
            'pattern_sub_type = ' . $ilDB->quote($this->getPatternSubType(), 'integer') . ' ' .
            'pattern = ' . $ilDB->quote($this->getPattern(), 'text') . ' ' .
            'parent_id = ' . $ilDB->quote($this->getParentId(), 'integer') . ', ' .
            'parent_type = ' . $ilDB->quote($this->getParentType(), 'text') . ', ' .
            'WHERE pattern_id = ' . $ilDB->quote($this->getPatternId(), 'integer');
        $res = $ilDB->manipulate($query);
        return void;
    }

    /**
     * Create new pattern
     * @global ilDB $ilDB
     * @return int
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->setPatternId($ilDB->nextId('didactic_tpl_fp'));
        $query = 'INSERT INTO didactic_tpl_fp (pattern_id,pattern_type,pattern_sub_type,pattern,parent_id,parent_type) ' .
            'VALUES ( ' .
            $ilDB->quote($this->getPatternId(), 'integer') . ', ' .
            $ilDB->quote($this->getPatterType(), 'integer') . ', ' .
            $ilDB->quote($this->getPatternSubType(), 'integer') . ', ' .
            $ilDB->quote($this->getPattern(), 'text') . ', ' .
            $ilDB->quote($this->getParentId(), 'integer') . ', ' .
            $ilDB->quote($this->getParentType(), 'text') . ' ' .
            ')';
        $ilDB->manipulate($query);
        return $this->getPatternId();
    }

    /**
     * Delete pattern
     * @global ilDB $ilDB
     * @return bool
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM didactic_tpl_fp ' .
            'WHERE pattern_id = ' . $ilDB->quote($this->getPatternId(), 'integer');
        $ilDB->manipulate($query);
        return true;
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
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM didactic_tpl_fp ' .
            'WHERE pattern_id = ' . $ilDB->quote($this->getPatternId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setPatternType($row->pattern_type);
            $this->setPatternSubType($row->pattern_sub_type);
            $this->setPattern($row->pattern);
        }
    }
}
