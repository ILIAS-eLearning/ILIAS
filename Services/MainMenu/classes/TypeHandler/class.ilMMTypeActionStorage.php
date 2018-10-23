<?php

/**
 * Class ilMMTypeActionStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeActionStorage extends CachedActiveRecord {

	/**
	 * @var string
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     64
	 */
	protected $identifier;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $action = '';
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_mm_actions";


	/**
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->identifier;
	}


	/**
	 * @param string $identifier
	 *
	 * @return ilMMTypeActionStorage
	 */
	public function setIdentifier(string $identifier): ilMMTypeActionStorage {
		$this->identifier = $identifier;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}


	/**
	 * @param string $action
	 *
	 * @return ilMMTypeActionStorage
	 */
	public function setAction(string $action): ilMMTypeActionStorage {
		$this->action = $action;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getCache(): ilGlobalCache {
		global $DIC;

		return $DIC->globalScreen()->storage()->cache();
	}


	/**
	 * @return ilMMTypeActionStorage
	 */
	public static function find($primary_key, array $add_constructor_args = array()) {
		$parent = parent::find($primary_key, $add_constructor_args);
		if ($parent === null) {
			$parent = new self();
			$parent->setIdentifier($primary_key);
			$parent->create();
		}

		return $parent;
	}
}
