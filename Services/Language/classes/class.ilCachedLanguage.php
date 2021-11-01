<?php

require_once "./Services/GlobalCache/classes/class.ilGlobalCache.php";

/**
 * Class ilCachedLanguage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilCachedLanguage
{
    protected $global_cache;
    /**
     * @var bool
     */
    protected $loaded = false;
    /**
     * @var string
     */
    protected $language_key = 'en';
    /**
     * @var array
     */
    protected $translations = array();
    /**
     * @var ilCachedLanguage[]
     */
    protected static $instances = array();


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

    public function isActive(): bool
    {
        return $this->global_cache->isActive();
    }


    protected function readFromCache(): void
    {
        if ($this->global_cache->isActive()) {
            $translations = $this->global_cache->get('translations_' . $this->getLanguageKey());
            if (is_array($translations)) {
                $this->setTranslations($translations);
                $this->setLoaded(true);
            }
        }
    }


    public function writeToCache(): void
    {
        if ($this->global_cache->isActive()) {
            $this->global_cache->set('translations_' . $this->getLanguageKey(), $this->getTranslations());
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
			$this->global_cache->delete('translations_' . $this->getLanguageKey());
			$this->setLoaded(false);
		}
	}

	protected function readFromDB(): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = 'SELECT module, lang_array FROM lng_modules WHERE lang_key = %s';
        $res = $ilDB->queryF($q, array( 'text' ), array( $this->getLanguageKey() ));
        $translations = array();
        while ($set = $ilDB->fetchObject($res)) {
            $lang_array = unserialize($set->lang_array);
            if (is_array($lang_array)) {
                $translations[$set->module] = $lang_array;
            }
        }
        $this->setTranslations($translations);
    }


    /**
     * @param $key
     *
     * @return ilCachedLanguage
     */
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

    public function setLanguageKey(string $language_key): void
    {
        $this->language_key = $language_key;
    }

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

    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
