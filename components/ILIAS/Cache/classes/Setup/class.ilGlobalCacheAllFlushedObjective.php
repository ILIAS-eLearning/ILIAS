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

declare(strict_types=1);
use ILIAS\Setup\Environment;
use ILIAS\Cache\Services;

class ilGlobalCacheAllFlushedObjective extends ilSetupObjective
{
    public function __construct(private \ilGlobalCacheSettingsAdapter $cache_settings_adapter)
    {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "All global caches flushed";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $client_ini = $environment->getResource(Environment::RESOURCE_CLIENT_INI);
        if ($client_ini === null) {
            throw new UnexpectedValueException("Client ini not found");
        }
        $this->cache_settings_adapter->readFromIniFile($client_ini);
        $services = new Services($this->cache_settings_adapter->getConfig());
        $services->flushAdapter();

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
