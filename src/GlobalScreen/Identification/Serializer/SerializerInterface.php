<?php namespace ILIAS\GlobalScreen\Identification\Serializer;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Interface SerializerInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface SerializerInterface {

	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return string
	 */
	public function serialize(IdentificationInterface $identification): string;


	/**
	 * @param string $serialized_string
	 *
	 * @return IdentificationInterface
	 */
	public function unserialize(string $serialized_string): IdentificationInterface;


	/**
	 * @param string $serialized_identification
	 *
	 * @return bool
	 */
	public function canHandle(string $serialized_identification): bool;
}
