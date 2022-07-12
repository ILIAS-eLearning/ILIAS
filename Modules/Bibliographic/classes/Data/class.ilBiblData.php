<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilBiblData
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblData extends ActiveRecord implements ilBiblDataInterface
{
    const TABLE_NAME = 'il_bibl_data';


    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }


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
     * @con_fieldtype  text
     * @con_length     256
     * @con_is_notnull true
     */
    protected ?string $filename = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected ?int $is_online = null;
    /**
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

    public function getId() : ?int
    {
        return $this->id;
    }


    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    public function getFilename() : ?string
    {
        return $this->filename;
    }


    public function setFilename(string $filename) : void
    {
        $this->filename = $filename;
    }
    
    public function isOnline() : bool
    {
        return (bool) $this->is_online;
    }


    public function setIsOnline(int $is_online) : void
    {
        $this->is_online = $is_online;
    }


    public function getFileType() : int
    {
        return $this->file_type;
    }


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
