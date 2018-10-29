<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Services;

/**
 * Interface Provider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Provider {

	/**
	 * @return string
	 */
	public function getFullyQualifiedClassName(): string;


	/**
	 * @return IdentificationInterface[]
	 */
	public function getAllIdentifications(): array;


	/**
	 * @return string
	 */
	public function getProviderNameForPresentation(): string;
}
