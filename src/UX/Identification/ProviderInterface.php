<?php namespace ILIAS\UX\Identification;

/**
 * Class ProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderInterface {

	/**
	 * @param string $identifier
	 *
	 * @return IdentificationInterface
	 */
	public function internal(string $identifier): IdentificationInterface;
}
