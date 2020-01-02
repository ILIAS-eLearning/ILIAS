<?php
/**
 * Created by PhpStorm.
 * User: fschmid
 * Date: 20.11.17
 * Time: 16:38
 */

/**
 * Class ilBiblTranslationFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTranslationFactoryInterface
{

    /**
     * Translates a field in the current users language in tho folowing order:
     * - if standard-field, user ILIAS ilLanguage
     * - if a explicit translation in the users language is available, use this
     * - if a translation is only for the systems language, us this
     * - return string like "bib_year"
     *
     * @param \ilBiblFieldInterface $field
     *
     * @return string
     */
    public function translate(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblAttributeInterface $attribute
     *
     * @return string
     */
    public function translateAttribute(ilBiblAttributeInterface $attribute);


    /**
     * @param int    $type_id
     * @param ilBiblAttributeInterface $attribute
     *
     * @return string
     */
    public function translateAttributeString($type_id, ilBiblAttributeInterface $attribute);


    /**
     * @return \ilBiblFieldFactoryInterface
     */
    public function getFieldFactory();


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return bool
     */
    public function translationExistsForFieldAndUsersLanguage(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return bool
     */
    public function translationExistsForFieldAndSystemsLanguage(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return bool
     */
    public function translationExistsForField(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return \ilBiblTranslationInterface
     */
    public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return \ilBiblTranslationInterface
     */
    public function getInstanceForFieldAndSystemsLanguage(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblFieldInterface $field
     * @param string                $language_key
     *
     * @return \ilBiblTranslationInterface
     */
    public function findArCreateInstanceForFieldAndlanguage(ilBiblFieldInterface $field, $language_key);


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return \ilBiblTranslationInterface[]
     */
    public function getAllTranslationsForField(ilBiblFieldInterface $field);


    /**
     * @param \ilBiblFieldInterface $field
     *
     * @return array
     */
    public function getAllTranslationsForFieldAsArray(ilBiblFieldInterface $field);


    /**
     * @param int $id
     *
     * @return \ilBiblTranslationInterface
     */
    public function findById($id);


    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteById($id);
}
