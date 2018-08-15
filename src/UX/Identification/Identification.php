<?php namespace ILIAS\UX\Identification;

/**
 * Class Identification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Identification implements IdentificationInterface {

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
	 * Identification constructor.
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
