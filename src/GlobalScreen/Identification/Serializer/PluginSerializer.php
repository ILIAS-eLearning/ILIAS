<?php namespace ILIAS\GlobalScreen\Identification\Serializer;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;

/**
 * Class PluginSerializer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginSerializer implements SerializerInterface {

	const DIVIDER = '|';


	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return string
	 */
	public function serialize(IdentificationInterface $identification): string {
		/**
		 * @var $identification PluginIdentification
		 */
		$divider = self::DIVIDER;

		return "{$identification->getPluginId()}{$divider}{$identification->getClassName()}{$divider}{$identification->getInternalIdentifier()}";
	}


	/**
	 * @inheritdoc
	 */
	public function unserialize(string $serialized_string, IdentificationMap $map): IdentificationInterface {
		global $DIC;
		list ($plugin_id, $class_name, $internal_identifier) = explode(self::DIVIDER, $serialized_string);

		$f = new PluginIdentificationProvider(new $class_name($DIC), $plugin_id, $this, $map);

		return $f->identifier($internal_identifier);
	}


	/**
	 * @inheritDoc
	 */
	public function canHandle(string $serialized_identification): bool {
		return preg_match('/(.*?)\|(.*?)\|(.*)/m', $serialized_identification) > 0;
	}
}
