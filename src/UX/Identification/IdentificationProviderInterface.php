<?php namespace ILIAS\UX\Identification;

/**
 * Class IdentificationProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface IdentificationProviderInterface {

	/**
	 * @param string $identifier_string this is a identifier which is only known
	 *                                  to your component. The UX services uses
	 *                                  this string together with e.g. the
	 *                                  classname of your provider to stack
	 *                                  items or to ask your provider for f
	 *                                  urther infos.
	 *
	 * @return IdentificationInterface use this CoreIdentification to put into your
	 *                                 UX-elements.
	 */
	public function identifier(string $identifier_string): IdentificationInterface;
}
