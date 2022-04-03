<?php

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionLanguageAdapter implements ilDataCollectionLanguagePort
{

    protected static ?self $instance = null;

    private \ilLanguage $language;

    private function __construct(
        \ilLanguage $language
    ) {
        $this->language = $language;
        $this->loadLanguageModules();
    }

    public static function new() : self
    {
        if (is_null(static::$instance) === true) {
            global $DIC;
            static::$instance = new self($DIC->language());
        }

        return static::$instance;
    }

    private function loadLanguageModules()
    {
        $this->language->loadLanguageModule("dcl");
        $this->language->loadLanguageModule('content');
        $this->language->loadLanguageModule('obj');
        $this->language->loadLanguageModule('cntr');
    }


    final public function translate(string $languageKey)
    {
        $this->language->txt($languageKey);
    }
}