<?php

/**
 * Class ilMMItemTranslationStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationStorage extends CachedActiveRecord {

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @con_sequence   true
	 */
	protected $id;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     64
	 */
	protected $identification = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     8
	 */
	protected $language_key = '';
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $translation = '';
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_mm_translation";


	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId(int $id) {
		$this->id = $id;
	}


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
	 * @return string
	 */
	public function getLanguageKey(): string {
		return $this->language_key;
	}


	/**
	 * @param string $language_key
	 */
	public function setLanguageKey(string $language_key) {
		$this->language_key = $language_key;
	}


	/**
	 * @return string
	 */
	public function getTranslation(): string {
		return $this->translation;
	}


	/**
	 * @param string $translation
	 */
	public function setTranslation(string $translation) {
		$this->translation = $translation;
	}


	/**
	 * @inheritDoc
	 */
	public function getCache(): ilGlobalCache {
		global $DIC;

		return $DIC->globalScreen()->storage()->cache();
	}
}
