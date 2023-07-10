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
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $repo = new ilMathJaxConfigSettingsRepository($factory);
        if (!empty($new_config = $this->checkClientScriptUrl($repo->getConfig(), $interaction))) {
            $repo->updateConfig($new_config);
        }

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    /**
     * Check if an outdated script URL is used and try to correct it
     * - Correct automatically if MathJax in the browser is not enabled (change an old default setting)
     * - Ask if MathJax in the browser is enabled
     * - Warn if an outdated URL is used in the config.json
     *
     * Return a config object which has to be saved, if changes are made
     */
    protected function checkClientScriptUrl(ilMathJaxConfig $config, Setup\AdminInteraction $interaction): ?ilMathJaxConfig
    {
        $change = false;
        $recommended = 'https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe';
        $outdated = [
            'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML',
            'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe',
            'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML',
            'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe',
            'https://cdn.jsdelivr.net/npm/mathjax@2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML',
            'https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML'
        ];

        if (in_array($config->getClientScriptUrl(), $outdated)) {
            if ($config->isClientEnabled()) {
                $change = $interaction->confirmOrDeny("Replace outdated or unsave MathJax URL with $recommended ?");
            } else {
                $interaction->inform("Replaced inactive outdated MathJax URL with $recommended");
                $change = true;
            }
        }
        if ($change
            && $this->setup_config !== null
            && in_array($this->setup_config->getConfig()->getClientScriptUrl(), $outdated)
        ) {
            $interaction->inform("Please change the URL in the setup.json to avoid this message in the next update.");
        }

        if ($change) {
            return $config->withClientScriptUrl($recommended);
        }
        return null;
    }
}
