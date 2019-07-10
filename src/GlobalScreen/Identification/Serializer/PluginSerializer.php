<?php namespace ILIAS\GlobalScreen\Identification\Serializer;

use ilDBConstants;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;
use ILIAS\GlobalScreen\Identification\NullPluginIdentification;
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;
use ILIAS\GlobalScreen\Provider\ProviderFactoryInterface;
use ilPlugin;

/**
 * Class PluginSerializer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginSerializer implements SerializerInterface
{

    const DIVIDER = '|';


    /**
     * @inheritdoc
     */
    public function serialize(IdentificationInterface $identification) : string
    {
        /**
         * @var $identification PluginIdentification
         */
        $divider = self::DIVIDER;

        $str = "{$identification->getPluginId()}{$divider}{$identification->getClassName()}{$divider}{$identification->getInternalIdentifier()}";

        if (strlen($str) > SerializerInterface::MAX_LENGTH) {
            throw new \LogicException("Serialized Identifications MUST be shorter than " . SerializerInterface::MAX_LENGTH . " characters");
        }

        return $str;
    }


    /**
     * @inheritdoc
     */
    public function unserialize(string $serialized_string, IdentificationMap $map, ProviderFactoryInterface $provider_factory) : IdentificationInterface
    {
	    global $DIC;

	    list ($plugin_id, $class_name, $internal_identifier) = explode(self::DIVIDER, $serialized_string);

	    if (!$provider_factory->isInstanceCreationPossible($class_name)) {
		    return new NullPluginIdentification($plugin_id, $serialized_string, $internal_identifier);
	    }

	    $result = $DIC->database()
		    ->queryF('SELECT component_type, component_name, slot_id, name FROM il_plugin WHERE plugin_id=%s', [ ilDBConstants::T_TEXT ], [ $plugin_id ]);

	    $plugin_data = $result->fetchAssoc();

	    if (!$plugin_data) {
		    return new NullPluginIdentification($plugin_id, $serialized_string, $internal_identifier);
	    }

	    $plugin = ilPlugin::getPluginObject($plugin_data["component_type"], $plugin_data["component_name"], $plugin_data["slot_id"], $plugin_data["name"]);

	    $f = new PluginIdentificationProvider($provider_factory->getProviderByClassName($class_name, [ $plugin ]), $plugin_id, $this, $map);

	    return $f->identifier($internal_identifier);
    }


    /**
     * @inheritDoc
     */
    public function canHandle(string $serialized_identification) : bool
    {
        return preg_match('/(.*?)\|(.*?)\|(.*)/m', $serialized_identification) > 0;
    }
}
