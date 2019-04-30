<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;


use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MetaBarProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MetaBarProviderInterface {

	/**
	 * @return IdentificationInterface[]
	 */
	public function getAllIdentifications(): array;


	/**
	 * @return string
	 */
	public function getProviderNameForPresentation(): string;
}
