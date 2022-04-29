<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Repository for storing and loading the MathJax configuration
 */
class ilMathJaxConfigSettingsRepository implements ilMathJaxConfigRespository
{
    protected ilSetting $settings;

    /**
     * Constructor
     */
    public function __construct(ilSettingsFactory $factory)
    {
        $this->settings = $factory->settingsFor('MathJax');
    }

    /**
     * Get the MathJax Configuration
     */
    public function getConfig() : ilMathJaxConfig
    {
        return new ilMathJaxConfig(
            (bool) $this->settings->get('enable'),
            (string) $this->settings->get('path_to_polyfill'),
            (string) $this->settings->get('path_to_mathjax'),
            (int) $this->settings->get('limiter'),
            (bool) $this->settings->get('enable_server'),
            (string) $this->settings->get('server_address'),
            (int) $this->settings->get('server_timeout'),
            (bool) $this->settings->get('server_for_browser'),
            (bool) $this->settings->get('server_for_export'),
            (bool) $this->settings->get('server_for_pdf')
        );
    }

    /**
     * Update the MathNax Configuration
     */
    public function updateConfig(ilMathJaxConfig $config) : void
    {
        $this->settings->set('enable', (string) $config->isClientEnabled());
        $this->settings->set('path_to_polyfill', $config->getClintPolyfillUrl());
        $this->settings->set('path_to_mathjax', $config->getClientScriptUrl());
        $this->settings->set('limiter', (string) $config->getClientLimiter());
        $this->settings->set('enable_server', (string) $config->isServerEnabled());
        $this->settings->set('server_address', $config->getServerAddress());
        $this->settings->set('server_timeout', (string) $config->getServerTimeout());
        $this->settings->set('server_for_browser', (string) $config->isServerForBrowser());
        $this->settings->set('server_for_export', (string) $config->isServerForExport());
        $this->settings->set('server_for_pdf', (string) $config->isServerForPdf());
    }
}
