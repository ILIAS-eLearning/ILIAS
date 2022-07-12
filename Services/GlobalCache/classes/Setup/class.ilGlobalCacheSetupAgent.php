<?php declare(strict_types=1);

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
use ILIAS\Refinery;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveConstructor;

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
    public function hasConfig() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) : \ilGlobalCacheSettings {
            $settings = new \ilGlobalCacheSettings();
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
                        $settings->setService(\ilGlobalCache::TYPE_STATIC);
                        break;
                    case "memcached":
                        array_walk($data["memcached_nodes"], function (array $node) use ($settings) : void {
                            $settings->addMemcachedNode($this->getMemcachedServer($node));
                        });
                        $settings->setService(\ilGlobalCache::TYPE_MEMCACHED);
                        break;
                    case "apc":
                        $settings->setService(\ilGlobalCache::TYPE_APC);
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

    protected function getMemcachedServer(array $node) : ilMemcacheServer
    {
        $m = new ilMemcacheServer();
        $m->setStatus($node["active"] === "1" ? ilMemcacheServer::STATUS_ACTIVE : ilMemcacheServer::STATUS_INACTIVE);
        $m->setHost($node["host"]);
        $m->setPort((int) $node["port"]);
        $m->setWeight((int) $node["weight"]);

        return $m;
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        if (!$config instanceof ilGlobalCacheSettings) {
            throw new Setup\UnachievableException('wrong config type, expected ilGlobalCacheSettings');
        }
        return new ilGlobalCacheConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config instanceof ilGlobalCacheSettings) {
            return new ilGlobalCacheConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilGlobalCacheMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
    
    public function getNamedObjectives(?Config $config = null) : array
    {
        return [
            'flushAll' => new ObjectiveConstructor(
                'flushes all GlobalCaches.',
                function () {
                    return new ilGlobalCacheAllFlushedObjective();
                }
            )
        ];
    }
}
