<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;

/**
 * Class PluginIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentificationProvider implements IdentificationProviderInterface {

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
	 * @param string              $class_name
	 * @param string              $plugin_id
	 * @param SerializerInterface $serializer
	 */
	public function __construct(string $class_name, string $plugin_id, SerializerInterface $serializer) {
		$this->class_name = $class_name;
		$this->plugin_id = $plugin_id;
		$this->serializer = $serializer;
	}


	/**
	 * @inheritdoc
	 */
	public function identifier(string $identifier_string): IdentificationInterface {
		return new PluginIdentification($identifier_string, $this->class_name, $this->plugin_id, $this->serializer);
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
