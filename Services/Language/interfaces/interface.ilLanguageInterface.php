<?php

/**
 * language handling
 *
 * this class offers the language handling for an application.
 * it works initially on one file: languages.txt
 * from this file the class can generate many single language files.
 * the constructor is called with a small language abbreviation
 * e.g. $lng = new Language("en");
 * the constructor reads the single-languagefile en.lang and puts this into an array.
 * with
 * e.g. $lng->txt("user_updated");
 * you can translate a lang-topic into the actual language
 *
 * @author  Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 *
 *
 * @todo    Das Datefeld wird bei Aenderungen einer Sprache (update, install, deinstall) nicht richtig gesetzt!!!
 *  Die Formatfunktionen gehoeren nicht in class.Language. Die sind auch woanders einsetzbar!!!
 *  Daher->besser in class.Format
 */
interface ilLanguageInterface
{
    /**
     * Return lang key
     */
    public function getLangKey(): string;
    
    /**
     * Return default language
     */
    public function getDefaultLanguage(): string;
    
    /**
     * Return text direction
     */
    public function getTextDirection(): string;
    
    /**
     * Return content language
     */
    public function getContentLanguage(): string;
    
    /**
     * gets the text for a given topic in a given language
     * if the topic is not in the list, the topic itself with "-" will be returned
     */
    public function txtlng(string $a_module, string $a_topic, string $a_language): string;
    
    /**
     * gets the text for a given topic
     * if the topic is not in the list, the topic itself with "-" will be returned
     */
    public function txt(string $a_topic, string $a_default_lang_fallback_mod = ""): string;
    
    /**
     * Check if language entry exists
     */
    public function exists(string $a_topic): bool;
    
    /**
     * Load language module
     */
    public function loadLanguageModule(string $a_module);
    
    /**
     * Get installed languages
     */
    public function getInstalledLanguages(): array;
    
    /**
     * Return used topics
     */
    public function getUsedTopics(): array;
    
    /**
     * Return used modules
     */
    public function getUsedModules(): array;
    
    /**
     * Return language of user
     */
    public function getUserLanguage(): string;
    
    public function getCustomLangPath(): string;
    
    /**
     * Transfer text to Javascript
     *
     * @param string|string[] $a_lang_key
     * $a_lang_key language key string or array of language keys
     */
    public function toJS($a_lang_key, ilGlobalTemplateInterface $a_tpl = null): void;
    
    /**
     * Transfer text to Javascript
     *
     * $a_map array of key value pairs (key is text string, value is content)
     */
    public function toJSMap(array $a_map, ilGlobalTemplateInterface $a_tpl = null): void;
}
