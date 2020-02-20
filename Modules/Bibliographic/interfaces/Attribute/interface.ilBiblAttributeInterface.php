<?php
/**
 * Interface ilBiblAttributeInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

interface ilBiblAttributeInterface
{

    /**
     * @return int
     */
    public function getEntryId();


    /**
     * @param int $entry_id
     */
    public function setEntryId($entry_id);


    /**
     * @return string
     */
    public function getName();


    /**
     * @param string $name
     */
    public function setName($name);


    /**
     * @return string
     */
    public function getValue();


    /**
     * @param string $value
     */
    public function setValue($value);


    /**
     * @return int
     */
    public function getId();


    /**
     * @param int $id
     */
    public function setId($id);
}
