<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilGlobalCacheConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilGlobalCacheSettings
     */
    protected $settings;

    public function __construct(
        \ilGlobalCacheSettings $settings
    ) {
        $this->settings = $settings;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store configuration of Services/GlobalCache";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        ilMemcacheServer::flushDB();

        $memcached_nodes = $this->settings->getMemcachedNodes();
        if (count($memcached_nodes) > 0) {
            foreach ($memcached_nodes as $node) {
                $node->create();
            }
        }

        $this->settings->writeToIniFile($client_ini);

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
        // The effort to check the whole ini file is too big here.
        return true;
    }
}
