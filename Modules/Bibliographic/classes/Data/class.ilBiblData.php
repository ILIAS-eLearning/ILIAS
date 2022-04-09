<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

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
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }


    /**
     * @return string
     */
    public function getConnectorContainerName() : string
    {
        return self::TABLE_NAME;
    }


    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     */
    protected ?int $id = null;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     * @con_is_notnull true
     */
    protected ?string $filename = null;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected ?int $is_online = null;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected ?int $file_type = null;

    /**
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     255
     * @con_is_notnull true
     */
    protected ?string $rid = null;

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }


    /**
     * @param integer $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    /**
     * @return string|null
     */
    public function getFilename() : ?string
    {
        return $this->filename;
    }


    /**
     * @param string $filename
     */
    public function setFilename(string $filename) : void
    {
        $this->filename = $filename;
    }
    
    public function isOnline() : bool
    {
        return (bool) $this->is_online;
    }


    /**
     * @param integer $is_online
     */
    public function setIsOnline(int $is_online) : void
    {
        $this->is_online = $is_online;
    }


    /**
     * @return int
     */
    public function getFileType() : int
    {
        return $this->file_type;
    }


    /**
     * @param integer $file_type
     */
    public function setFileType(int $file_type) : void
    {
        $this->file_type = $file_type;
    }

    /**
     * @return string
     */
    public function getResourceId() : ?string
    {
        return $this->rid;
    }

    public function setResourceId(string $rid) : self
    {
        $this->rid = $rid;
        return $this;
    }
}
