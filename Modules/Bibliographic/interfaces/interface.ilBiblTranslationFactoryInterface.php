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
interface ilBiblTranslationFactoryInterface {

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
	 * @return \ilBiblFieldFactoryInterface
	 */
	public function getFieldFactory();


	/**
	 * @param \ilBiblFieldInterface $field
	 *
	 * @return bool
	 */
	public function translationExistsForField(ilBiblFieldInterface $field);


	/**
	 * @param \ilBiblFieldInterface $field
	 *
	 * @throws \ilException when is dows not exists
	 *
	 * @return \ilBiblTranslation
	 */
	public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field);
}