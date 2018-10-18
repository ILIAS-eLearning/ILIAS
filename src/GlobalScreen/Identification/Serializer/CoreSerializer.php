<?php namespace ILIAS\GlobalScreen\Identification\Serializer;

use ILIAS\GlobalScreen\Identification\CoreIdentification;
use ILIAS\GlobalScreen\Identification\CoreIdentificationProvider;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;

/**
 * Class CoreSerializer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreSerializer implements SerializerInterface {

	const DIVIDER = '|';


	/**
	 * @inheritdoc
	 */
	public function serialize(IdentificationInterface $identification): string {
		$divider = self::DIVIDER;

		return "{$identification->getClassName()}{$divider}{$identification->getInternalIdentifier()}";
	}


	/**
	 * @inheritdoc
	 */
	public function unserialize(string $serialized_string, IdentificationMap $map): IdentificationInterface {
		global $DIC;
		list ($class_name, $internal_identifier) = explode(self::DIVIDER, $serialized_string);

		$f = new CoreIdentificationProvider(new $class_name($DIC), $this, $map);

		return $f->identifier($internal_identifier);
	}


	/**
	 * @inheritDoc
	 */
	public function canHandle(string $serialized_identification): bool {
		return preg_match('/(.*?)\|(.*)/m', $serialized_identification) > 0;
	}
}
