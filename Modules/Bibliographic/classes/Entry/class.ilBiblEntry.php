<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBiblEntry
 * @author     Gabriel Comte
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntry extends ActiveRecord implements ilBiblEntryInterface
{
    const TABLE_NAME = 'il_bibl_entry';
    
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
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected ?int $data_id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     50
     * @con_is_notnull true
     */
    protected ?string $type = null;
    
    protected string $overview = '';
    
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
     * @return int
     */
    public function getDataId() : int
    {
        return $this->data_id;
    }
    
    /**
     * @param integer $data_id
     */
    public function setDataId(int $data_id) : void
    {
        $this->data_id = $data_id;
    }
    
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    /**
     * @param string $type
     */
    public function setType(string $type) : void
    {
        $this->type = $type;
    }
    
    public function getOverview() : string
    {
        return $this->overview;
    }
}
