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
	protected $identification;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $action = '';
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_mm_actions";


	/**
	 * @return string
	 */
	public function getIdentification(): string {
		return $this->identification;
	}


	/**
	 * @param string $identification
	 *
	 * @return ilMMTypeActionStorage
	 */
	public function setIdentification(string $identification): ilMMTypeActionStorage {
		$this->identification = $identification;

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
}
