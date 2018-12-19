<?php

/**
 * Class ilMMCustomItemStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomItemStorage extends CachedActiveRecord {

	/**
	 * @var string
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $identifier = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     128
	 */
	protected $type = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $action = "";
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $default_title = "";
	/**
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $top_item = false;
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_mm_custom_items";


	/**
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->identifier;
	}


	/**
	 * @param string $identifier
	 *
	 * @return ilMMCustomItemStorage
	 */
	public function setIdentifier(string $identifier): ilMMCustomItemStorage {
		$this->identifier = $identifier;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}


	/**
	 * @param string $type
	 *
	 * @return ilMMCustomItemStorage
	 */
	public function setType(string $type): ilMMCustomItemStorage {
		$this->type = $type;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isTopItem(): bool {
		return $this->top_item;
	}


	/**
	 * @param bool $top_item
	 *
	 * @return ilMMCustomItemStorage
	 */
	public function setTopItem(bool $top_item): ilMMCustomItemStorage {
		$this->top_item = $top_item;

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
	 * @return ilMMCustomItemStorage
	 */
	public function setAction(string $action): ilMMCustomItemStorage {
		$this->action = $action;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getDefaultTitle(): string {
		return $this->default_title;
	}


	/**
	 * @param string $default_title
	 *
	 * @return ilMMCustomItemStorage
	 */
	public function setDefaultTitle(string $default_title): ilMMCustomItemStorage {
		$this->default_title = $default_title;

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
