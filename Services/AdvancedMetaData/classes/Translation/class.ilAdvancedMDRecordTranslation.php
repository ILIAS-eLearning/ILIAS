<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAdvancedMDRecordTranslation
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordTranslation
{
    public const TABLE_NAME = 'adv_md_record_int';

    /**
     * @var int
     */
    private $record_id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $lang_key;


    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * ilAdvancedMDRecordTranslation constructor.
     */
    public function __construct(int $record_id, string $title, string $description, string $lang_key)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->record_id = $record_id;
        $this->title = $title;
        $this->description = $description;
        $this->lang_key = $lang_key;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    /**
     * @param bool $lang_default
     */
    public function setLangDefault(bool $lang_default) : void
    {
        $this->lang_default = $lang_default;
    }


    /**
     * @return int
     */
    public function getRecordId() : int
    {
        return $this->record_id;
    }

    public function setRecordId(int $record_id) : void
    {
        $this->record_id = $record_id;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getLangKey() : string
    {
        return $this->lang_key;
    }


    public function update()
    {
        $query = 'update ' . self::TABLE_NAME . ' ' .
            'set title = ' . $this->db->quote($this->getTitle(), ilDBConstants::T_TEXT) . ', ' .
            'description = ' . $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ' ' .
            'where record_id = ' . $this->db->quote($this->getRecordId(), ilDBConstants::T_INTEGER) . ' ' .
            'and lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT);

        $this->db->manipulate($query);
    }

    public function delete()
    {
        $query = 'delete from ' . self::TABLE_NAME . ' ' .
            'where record_id = ' . $this->db->quote($this->getRecordId(), ilDBConstants::T_INTEGER) . ' and ' .
            'lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    public function insert()
    {
        $query = 'insert into ' . self::TABLE_NAME . ' (record_id, title, lang_code, description) ' .
            'values (  ' .
            $this->db->quote($this->getRecordId(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getTitle() , ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ' ' .
            ')';
        $this->db->manipulate($query);
    }

}