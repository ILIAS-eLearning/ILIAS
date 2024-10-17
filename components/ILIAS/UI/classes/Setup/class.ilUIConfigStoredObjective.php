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

class ilUIConfigStoredObjective implements Setup\Objective
{
    public function __construct(
        private readonly ilUISetupConfig $config
    ) {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Store configuration of component UI";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        /** @var ilSettingsFactory $factory */
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        /** @var ilSetting $settings */
        $settings = $factory->settingsFor('UI');
        $settings->set('mathjax_enabled', $this->config->isMathJaxEnabled() ? '1' : 0);
        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        /** @var ilSettingsFactory $factory */
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        /** @var ilSetting $settings */
        $settings = $factory->settingsFor('UI');
        return $this->config->isMathJaxEnabled() !== (bool) $settings->get('mathjax_enabled');
    }
}
