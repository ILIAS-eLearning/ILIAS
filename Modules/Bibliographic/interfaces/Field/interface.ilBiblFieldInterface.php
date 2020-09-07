<?php

/**
 * Interface ilBiblEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldInterface
{
    const DATA_TYPE_RIS = 1;
    const DATA_TYPE_BIBTEX = 2;


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
    public function getIdentifier();


    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier);


    /**
     * @return int
     */
    public function getPosition();


    /**
     * @param int $position
     */
    public function setPosition($position);



    /**
     * @return int
     */
    public function getIsStandardField();


    /**
     * @param int $is_standard_field
     */
    public function setIsStandardField($is_standard_field);


    /**
     * @return int
     */
    public function getDataType();


    /**
     * @param int $data_type
     */
    public function setDataType($data_type);


    /**
     * Stores the Object, creates a newone in Db if non existing or updates an existing
     *
     * @return void
     */
    public function store();
}
