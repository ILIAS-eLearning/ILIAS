<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;

class ilGlobalCacheSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @var Refinery\Factory
     */
    protected $refinery;

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
        return $this->refinery->custom()->transformation(function ($data) {
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
                    case "static":
                        $settings->setService(\ilGlobalCache::TYPE_STATIC);
                        break;
                    case "xcache":
                        $settings->setService(\ilGlobalCache::TYPE_XCACHE);
                        break;
                    case "memcached":
                        array_walk($data["memcached_nodes"], function (array $node) use ($settings) {
                            $settings->addMemcachedNode($this->getMemcachedServer($node));
                        });
                        $settings->setService(\ilGlobalCache::TYPE_MEMCACHED);
                        break;
                    case "apc":
                        $settings->setService(\ilGlobalCache::TYPE_APC);
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            "Unknown caching service: '{$data["service"]}'"
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
        $m->setPort($node["port"]);
        $m->setWeight($node["weight"]);

        return $m;
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilGlobalCacheConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
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
}
