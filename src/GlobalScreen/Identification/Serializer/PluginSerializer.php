<?php namespace ILIAS\GlobalScreen\Identification\Serializer;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\PluginIdentification;

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
	public function unserialize(string $serialized_string): IdentificationInterface {
		global $DIC;
		list ($plugin_id, $class_name, $internal_identifier) = explode(self::DIVIDER, $serialized_string);

		return new PluginIdentification($internal_identifier, new $class_name($DIC), $plugin_id, $this);
	}


	/**
	 * @inheritDoc
	 */
	public function canHandle(string $serialized_identification): bool {
		return preg_match('/(.*?)\|(.*?)\|(.*)/m', $serialized_identification) > 0;
	}
}
