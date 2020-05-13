<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

/**
 * There seems to already exist an ILIAS installation, an interaction with it
 * should be confirmed.
 */
class ilOverwritesExistingInstallationConfirmed extends ilSetupObjective
{
    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "Confirm that an existing installation should be overwritten if applicable.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        if (!$this->iniExists() && !$this->clientIniExists()) {
            return $environment;
        }

        $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $message =
            "An installation already seems to exist in this location. Using this command\n" .
            "might change your installation in unexpected ways. Are you sure that you\n" .
            "want to proceed?";

        if (!$admin_interaction->confirmOrDeny($message)) {
            throw new Setup\NoConfirmationException($message);
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }

    public function iniExists()
    {
        return file_exists(dirname(__DIR__, 2) . "/ilias.ini.php");
    }

    public function clientIniExists()
    {
        return file_exists($this->getClientDir() . "/client.ini.php");
    }

    protected function getClientDir() : string
    {
        return dirname(__DIR__, 2) . "/data/" . $this->config->getClientId();
    }
}
