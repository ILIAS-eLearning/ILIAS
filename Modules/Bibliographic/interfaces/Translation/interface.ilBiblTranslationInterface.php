<?php

/**
 * Interface ilBiblTranslationInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTranslationInterface
{

    /**
     * @return integer
     */
    public function getId();


    /**
     * @param integer $id
     */
    public function setId($id);


    /**
     * @return integer
     */
    public function getFieldId();


    /**
     * @param integer $field_id
     */
    public function setFieldId($field_id);


    /**
     * @return string
     */
    public function getLanguageKey();


    /**
     * @param string $language_key
     */
    public function setLanguageKey($language_key);


    /**
     * @return string
     */
    public function getTranslation();


    /**
     * @param string $translation
     */
    public function setTranslation($translation);


    /**
     * @return mixed
     */
    public function getDescription();


    /**
     * @param mixed $description
     */
    public function setDescription($description);


    /**
     * @return void
     */
    public function store();
}
