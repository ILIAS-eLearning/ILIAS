<?php
/**
 * Class ilField
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblField extends ActiveRecord implements ilBiblFieldInterface
{
    const TABLE_NAME = 'il_bibl_field';


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
     * @con_is_unique  true
     * @con_sequence   true
     */
    protected $id;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     50
     * @con_is_notnull true
     */
    protected $identifier;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $data_type;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     3
     */
    protected $position;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $is_standard_field;


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
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }


    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }


    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }


    /**
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }


    /**
     * @return integer
     */
    public function getisStandardField()
    {
        return $this->is_standard_field;
    }


    /**
     * @param integer $is_standard_field
     */
    public function setIsStandardField($is_standard_field)
    {
        $this->is_standard_field = $is_standard_field;
    }


    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->data_type;
    }


    /**
     * @param mixed $data_type
     */
    public function setDataType($data_type)
    {
        $this->data_type = $data_type;
    }
}
