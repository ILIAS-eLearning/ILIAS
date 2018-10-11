<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;

/**
 * Class PluginIdentification
 *
 * @see    IdentificationFactory
 * This is a implementation of IdentificationInterface for usage in Plugins
 * (they will get them through the factory or through ilPlugin).
 * This a Serializable and will be used to store in database and cache.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentification extends AbstractIdentification implements IdentificationInterface {

	/**
	 * @var string
	 */
	protected $plugin_id = "";


	/**
	 * PluginIdentification constructor.
	 *
	 * @param string              $internal_identifier
	 * @param string              $classname
	 * @param string              $plugin_id
	 * @param SerializerInterface $serializer
	 */
	public function __construct(string $internal_identifier, string $classname, string $plugin_id, SerializerInterface $serializer) {
		$this->plugin_id = $plugin_id;
		parent::__construct($internal_identifier, $classname, $serializer);
	}


	/**
	 * @return string
	 */
	public function getPluginId(): string {
		return $this->plugin_id;
	}
}
