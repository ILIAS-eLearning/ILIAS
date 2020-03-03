<?php
/**
 * Class ilBiblTranslation
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

class ilBiblTranslation extends ActiveRecord implements ilBiblTranslationInterface
{
    const TABLE_NAME = 'il_bibl_translation';


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     * @con_sequence   true
     */
    protected $id = 0;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_is_notnull true
     * @con_is_unique  true
     */
    protected $field_id = 0;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     2
     * @con_is_notnull true
     */
    protected $language_key = '';
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $translation = '';
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected $description = '';


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return integer
     */
    public function getFieldId()
    {
        return $this->field_id;
    }


    /**
     * @param integer $field_id
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;
    }


    /**
     * @return string
     */
    public function getLanguageKey()
    {
        return $this->language_key;
    }


    /**
     * @param string $language_key
     */
    public function setLanguageKey($language_key)
    {
        $this->language_key = $language_key;
    }


    /**
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }


    /**
     * @param string $translation
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;
    }


    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
