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

use ILIAS\Cache\Container\Request;
use ILIAS\Refinery\Custom\Transformation;

/**
 * Class ilCachedLanguage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilCachedLanguage implements Request
{
    protected \ILIAS\Cache\Container\Container $language_cache;
    protected bool $loaded = false;
    protected string $language_key = "en";
    protected array $translations = array();
    protected static array $instances = array();

    /**
     * ilCachedLanguage constructor.
     */
    protected function __construct(string $language_key)
    {
        global $DIC;
        $this->setLanguageKey($language_key);
        $this->language_cache = $DIC->globalCache()->get($this);
        $this->readFromCache();
        if (!$this->getLoaded()) {
            $this->readFromDB();
            $this->writeToCache();
            $this->setLoaded(true);
        }
    }

    public function getContainerKey(): string
    {
        return 'clng';
    }


    public function isForced(): bool
    {
        return true;
    }

    /**
     * Return whether the global cache is active
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * Read from cache
     */
    protected function readFromCache(): void
    {
        $key = "translations_" . $this->getLanguageKey();
        if ($this->language_cache->has($key)) {
            // This is a workaround for the fact that transformatuin cannot be created by
            // $DIC->refinery()->xy() since we are in a hell of dependencies. E.g. we cant instantiate the
            // caching service with $DIC->refinery() since the Refinery needs ilLanguage, but ilLanguage
            // needs the caching service and so on...
            $always = new Transformation(
                function ($v) {
                    return is_array($v) ? $v : null;
                }
            );

            $translations = $this->language_cache->get($key, $always);
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
        $this->language_cache->set("translations_" . $this->getLanguageKey(), $this->getTranslations());
    }

    /**
     * Delete the cache entry for this language without flushing the whole global cache
     * Using this function avoids a flush loop when languages are updated
     * A missing entry will cause the next request to refill the cache in the constructor of this class
     * @see mantis #28818
     */
    public function deleteInCache(): void
    {
        $this->language_cache->delete("translations_" . $this->getLanguageKey());
        $this->setLoaded(false);
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
        $this->language_cache->flush();
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
