<?php namespace ILIAS\UX\Identification;

/**
 * Class DynamicProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Core implements ProviderInterface {

	/**
	 * @var string
	 */
	protected $class_name = '';


	/**
	 * DynamicProvider constructor.
	 *
	 * @param string $class_name
	 */
	public function __construct(string $class_name) { $this->class_name = $class_name; }


	/**
	 * @param string $identifier
	 *
	 * @return Identification$
	 */
	public function internal(string $identifier): IdentificationInterface {
		return new Identification($identifier, $this->class_name);
	}
}
