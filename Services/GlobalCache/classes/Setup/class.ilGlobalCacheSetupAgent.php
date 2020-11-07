<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;

class ilGlobalCacheSetupAgent implements Setup\Agent
{
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
            if ($data === null || !$data["components"] || $data["service"] == "none") {
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
                if ($data["components"] == "all") {
                    $settings->activateAll();
                } else {
                    foreach ($data["components"] as $cmp) {
                        $settings->addActivatedComponents($cmp);
                    }
                }
            }
            return $settings;
        });
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
