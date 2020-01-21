<?php namespace ILIAS\GlobalScreen\Identification\Serializer;

/**
 * Class SerializerFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SerializerFactory
{

    /**
     * @var CoreSerializer
     */
    private static $core_instance;
    /**
     * @var PluginSerializer
     */
    private static $plugin_instance;


    /**
     * @return SerializerInterface
     */
    public function core() : SerializerInterface
    {
        if (!isset(self::$core_instance)) {
            self::$core_instance = new CoreSerializer();
        }

        return self::$core_instance;
    }


    /**
     * @return SerializerInterface
     */
    public function plugin() : SerializerInterface
    {
        if (!isset(self::$plugin_instance)) {
            self::$plugin_instance = new PluginSerializer();
        }

        return self::$plugin_instance;
    }


    /**
     * @param string $serialized_identification
     *
     * @return SerializerInterface
     */
    public function fromSerializedIdentification(string $serialized_identification) : SerializerInterface
    {
        $plugin = $this->plugin();
        if ($plugin->canHandle($serialized_identification)) {
            return $plugin;
        }

        $core = $this->core();
        if ($core->canHandle($serialized_identification)) {
            return $core;
        }

        throw new \InvalidArgumentException("Nobody can handle serialized identification '$serialized_identification'.");
    }
}
