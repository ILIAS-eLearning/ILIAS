<?php
require_once("./Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilStudyProgrammeAdvancedMetadataRecord
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilStudyProgrammeAdvancedMetadataRecord extends ActiveRecord
{
    protected $connector_container_name = 'prg_type_adv_md_rec';

    /**
     *
     * @var int
     *
     * @con_is_primary  true
     * @con_sequence    true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $id;

    /**
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $type_id;

    /**
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $rec_id;

    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return 'prg_type_adv_md_rec';
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->type_id;
    }


    /**
     * @param int $type_id
     */
    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;
    }


    /**
     * @return int
     */
    public function getRecId()
    {
        return $this->rec_id;
    }


    /**
     * @param int $rec_id
     */
    public function setRecId($rec_id)
    {
        $this->rec_id = $rec_id;
    }
}
