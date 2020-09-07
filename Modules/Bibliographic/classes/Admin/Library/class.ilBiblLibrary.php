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
     * @var int
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
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     50
     * @con_is_notnull true
     */
    protected $name;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     128
     * @con_is_notnull true
     */
    protected $url;
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    128
     */
    protected $img;
    /**
     * @var bool
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     */
    protected $show_in_list;


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
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }


    /**
     * @param string $img
     */
    public function setImg($img)
    {
        $this->img = $img;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return bool
     */
    public function getShowInList()
    {
        return $this->show_in_list;
    }


    /**
     * @param bool $show_in_list
     */
    public function setShowInList($show_in_list)
    {
        $this->show_in_list = $show_in_list;
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
