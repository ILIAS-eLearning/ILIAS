<?php
/**
 * Created by PhpStorm.
 * User: fschmid
 * Date: 20.11.17
 * Time: 16:38
 */

/**
 * Class ilBiblTranslationFactory
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
     */
    public function translate(ilBiblFieldInterface $field) : string;

    public function translateAttribute(ilBiblAttributeInterface $attribute) : string;

    public function translateAttributeString(int $type_id, ilBiblAttributeInterface $attribute) : string;

    public function getFieldFactory() : \ilBiblFieldFactoryInterface;

    /**
     * @param \ilBiblFieldInterface $field
     * @return bool
     */
    public function translationExistsForFieldAndUsersLanguage(ilBiblFieldInterface $field) : bool;

    /**
     * @param \ilBiblFieldInterface $field
     * @return bool
     */
    public function translationExistsForFieldAndSystemsLanguage(ilBiblFieldInterface $field) : bool;

    /**
     * @param \ilBiblFieldInterface $field
     * @return bool
     */
    public function translationExistsForField(ilBiblFieldInterface $field) : bool;

    /**
     * @param \ilBiblFieldInterface $field
     * @return \ilBiblTranslationInterface|null
     */
    public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field) : ?\ilBiblTranslationInterface;

    /**
     * @param \ilBiblFieldInterface $field
     * @return \ilBiblTranslationInterface|null
     */
    public function getInstanceForFieldAndSystemsLanguage(ilBiblFieldInterface $field) : ?\ilBiblTranslationInterface;

    /**
     * @return \ilBiblTranslationInterface
     */
    public function findArCreateInstanceForFieldAndlanguage(
        ilBiblFieldInterface $field,
        string $language_key
    ) : \ilBiblTranslationInterface;

    /**
     * @return \ilBiblTranslationInterface[]
     */
    public function getAllTranslationsForField(ilBiblFieldInterface $field) : array;

    /**
     * @param \ilBiblFieldInterface $field
     * @return string[]
     */
    public function getAllTranslationsForFieldAsArray(ilBiblFieldInterface $field) : array;

    public function findById(int $id) : \ilBiblTranslationInterface;

    public function deleteById(int $id) : bool;
}
