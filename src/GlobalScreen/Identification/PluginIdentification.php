<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;

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
	 * @param Provider            $provider
	 * @param string              $plugin_id
	 * @param SerializerInterface $serializer
	 */
	public function __construct(string $internal_identifier, Provider $provider, string $plugin_id, SerializerInterface $serializer) {
		$this->plugin_id = $plugin_id;
		parent::__construct($internal_identifier, get_class($provider), $serializer, $provider->getProviderNameForPresentation());
	}


	/**
	 * @return string
	 */
	public function getPluginId(): string {
		return $this->plugin_id;
	}
}
