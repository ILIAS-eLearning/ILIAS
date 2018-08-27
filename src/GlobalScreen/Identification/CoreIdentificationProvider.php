<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class CoreIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentificationProvider implements IdentificationProviderInterface {

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
	 * @inheritdoc
	 */
	public function identifier(string $identifier_string): IdentificationInterface {
		return new CoreIdentification($identifier_string, $this->class_name);
	}
}
