<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class NullPluginIdentification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullPluginIdentification implements IdentificationInterface
{

    /**
     * @var string
     */
    private $internal_identifier = "unknown";
    /**
     * @var string
     */
    private $identification = "unknown";
    /**
     * @var string
     */
    private $plugin_id = "unknown";


    /**
     * NullPluginIdentification constructor.
     *
     * @param string $plugin_id
     * @param string $identification
     * @param string $internal_identifier
     */
    public function __construct(string $plugin_id, string $identification = "", string $internal_identifier = "")
    {
        $this->plugin_id = $plugin_id;
        $this->identification = $identification;
        $this->internal_identifier = $internal_identifier;
    }


    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return $this->identification;
    }


    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        return;
    }


    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        return $this->plugin_id;
    }


    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        return $this->internal_identifier;
    }


    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return $this->plugin_id;
    }
}
