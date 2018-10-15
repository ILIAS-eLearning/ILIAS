<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerFactory;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;

/**
 * Class CoreIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentificationProvider implements IdentificationProviderInterface {

	/**
	 * @var Serializer\SerializerInterface
	 */
	private $serializer;
	/**
	 * @var string
	 */
	protected $class_name = '';
	/**
	 * @var IdentificationInterface[]
	 */
	protected static $instances = [];


	/**
	 * CoreIdentificationProvider constructor.
	 *
	 * @param string              $class_name
	 * @param SerializerInterface $serializer
	 */
	public function __construct(string $class_name, SerializerInterface $serializer) {
		$this->class_name = $class_name;
		$this->serializer = $serializer;;
	}


	/**
	 * @inheritdoc
	 */
	public function identifier(string $identifier_string): IdentificationInterface {
		if (isset(self::$instances[$identifier_string])) {
			return self::$instances[$identifier_string];
		}

		return self::$instances[$identifier_string] = new CoreIdentification($identifier_string, $this->class_name, $this->serializer);
	}


	/**
	 * @param string $serialized_string
	 *
	 * @return IdentificationInterface
	 */
	public function fromSerializedString(string $serialized_string): IdentificationInterface {
		return $this->serializer->unserialize($serialized_string);
	}
}
