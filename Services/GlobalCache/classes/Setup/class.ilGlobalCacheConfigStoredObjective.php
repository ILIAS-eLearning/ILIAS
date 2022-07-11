<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
use ILIAS\Setup;

class ilGlobalCacheConfigStoredObjective implements Setup\Objective
{
    protected \ilGlobalCacheSettings $settings;

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
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        /** @var $db ilDBInterface */
        $db->manipulate("TRUNCATE TABLE il_gc_memcache_server");

        $memcached_nodes = $this->settings->getMemcachedNodes();
        foreach ($memcached_nodes as $node) {
            $node->create();
        }

        $return = $this->settings->writeToIniFile($client_ini);

        if (!$client_ini->write() || !$return) {
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
