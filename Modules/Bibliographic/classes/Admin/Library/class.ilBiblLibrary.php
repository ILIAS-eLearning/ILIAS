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
 
/**
 * Class ilBiblLibrary
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblLibrary extends ActiveRecord implements ilBiblLibraryInterface
{
    const TABLE_NAME = 'il_bibl_settings';


    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }


    public function getConnectorContainerName() : string
    {
        return self::TABLE_NAME;
    }


    /**
     *
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
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     50
     * @con_is_notnull true
     */
    protected ?string $name = null;
    /**
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     128
     * @con_is_notnull true
     */
    protected ?string $url = null;
    /**
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    128
     */
    protected ?string $img = null;
    /**
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     */
    protected ?bool $show_in_list = null;


    public function getId() : ?int
    {
        return $this->id;
    }


    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    public function getImg() : ?string
    {
        return $this->img;
    }


    public function setImg(string $img) : void
    {
        $this->img = $img;
    }


    public function getName() : string
    {
        return $this->name;
    }


    public function setName(string $name) : void
    {
        $this->name = $name;
    }


    public function isShownInList() : bool
    {
        return $this->show_in_list;
    }


    public function setShowInList(bool $show_in_list) : void
    {
        $this->show_in_list = $show_in_list;
    }


    public function getUrl() : string
    {
        return $this->url;
    }


    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }
}
