<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLanguageSetupConfig implements Setup\Config {
	/**
	 * @var string
	 */
	protected $default_language;

	/**
	 * @var	string[]
	 */
	protected $install_languages;

	/**
	 * @var	string[]
	 */
	protected $install_local_languages;

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
				"Local languages ".implode(", ", $diff)." are not in the languages to be installed."
			);
		}
		$this->default_language = $default_language;
		$this->install_languages = array_values($install_languages);
		$this->install_local_languages = array_values($install_local_languages);
	}

	protected function checkLanguageName(string $l) : void {
		if (!strlen($l) == 2) {
			throw new \InvalidArgumentException(
				"'$l' is not a valid language id."
			);
		}
	}

	public function getDefaultLanguage() : string {
		return $this->default_language;
	}

	/**
	 * @return	string[]
	 */
	public function getInstallLanguages() : array {
		return $this->install_languages;
	}

	/**
	 * @return	string[]
	 */
	public function getInstallLocalLanguages() : array {
		return $this->install_local_languages;
	}
}
