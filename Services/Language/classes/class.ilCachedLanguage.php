<?php

declare(strict_types=1);

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
 ********************************************************************
 */

require_once "./Services/GlobalCache/classes/class.ilGlobalCache.php";

/**
 * Class ilCachedLanguage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilCachedLanguage
{
    protected \ilGlobalCache $global_cache;
    protected bool $loaded = false;
    protected string $language_key = "en";
    protected array $translations = array();
    protected static array $instances = array();

    /**
     * ilCachedLanguage constructor.
     */
    protected function __construct(string $language_key)
    {
        $this->setLanguageKey($language_key);
        $this->global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_CLNG);
        $this->readFromCache();
        if (!$this->getLoaded()) {
            $this->readFromDB();
            $this->writeToCache();
            $this->setLoaded(true);
        }
    }

    /**
     * Return whether the global cache is active
     */
    public function isActive(): bool
    {
        return $this->global_cache->isActive();
    }

    /**
     * Read from cache
     */
    protected function readFromCache(): void
    {
        if ($this->global_cache->isActive()) {
            $translations = $this->global_cache->get("translations_" . $this->getLanguageKey());
            if (is_array($translations)) {
                $this->setTranslations($translations);
                $this->setLoaded(true);
            }
        }
    }

    /**
     * Write to global cache
     */
    public function writeToCache(): void
    {
        if ($this->global_cache->isActive()) {
            $this->global_cache->set("translations_" . $this->getLanguageKey(), $this->getTranslations());
        }
    }

    /**
     * Delete the cache entry for this language without flushing the whole global cache
     * Using this function avoids a flush loop when languages are updated
     * A missing entry will cause the next request to refill the cache in the constructor of this class
     * @see mantis #28818
     */
    public function deleteInCache(): void
    {
        if ($this->global_cache->isActive()) {
            $this->global_cache->delete("translations_" . $this->getLanguageKey());
            $this->setLoaded(false);
        }
    }

    /**
    * Read data from table lng_module from DB
    */
    protected function readFromDB(): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = 'SELECT module, lang_array FROM lng_modules WHERE lang_key = %s';
        $res = $ilDB->queryF($q, array( "text" ), array( $this->getLanguageKey() ));
        $translations = array();
        while ($set = $ilDB->fetchObject($res)) {
            try {
                $lang_array = unserialize($set->lang_array, ['allowed_classes' => false]);
            } catch (Throwable $t) {
                continue;
            }
            if (is_array($lang_array)) {
                $translations[$set->module] = $lang_array;
            }
        }
        $this->setTranslations($translations);
    }

    public static function getInstance($key): self
    {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($key);
        }

        return self::$instances[$key];
    }

    public function flush(): void
    {
        if ($this->global_cache->isActive()) {
            $this->global_cache->flush();
        }
        $this->readFromDB();
        $this->writeToCache();
    }

    /**
     * Set language key
     */
    public function setLanguageKey(string $language_key): void
    {
        $this->language_key = $language_key;
    }

    /**
     * Return language key
     */
    public function getLanguageKey(): string
    {
        return $this->language_key;
    }

    public function setLoaded(bool $loaded): void
    {
        $this->loaded = $loaded;
    }

    public function getLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Set translations
     */
    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * Return translations as array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
