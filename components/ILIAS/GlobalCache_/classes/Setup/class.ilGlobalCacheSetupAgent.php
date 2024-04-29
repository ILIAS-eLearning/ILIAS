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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveConstructor;
use ILIAS\Cache\Nodes\Node;

class ilGlobalCacheSetupAgent implements Setup\Agent
{
    protected \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        Refinery\Factory $refinery
    ) {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data): \ilGlobalCacheSettingsAdapter {
            $settings = new \ilGlobalCacheSettingsAdapter();
            if (
                $data === null ||
                !isset($data["components"]) ||
                !$data["components"] ||
                !isset($data["service"]) ||
                $data["service"] === "none" ||
                (
                    $data["service"] === "memcached" &&
                    (!isset($data["memcached_nodes"]) || count($data["memcached_nodes"]) === 0)
                )
            ) {
                $settings->setActive(false);
            } else {
                $settings->setActive(true);
                switch ($data["service"]) {
                    case "xcache": // xcache has been removed in ILIAS 8, we switch to static cache then
                    case "static":
                        $settings->setService(\ILIAS\Cache\Config::PHPSTATIC);
                        break;
                    case "memcached":
                        array_walk($data["memcached_nodes"], function (array $node) use ($settings): void {
                            $settings->addMemcachedNode($this->convertNode($node));
                        });
                        $settings->setService(\ILIAS\Cache\Config::MEMCACHED);
                        break;
                    case "apc":
                        $settings->setService(\ILIAS\Cache\Config::APCU);
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            sprintf("Unknown caching service: '%s'", $data["service"])
                        );
                }
                $settings->resetActivatedComponents();
                if ($data["components"] === "all") {
                    $settings->activateAll();
                } else {
                    foreach ($data["components"] as $cmp => $active) {
                        if ($active) {
                            $settings->addActivatedComponent($cmp);
                        }
                    }
                }
            }

            return $settings;
        });
    }

    protected function convertNode(array $node): Node
    {
        return new Node(
            (string) $node["host"],
            (int) $node["port"],
            (int) $node["weight"]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        if (!$config instanceof ilGlobalCacheSettingsAdapter) {
            throw new Setup\UnachievableException('wrong config type, expected ilGlobalCacheSettings');
        }
        return new ilGlobalCacheConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        if ($config instanceof ilGlobalCacheSettingsAdapter) {
            return new ilGlobalCacheConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilGlobalCacheMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        $config = $config ?? new ilGlobalCacheSettingsAdapter();
        return [
            'flushAll' => new ObjectiveConstructor(
                'flushes all GlobalCaches.',
                function () use ($config) {
                    return new ilGlobalCacheAllFlushedObjective($config);
                }
            )
        ];
    }
}
