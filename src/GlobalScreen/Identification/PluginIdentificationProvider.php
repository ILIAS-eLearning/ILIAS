<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class PluginIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentificationProvider implements IdentificationProviderInterface {

	/**
	 * @var Provider
	 */
	protected $provider;
	/**
	 * @var SerializerInterface
	 */
	protected $serializer;
	/**
	 * @var string
	 */
	protected $class_name = '';
	/**
	 * @var string
	 */
	protected $plugin_id = "";


	/**
	 * PluginIdentificationProvider constructor.
	 *
	 * @param Provider            $provider
	 * @param string              $plugin_id
	 * @param SerializerInterface $serializer
	 */
	public function __construct(Provider $provider, string $plugin_id, SerializerInterface $serializer) {
		$this->provider = $provider;
		$this->class_name = get_class($provider);
		$this->plugin_id = $plugin_id;
		$this->serializer = $serializer;
	}


	/**
	 * @inheritdoc
	 */
	public function identifier(string $identifier_string): IdentificationInterface {
		$this->provider->getProviderNameForPresentation();
		exit;
		return new PluginIdentification($this->plugin_id, $identifier_string, $this->class_name, $this->serializer, $this->provider->getProviderNameForPresentation());
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
