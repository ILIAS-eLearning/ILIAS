<?php

/**
 * Interface ilBiblLibraryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblLibraryInterface
{

    /**
     * @return int
     */
    public function getId();


    /**
     * @param int $id
     */
    public function setId($id);


    /**
     * @return string
     */
    public function getImg();


    /**
     * @param string $img
     */
    public function setImg($img);


    /**
     * @return string
     */
    public function getName();


    /**
     * @param string $name
     */
    public function setName($name);


    /**
     * @return bool
     */
    public function getShowInList();


    /**
     * @param bool $show_in_list
     */
    public function setShowInList($show_in_list);


    /**
     * @return string
     */
    public function getUrl();


    /**
     * @param string $url
     */
    public function setUrl($url);


    public function store();


    public function delete();


    public function create();


    public function update();
}
