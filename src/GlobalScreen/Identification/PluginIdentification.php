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
class PluginIdentification extends AbstractIdentification implements IdentificationInterface
{

    /**
     * @var string
     */
    protected $plugin_id = "";


    /**
     * @inheritDoc
     */
    public function __construct(string $plugin_id, string $internal_identifier, string $classname, SerializerInterface $serializer, string $provider_presentation_name)
    {
        parent::__construct($internal_identifier, $classname, $serializer, $provider_presentation_name);
        $this->plugin_id = $plugin_id;
    }


    /**
     * @return string
     */
    public function getPluginId() : string
    {
        return $this->plugin_id;
    }


    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return $this->plugin_id;
    }
}
