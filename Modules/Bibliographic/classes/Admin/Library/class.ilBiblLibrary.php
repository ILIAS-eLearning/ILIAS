<?php

/**
 * Class ilBiblLibrary
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblLibrary extends ActiveRecord implements ilBiblLibraryInterface
{
    const TABLE_NAME = 'il_bibl_settings';


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


    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    /**
     * @return string|null
     */
    public function getImg() : ?string
    {
        return $this->img;
    }


    /**
     * @param string $img
     */
    public function setImg(string $img) : void
    {
        $this->img = $img;
    }


    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }


    /**
     * @return bool
     */
    public function isShownInList() : bool
    {
        return $this->show_in_list;
    }


    /**
     * @param bool $show_in_list
     */
    public function setShowInList(bool $show_in_list) : void
    {
        $this->show_in_list = $show_in_list;
    }


    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }


    /**
     * @param string $url
     */
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }
}
