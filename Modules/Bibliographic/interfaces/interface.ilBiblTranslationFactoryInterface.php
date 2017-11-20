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
	 * @param \ilBiblFieldInterface $field
	 *
	 * @return string
	 */
	public function translate(ilBiblFieldInterface $field);


	/**
	 * @return \ilBiblFieldFactoryInterface
	 */
	public function getFieldFactory();
}