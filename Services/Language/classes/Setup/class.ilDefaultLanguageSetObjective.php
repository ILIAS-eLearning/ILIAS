<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilDefaultLanguageSetObjective extends ilLanguageObjective
{
    /**
     * @inheritDoc
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            self::class . "::en"
        );
    }

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Set default language to en";
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
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);

        $settings = $factory->settingsFor("common");
        $settings->set("language", "en");

        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $client_ini->setVariable("language", "default", "en");

        if (!$client_ini->write()) {
            throw new Setup\UnachievableException("Could not write client.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $settings = $factory->settingsFor("common");

        return
            $settings->get("language") !== "en" ||
            $client_ini->readVariable("language", "default") !== "en";
    }
}
