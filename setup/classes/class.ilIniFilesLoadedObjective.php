<?php

declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilIniFilesLoadedObjective implements Setup\Objective
{
    // ATTENTION: This is an evil hack to make bootstrapping of the system simpler.
    // We have three variariations on loading ini-files: (1) during the setup,
    // (2) after the setup and while collecting status (3). It should be simple
    // for consumers in ILIAS (and plugins) to attempt to load ini files in all
    // circumstances. Hence one objective. Still, the details on how this should
    // be achieved vary. For (1), we want to populate and load the files. For (2)
    // we want to just load the files, but make sure they are populated. For (3)
    // we want to maybe load the files if they exist, but not populate them for
    // sure. This is the switch to change that behaviour, but also maintain the
    // simple interface. There for sure are other ways to achieve this, maybe
    // even better ones, but this hack seems to be okish atm. I suspect it
    // could go away when we work on the system on how inis are build, the clients
    // (abandoned) are implemented and the config is loaded in general, but this
    // is task for another day. If anyone has an idea or wants to work on getting
    // rid of these, feel free to get in contact with Richard.
    public static bool $might_populate_ini_files_as_well = true;

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "The ilias.ini.php and client.ini.php are loaded";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        if (self::$might_populate_ini_files_as_well) {
            return [
                new Setup\Objective\ClientIdReadObjective(),
                new ilIniFilesPopulatedObjective()
            ];
        } else {
            return [
                new Setup\Objective\ClientIdReadObjective(),
            ];
        }
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        if ($client_id === null) {
            throw new Setup\UnachievableException(
                "To initialize the ini-files, we need a client id, but it does not " .
                "exist in the environment."
            );
        }

        if ($environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI) == null) {
            $path = dirname(__DIR__, 2) . "/ilias.ini.php";
            $ini = new ilIniFile($path);
            $ini->read();
            $environment = $environment
                ->withResource(Setup\Environment::RESOURCE_ILIAS_INI, $ini);
        }

        if ($environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI) == null) {
            $path = $this->getClientDir($client_id) . "/client.ini.php";
            $client_ini = new ilIniFile($path);
            $client_ini->read();
            $environment = $environment
                ->withResource(Setup\Environment::RESOURCE_CLIENT_INI, $client_ini);
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        return is_null($ini) || is_null($client_ini);
    }

    protected function getClientDir($client_id): string
    {
        return dirname(__DIR__, 2) . "/data/$client_id";
    }
}
