<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLanguageSetupConfig implements Setup\Config
{
    protected string $default_language;
    protected array $install_languages;
    protected array $install_local_languages;

    public function __construct(
        string $default_language,
        array $install_languages,
        array $install_local_languages
    ) {
        $this->checkLanguageName($default_language);
        foreach ($install_languages as $l) {
            $this->checkLanguageName($l);
        }
        foreach ($install_local_languages as $l) {
            $this->checkLanguageName($l);
        }
        if (!in_array($default_language, $install_languages)) {
            throw new \InvalidArgumentException(
                "Default language '$default_language' is not in the languages to be installed."
            );
        }
        $diff = array_diff($install_local_languages, $install_languages);
        if (count($diff) > 0) {
            throw new \InvalidArgumentException(
                "Local languages " . implode(", ", $diff) . " are not in the languages to be installed."
            );
        }
        $this->default_language = $default_language;
        $this->install_languages = array_values($install_languages);
        $this->install_local_languages = array_values($install_local_languages);
    }

    /**
     * Check the language name
     */
    protected function checkLanguageName(string $l) : void
    {
        if (strlen($l) !== 2) {
            throw new \InvalidArgumentException(
                "'$l' is not a valid language id."
            );
        }
    }

    /**
     * Return default language
     */
    public function getDefaultLanguage() : string
    {
        return $this->default_language;
    }

    /**
     * Return installed languages
     */
    public function getInstallLanguages() : array
    {
        return $this->install_languages;
    }

    /**
     * Return installed local languages
     */
    public function getInstallLocalLanguages() : array
    {
        return $this->install_local_languages;
    }
}
