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

/**
 * Repository for storing and loading the MathJax configuration
 */
class ilMathJaxConfigSettingsRepository implements ilMathJaxConfigRespository
{
    protected ilSetting $settings;

    /**
     * Constructor
     * @param ilSetting $settings - must be settings with loaded module 'MathJax'
     */
    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get the MathJax Configuration
     */
    public function getConfig(): ilMathJaxConfig
    {
        return new ilMathJaxConfig(
            (bool) $this->settings->get('enable'),
        );
    }

    /**
     * Update the MathNax Configuration
     */
    public function updateConfig(ilMathJaxConfig $config): void
    {
        $this->settings->set('enable', (string) $config->isClientEnabled());
    }
}
