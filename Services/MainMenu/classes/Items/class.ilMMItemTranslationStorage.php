<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ilMMItemTranslationStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationStorage extends CachedActiveRecord {

	/**
	 * @param IdentificationInterface $identification
	 * @param string                  $language_key
	 * @param string                  $translation
	 *
	 * @return ilMMItemTranslationStorage
	 */
	public static function storeTranslation(IdentificationInterface $identification, string $language_key, string $translation): self {
		if ($translation === "-") {
			return new self();
		}
		$language_identification = "{$identification->serialize()}|$language_key";
		$mt = ilMMItemTranslationStorage::find($language_identification);
		if (!$mt instanceof ilMMItemTranslationStorage) {
			$mt = new ilMMItemTranslationStorage();
			$mt->setId($language_identification);
			$mt->setIdentification($identification->serialize());
			$mt->create();
		}

		$mt->setTranslation($translation);
		$mt->setLanguageKey($language_key);
		$mt->update();

		return $mt;
	}


	/**
	 * @param IdentificationInterface $identification
	 * @param string                  $translation
	 *
	 * @return ilMMItemTranslationStorage
	 */
	public static function storeDefaultTranslation(IdentificationInterface $identification, string $translation): self {
		global $DIC;

		$language_key = $DIC->language()->getDefaultLanguage() ? $DIC->language()->getDefaultLanguage() : "en";

		return self::storeTranslation($identification, $language_key, $translation);
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
	protected $id;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $identification;
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
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     8
	 */
	protected $language_key = '';
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_mm_translation";


	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}


	/**
	 * @param string $id
	 */
	public function setId(string $id) {
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
	 * @inheritDoc
	 */
	public function getCache(): ilGlobalCache {
		global $DIC;

		return $DIC->globalScreen()->storage()->cache();
	}
}
