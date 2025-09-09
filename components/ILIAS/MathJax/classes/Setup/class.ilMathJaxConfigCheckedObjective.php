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

class ilMathJaxConfigCheckedObjective implements Setup\Objective
{
    protected ?\ilMathJaxSetupConfig $setup_config = null;

    public function __construct(?\ilMathJaxSetupConfig $setup_config = null)
    {
        $this->setup_config = $setup_config;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Check configuration of Services/MathJax";
    }

    public function isNotable(): bool
    {
        return false;
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
        $interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $repo = new ilMathJaxConfigSettingsRepository($factory->settingsFor('MathJax'));
        $this->checkClientScriptUrl($repo->getConfig(), $interaction);

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    /**
     * Check if an outdated script URL is used
     */
    protected function checkClientScriptUrl(ilMathJaxConfig $config, Setup\AdminInteraction $interaction): void
    {
        $recommended = 'https://YOUR_ILIAS_URL/assets/js/cdn-mathjax2-tex-mml-chtml-safe.js';

        if (str_contains($config->getClientScriptUrl(), '?')) {
            $interaction->inform("ILIAS 10 cuts query params of javascript URLs that are added to the page."
                . " Please replace your MathJax URL with $recommended or a similar script that sets the save mode of MathJax!");

            if ($this->setup_config !== null && str_contains($this->setup_config->getConfig()->getClientScriptUrl(), '?')) {
                $interaction->inform("Change the URL in the setup.json to avoid this message in the next update.");
            }
        }
    }
}
