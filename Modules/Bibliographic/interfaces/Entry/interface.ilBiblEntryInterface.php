<?php

/**
 * Interface ilBiblEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryInterface
{

    /**
     * @param int $id
     */
    public function setId($id);


    /**
     * @return int
     */
    public function getId();

    /**
     * @return integer
     */
    public function getDataId();


    /**
     * @param integer $data_id
     */
    public function setDataId($data_id);

    /**
     * @return string
     */
    public function getType();


    /**
     * @param string $type
     */
    public function setType($type);
}
