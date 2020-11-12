<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAdvancedMDFieldTranslation
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldTranslation
{
    public const TABLE_NAME = 'adv_md_field_int';

    /**
     * @var
     */
    private $field_id;

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
     * ilAdvancedMDFieldTranslation constructor.
     */
    public function __construct(int $field_id, string $title, string $description, string $lang_key)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->field_id = $field_id;
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
     * @return int
     */
    public function getFieldId() : int
    {
        return $this->field_id;
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

    /**
     * update or insert entry
     */
    public function update()
    {
        $query = 'select *  from ' . self::TABLE_NAME . ' ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'and lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT) . ' ';
        $res = $this->db->query($query);
        if (!$res->numRows()) {
            return $this->insert();
        }

        $query = 'update ' . self::TABLE_NAME . ' ' .
            'set title = ' . $this->db->quote($this->getTitle(), ilDBConstants::T_TEXT) . ', ' .
            'description = ' . $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ' ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'and lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT);

        $this->db->manipulate($query);
    }

    public function delete()
    {
        $query = 'delete from ' . self::TABLE_NAME . ' ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' and ' .
            'lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    public function insert()
    {
        $query = 'insert into ' . self::TABLE_NAME . ' (field_id, title, lang_code, description) ' .
            'values (  ' .
            $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getTitle() , ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ' ' .
            ')';
        $this->db->manipulate($query);
    }

}