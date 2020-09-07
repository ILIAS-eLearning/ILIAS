<?php
/**
 * Class ilBiblAttribute
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAttribute extends ActiveRecord implements ilBiblAttributeInterface
{

    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'il_bibl_attribute';
    }


    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return 'il_bibl_attribute';
    }


    /**
     * @var
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    11
     */
    protected $entry_id;
    /**
     * @var
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $name;
    /**
     * @var
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    4000
     */
    protected $value;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence  true
     */
    protected $id;


    /**
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entry_id;
    }


    /**
     * @param mixed $entry_id
     */
    public function setEntryId($entry_id)
    {
        $this->entry_id = $entry_id;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
