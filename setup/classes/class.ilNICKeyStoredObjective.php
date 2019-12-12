<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilNICKeyStoredObjective extends ilSetupObjective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "A NIC key for the installation is generated and stored";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");

        if ($settings->get("nic_key")) {
            return $environment;
        }

        $nic_key = $this->generateNICKey();
        $settings->set("nic_key", $nic_key);

        return $environment;
    }

    protected function generateNICKey()
    {
        return md5(uniqid($this->getClientId(), true));
    }
}
