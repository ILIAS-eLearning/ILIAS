<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class PluginIdentification
 *
 * @see    IdentificationFactory
 * This is a implementation of IdentificationInterface for usage in Plugins
 * (they will get them through the factory or through ilPlugin).
 * This a Serializable and will be used to store in database and cache.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentification implements IdentificationInterface {

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
	 * @var string
	 */
	protected $plugin_id = "";


	/**
	 * CoreIdentification constructor.
	 *
	 * @param string $internal_identifier
	 * @param string $classname
	 * @param string $plugin_id
	 */
	public function __construct(string $internal_identifier, string $classname, string $plugin_id) {
		$this->internal_identifier = $internal_identifier;
		$this->classname = $classname;
		$this->plugin_id = $plugin_id;
	}


	/**
	 * @inheritDoc
	 */
	public function serialize() {
		$divider = self::DIVIDER;

		return "{$this->plugin_id}{$divider}{$this->getClassName()}{$divider}{$this->getInternalIdentifier()}";
	}


	/**
	 * @inheritDoc
	 */
	public function unserialize($serialized) {
		list ($plugin_id, $class_name, $internal_identifier) = explode(self::DIVIDER, $serialized);
		$this->plugin_id = $plugin_id;
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
