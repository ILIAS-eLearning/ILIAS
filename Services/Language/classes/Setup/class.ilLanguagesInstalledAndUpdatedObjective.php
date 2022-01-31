<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLanguagesInstalledAndUpdatedObjective extends ilLanguageObjective
{
    protected \ilSetupLanguage $il_setup_language;

    public function __construct(
        ?\ilLanguageSetupConfig $config,
        \ilSetupLanguage $il_setup_language
    ) {
        parent::__construct($config);
        $this->il_setup_language = $il_setup_language;
    }

    /**
     * @inheritDoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * Return installed languages
     */
    protected function getInstallLanguages() : array
    {
        if (!is_null($this->config)) {
            return $this->config->getInstallLanguages();
        }
        return $this->il_setup_language->getInstalledLanguages();
    }

    /**
     * Return installed local languages
     */
    protected function getInstallLocalLanguages() : array
    {
        if (!is_null($this->config)) {
            return $this->config->getInstallLocalLanguages();
        }
        return $this->il_setup_language->getInstalledLocalLanguages();
    }

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Install/Update languages " . implode(", ", $this->getInstallLanguages());
    }

    /**
     * @inheritDoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        if (is_null($this->config)) {
            return [];
        }

        $db_config = $environment->getConfigFor("database");
        return [
            new ilDatabasePopulatedObjective($db_config)
        ];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        // TODO: Remove this once ilSetupLanguage (or a successor) supports proper
        // DI for all methods.
        $db_tmp = $GLOBALS["ilDB"];
        $GLOBALS["ilDB"] = $db;

        $this->il_setup_language->setDbHandler($db);
        $this->il_setup_language->installLanguages(
            $this->getInstallLanguages(),
            $this->getInstallLocalLanguages()
        );

        $GLOBALS["ilDB"] = $db_tmp;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
