<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class CoreIdentification
 *
 * @see IdentificationFactory
 * This is a implementation of IdentificationInterface for usage in Core
 * components (they will get them through the factory). This a Serializable and
 * will be used to store in database and cache.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentification implements IdentificationInterface {

	const DIVIDER = '|';
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
	 * @param string $internal_identifier
	 * @param string $classname
	 */
	public function __construct(string $internal_identifier, string $classname) {
		$this->internal_identifier = $internal_identifier;
		$this->classname = $classname;
	}


	/**
	 * @inheritDoc
	 */
	public function serialize() {
		$divider = self::DIVIDER;

		return "{$this->getClassName()}{$divider}{$this->getInternalIdentifier()}";
	}


	/**
	 * @inheritDoc
	 */
	public function unserialize($serialized) {
		list ($class_name, $internal_identifier) = explode(self::DIVIDER, $serialized);
		$this->classname = $class_name;
		$this->internal_identifier = $internal_identifier;
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
