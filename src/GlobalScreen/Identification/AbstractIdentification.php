<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;

/**
 * Class AbstractIdentification
 *
 * @package ILIAS\GlobalScreen\Identification
 */
abstract class AbstractIdentification implements IdentificationInterface {

	/**
	 * @var SerializerInterface
	 */
	protected $serializer;
	/**
	 * @var string
	 */
	protected $internal_identifier = '';
	/**
	 * @var string
	 */
	protected $classname = '';


	/**
	 * CoreIdentification constructor.
	 *
	 * @param string              $internal_identifier
	 * @param string              $classname
	 * @param SerializerInterface $serializer
	 */
	public function __construct(string $internal_identifier, string $classname, SerializerInterface $serializer) {
		$this->serializer = $serializer;
		$this->internal_identifier = $internal_identifier;
		$this->classname = $classname;
	}


	/**
	 * @inheritDoc
	 */
	public function serialize() {
		return $this->serializer->serialize($this);
	}


	/**
	 * @inheritDoc
	 */
	public function unserialize($serialized) {
		throw new \LogicException("Please use the identification factory to unserialize");
	}


	/**
	 * @inheritDoc
	 */
	public function getClassName(): string {
		return $this->classname;
	}


	/**
	 * @inheritDoc
	 */
	public function getInternalIdentifier(): string {
		return $this->internal_identifier;
	}
}
