<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerFactory;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class CoreIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentificationProvider implements IdentificationProviderInterface {

	protected $provider;
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
	 * @param Provider            $provider
	 * @param SerializerInterface $serializer
	 */
	public function __construct(Provider $provider, SerializerInterface $serializer) {
		$this->provider = $provider;
		$this->class_name = get_class($provider);
		$this->serializer = $serializer;;
	}


	/**
	 * @inheritdoc
	 */
	public function identifier(string $identifier_string): IdentificationInterface {
		if (isset(self::$instances[$identifier_string])) {
			return self::$instances[$identifier_string];
		}

		return self::$instances[$identifier_string] = new CoreIdentification($identifier_string, $this->class_name, $this->serializer, $this->provider->getProviderNameForPresentation());
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
