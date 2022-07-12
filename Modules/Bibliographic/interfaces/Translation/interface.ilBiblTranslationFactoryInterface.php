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

    public function translationExistsForFieldAndUsersLanguage(ilBiblFieldInterface $field) : bool;

    public function translationExistsForFieldAndSystemsLanguage(ilBiblFieldInterface $field) : bool;

    public function translationExistsForField(ilBiblFieldInterface $field) : bool;

    public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field) : ?\ilBiblTranslationInterface;

    public function getInstanceForFieldAndSystemsLanguage(ilBiblFieldInterface $field) : ?\ilBiblTranslationInterface;

    public function findArCreateInstanceForFieldAndlanguage(
        ilBiblFieldInterface $field,
        string $language_key
    ) : \ilBiblTranslationInterface;

    /**
     * @return \ilBiblTranslationInterface[]
     */
    public function getAllTranslationsForField(ilBiblFieldInterface $field) : array;

    /**
     * @return string[]
     */
    public function getAllTranslationsForFieldAsArray(ilBiblFieldInterface $field) : array;

    public function findById(int $id) : \ilBiblTranslationInterface;

    public function deleteById(int $id) : bool;
}
