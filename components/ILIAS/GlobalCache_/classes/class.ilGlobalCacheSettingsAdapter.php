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
use ILIAS\Cache\Config;
use ILIAS\Cache\Nodes\Node;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilGlobalCacheSettingsAdapter implements Setup\Config
{
    /**
     * @readonly
     */
    private ?Config $config = null;
    private bool $active = true;
    private string $service = Config::PHPSTATIC;
    private array $components = [];
    /**
     * @var Node[]
     */
    private array $nodes = [];

    public function __construct(
        private readonly ?ilIniFile $client_ini = null,
        private readonly ?ilDBInterface $db = null
    ) {
        if ($this->client_ini !== null) {
            $this->readFromIniFile($this->client_ini);
        }
        $this->config = $this->toConfig();
    }

    public function getConfig(): Config
    {
        return $this->config ?? $this->toConfig();
    }

    public function toConfig(): Config
    {
        return new Config(
            $this->service,
            $this->active,
            $this->components,
            $this->getNodesRepository()
        );
    }

    public function readFromIniFile(ilIniFile $client_ini): bool
    {
        $this->active = (bool) $client_ini->readVariable('cache', 'activate_global_cache');
        $service_type = $client_ini->readVariable('cache', 'global_cache_service_type');
        if(is_numeric($service_type)) {
            $service_type = match ((int) $service_type) {
                0 => Config::PHPSTATIC,
                2 => Config::MEMCACHED,
                3 => Config::APCU,
                default => Config::PHPSTATIC,
            };
        }
        $this->service = (string) $service_type;
        $read_group = $client_ini->readGroup('cache_activated_components');

        $this->components = array_unique(
            array_map(static function (string $component): string {
                if ($component === 'all') {
                    return '*';
                }
                return $component;
            }, array_keys($read_group))
        );
        $this->nodes = [];
        if ($this->db !== null) {
            $repo = $this->getNodesRepository();
            foreach ($repo->getNodes() as $node) {
                $this->addMemcachedNode($node);
            }
        }

        return true;
    }

    public function getNodesRepository(): ilMemcacheNodesRepository
    {
        return new ilMemcacheNodesRepository($this->db);
    }

    public function storeToIniFile(ilIniFile $client_ini): bool
    {
        $client_ini->setVariable('cache', 'activate_global_cache', $this->active ? '1' : '0');
        $client_ini->setVariable('cache', 'global_cache_service_type', $this->service);
        $client_ini->removeGroup('cache_activated_components');
        $client_ini->addGroup('cache_activated_components');
        foreach ($this->components as $component) {
            $client_ini->setVariable('cache_activated_components', $component, '1');
        }

        // store nodes to db
        if ($this->db !== null) {
            $repo = $this->getNodesRepository();
            $repo->deleteAll();
            foreach ($this->nodes as $node) {
                $repo->store($node);
            }
        }

        return $client_ini->write();
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function setService(string $service): void
    {
        $this->service = $service;
    }

    public function addMemcachedNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function getMemcachedNodes(): array
    {
        return $this->nodes;
    }

    public function resetActivatedComponents(): void
    {
        $this->components = [];
    }

    public function activateAll(): void
    {
        $this->components = ['*'];
    }

    public function addActivatedComponent(string $component): void
    {
        $this->components[] = $component;
    }
}
