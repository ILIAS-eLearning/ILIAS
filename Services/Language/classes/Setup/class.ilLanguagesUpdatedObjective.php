<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLanguagesUpdatedObjective implements Setup\Objective
{
    protected \ilSetupLanguage $il_setup_language;

    public function __construct(
        \ilSetupLanguage $il_setup_language
    ) {
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
     * Return installed languages as string
     */
    protected function getInstalledLanguagesAsString() : string
    {
        return implode(", ", $this->il_setup_language->getInstalledLanguages());
    }

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Update languages " . $this->getInstalledLanguagesAsString();
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
        return [];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        // TODO: Remove this once ilSetupLanguage (or a successor) supports proper
        //// DI for all methods.
        $db_tmp = $GLOBALS["ilDB"];
        $GLOBALS["ilDB"] = $db;

        $this->il_setup_language->setDbHandler($db);
        $this->il_setup_language->installLanguages(
            $this->il_setup_language->getInstalledLanguages(),
            $this->il_setup_language->getLocalLanguages()
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