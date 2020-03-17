<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilNICKeyRegisteredObjective extends ilSetupObjective
{
    const MAX_REDIRECTS = 5;
    const SOCKET_TIMEOUT = 5;
    const ILIAS_NIC_SERVER = "https://nic.ilias.de/index.php";

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The NIC key is registered at the ILIAS Open Source society";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $http_config = $environment->getConfigFor("http");
        return [
            new \ilNICKeyStoredObjective($this->config),
            new \ilSettingsFactoryExistsObjective(),
            new \ilHttpConfigStoredObjective($http_config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");
        $systemfolder_config = $environment->getConfigFor("systemfolder");
        $http_config = $environment->getConfigFor("http");

        if (!\ilCurlConnection::_isCurlExtensionLoaded()) {
            throw new Setup\UnachievableException(
                "CURL extension is required to register NIC."
            );
        }

        //ATTENTION: This makes ilProxySettings work. It uses global ilSetting...
        $old_settings = $GLOBALS["ilSetting"] ?? null;
        $GLOBALS["ilSetting"] = $settings;

        $url = $this->getURLStringForNIC($settings, $systemfolder_config, $http_config);
        $req = $this->getCurlConnection($url);
        $response = $req->exec();
        $req->parseResponse($response);

        if ($req->getInfo()["http_code"] != "200") {
            $settings->set("nic_enabled", "-1");
            throw new Setup\UnachievableException(
                "Could not connect to NIC server at \"" . self::ILIAS_NIC_SERVER . "\""
            );
        }

        $status = explode("\n", $req->getResponseBody());
        $settings->set("nic_enabled", "1");
        $settings->set("inst_id", $status[2]);

        $GLOBALS["ilSetting"] = $old_settings;

        return $environment;
    }

    protected function getURLStringForNIC($settings, \ilSystemFolderSetupConfig $systemfolder_config, \ilHttpSetupConfig $http_config) : string
    {
        $inst_id = $settings->get("inst_id", 0);
        $http_path = $http_config->getHttpPath();
        $host_name = parse_url($http_path)["host"];

        $url = self::ILIAS_NIC_SERVER .
                "?cmd=getid" .
                "&inst_id=" . rawurlencode($inst_id) .
                "&hostname=" . rawurlencode($host_name) .
                "&inst_name=" . rawurlencode($systemfolder_config->getClientName()) .
                "&inst_info=" . rawurlencode($systemfolder_config->getClientDescription()) .
                "&http_path=" . rawurlencode($http_path) .
                "&contact_firstname=" . rawurlencode($systemfolder_config->getContactFirstname()) .
                "&contact_lastname=" . rawurlencode($systemfolder_config->getContactLastname()) .
                "&contact_email=" . rawurlencode($systemfolder_config->getContactEMail()) .
                "&nic_key=" . rawurlencode($settings->get("nic_key"));

        return $url;
    }

    protected function getCurlConnection(string $url)
    {
        $req = new \ilCurlConnection($url);
        $req->init();

        $req->setOpt(CURLOPT_HEADER, 1);
        $req->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $req->setOpt(CURLOPT_CONNECTTIMEOUT, self::SOCKET_TIMEOUT);
        $req->setOpt(CURLOPT_MAXREDIRS, self::MAX_REDIRECTS);

        return $req;
    }
}
