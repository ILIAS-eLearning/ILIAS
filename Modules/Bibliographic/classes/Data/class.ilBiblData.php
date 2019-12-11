<?php
/**
 * Class ilBiblData
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblData extends ActiveRecord implements ilBiblDataInterface
{
    const TABLE_NAME = 'il_bibl_data';

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
     * @con_length     256
     * @con_is_notnull true
     */
    protected $filename;

    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $is_online;

    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $file_type;


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
    public function getFilename()
    {
        return $this->filename;
    }


    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }


    /**
     * @return integer
     */
    public function getIsOnline()
    {
        return $this->is_online;
    }


    /**
     * @param integer $is_online
     */
    public function setIsOnline($is_online)
    {
        $this->is_online = $is_online;
    }


    /**
     * @return integer
     */
    public function getFileType()
    {
        return $this->file_type;
    }


    /**
     * @param integer $file_type
     */
    public function setFileType($file_type)
    {
        $this->file_type = $file_type;
    }
}
