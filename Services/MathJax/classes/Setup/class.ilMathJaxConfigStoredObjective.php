<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Setup;

class ilMathJaxConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilMathJaxSetupConfig
     */
    protected $config;

    public function __construct(
        \ilMathJaxSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store configuration of Services/MathJax";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $repo = new ilMathJaxConfigSettingsRepository($factory);
        $repo->updateConfig($this->config->applyTo($repo->getConfig()));

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $repo = new ilMathJaxConfigSettingsRepository($factory);

        return $this->config->isApplicableTo($repo->getConfig());

    }
}
