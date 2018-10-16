<?php

/**
 * Class ilMMItemStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemStorage extends CachedActiveRecord {

	/**
	 * @inheritDoc
	 */
	public function getCache(): ilGlobalCache {
		global $DIC;

		return $DIC->globalScreen()->storage()->cache();
	}


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
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $active = true;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $position = 0;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $parent_identification = '';
	/**
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $sticky = false;
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_mm_items";


	/**
	 * @return string
	 */
	public function getIdentification(): string {
		return $this->identification;
	}


	/**
	 * @param string $identification
	 */
	public function setIdentification(string $identification) {
		$this->identification = $identification;
	}


	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->active;
	}


	/**
	 * @param bool $active
	 */
	public function setActive(bool $active) {
		$this->active = $active;
	}


	/**
	 * @return int
	 */
	public function getPosition(): int {
		return $this->position;
	}


	/**
	 * @param int $position
	 */
	public function setPosition(int $position) {
		$this->position = $position;
	}


	/**
	 * @return string
	 */
	public function getParentIdentification(): string {
		return $this->parent_identification;
	}


	/**
	 * @param string $parent_identification
	 */
	public function setParentIdentification(string $parent_identification) {
		$this->parent_identification = $parent_identification;
	}


	/**
	 * @return bool
	 */
	public function isSticky(): bool {
		return $this->sticky;
	}


	/**
	 * @param bool $sticky
	 */
	public function setSticky(bool $sticky) {
		$this->sticky = $sticky;
	}
}
