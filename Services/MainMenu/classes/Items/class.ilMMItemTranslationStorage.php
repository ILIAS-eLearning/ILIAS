<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ilMMItemTranslationStorage
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationStorage extends CachedActiveRecord
{
    /**
     * @param IdentificationInterface $identification
     * @param string                  $language_key
     * @param string                  $translation
     * @return ilMMItemTranslationStorage
     */
    public static function storeTranslation(IdentificationInterface $identification, string $language_key, string $translation): self
    {
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
     * @return ilMMItemTranslationStorage
     */
    public static function storeDefaultTranslation(IdentificationInterface $identification, string $translation): self
    {
        return self::storeTranslation($identification, self::getDefaultLanguage(), $translation);
    }

    /**
     * @param IdentificationInterface $identification
     * @return string
     */
    public static function getDefaultTranslation(IdentificationInterface $identification): string
    {
        if (!self::hasDefaultTranslation($identification)) {
            return "";
        }
        $lng = self::getDefaultLanguage();
        $key = "{$identification->serialize()}|$lng";
        /**
         * @var $item self
         */
        if ($item = self::find($key)) {
            return $item->getTranslation();
        }

        return "";
    }

    /**
     * @param IdentificationInterface $identification
     * @return bool
     */
    public static function hasDefaultTranslation(IdentificationInterface $identification): bool
    {
        $lng = self::getDefaultLanguage();
        $key = "{$identification->serialize()}|$lng";

        return self::find($key) instanceof self;
    }

    /**
     * @return string
     */
    public static function getDefaultLanguage(): string
    {
        static $default_language;
        global $DIC;
        if (!isset($default_language)) {
            $default_language = $DIC->language()->getDefaultLanguage() ? $DIC->language()->getDefaultLanguage() : "en";
        }

        return $default_language;
    }

    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     64
     */
    protected ?string $id;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected string $identification = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected string $translation = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     8
     */
    protected string $language_key = '';
    /**
     * @var string
     */
    protected string $connector_container_name = "il_mm_translation";

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getIdentification(): string
    {
        return $this->identification;
    }

    /**
     * @param string $identification
     */
    public function setIdentification(string $identification): void
    {
        $this->identification = $identification;
    }

    /**
     * @return string
     */
    public function getTranslation(): string
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     */
    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }

    /**
     * @return string
     */
    public function getLanguageKey(): string
    {
        return $this->language_key;
    }

    /**
     * @param string $language_key
     */
    public function setLanguageKey(string $language_key): void
    {
        $this->language_key = $language_key;
    }

    /**
     * @inheritDoc
     */
    public function getCache(): ilGlobalCache
    {
        return ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }
}
