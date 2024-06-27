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

class ilMathJaxSetupConfig implements Setup\Config
{
    // raw setup data
    protected array $data = [];

    // translated config
    protected ilMathJaxConfig $config;

    /**
     * Create the config fron a data array
     */
    public function __construct(array $data)
    {
        $this->data = $data;

        $this->config = new \ilMathJaxConfig(
            (bool) ($this->data['client_enabled'] ?? false)
        );
    }

    /**
     * Get the MathJaxConfig object which is created from the data in config.json
     * @return ilMathJaxConfig
     */
    public function getConfig(): ilMathJaxConfig
    {
        return $this->config;
    }

    /**
     * Get a data array from a config
     */
    public function getDataFromConfig(ilMathJaxConfig $config): array
    {
        return [
            'client_enabled' => $config->isClientEnabled()
        ];
    }

    /**
     * Check if the setup config can be applied to an existing stored config
     * Only the values that are actually defined in the config.json will be applied
     * The setup config is applicable if at least one setting in config.json is defined and differs fron the stored config
     */
    public function isApplicableTo(ilMathJaxConfig $config): bool
    {
        return isset($this->data['client_enabled']) && $this->config->isClientEnabled() !== $config->isClientEnabled();
    }

    /**
     * Apply the setup config to an existing stored config
     * Only the values that are actually defined in the config.json will be applied
     */
    public function applyTo(ilMathJaxConfig $config): ilMathJaxConfig
    {
        if (isset($this->data['client_enabled'])) {
            $config = $config->withClientEnabled($this->config->isClientEnabled());
        }
        return $config;
    }
}
