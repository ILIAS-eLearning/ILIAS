<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Repository for storing and loading the MathJax configiration
 */
class ilMathJaxConfigSettingsRepository implements ilMathJaxConfigRespository
{

    /** @var ilSetting */
    protected $settings;

    /**
     * Constructor
     * @param ilSettingsFactory $factory
     */
    public function __construct(ilSettingsFactory $factory)
    {
        $this->settings = $factory->settingsFor('MathJax');
    }

    /**
     * Get the MathJax Configuration
     * @return ilMathJaxConfig
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
     * @param ilMathJaxConfig $config
     */
    public function updateConfig(ilMathJaxConfig $config)
    {
        $this->settings->set('enable', (string) $config->isClientEnabled());
        $this->settings->set('path_to_polyfill', $config->getClintPolyfillUrl());
        $this->settings->set('path_to_mathjax', $config->getClientScriptUrl());
        $this->settings->set('limiter', (string) $config->getClientLimiter());
        $this->settings->set('enable_server', (string) $config->isServerEnabled());
        $this->settings->set('server_address', (string) $config->getServerAddress());
        $this->settings->set('server_timeout', (string) $config->getServerTimeout());
        $this->settings->set('server_for_browser', (string) $config->isServerForBrowser());
        $this->settings->set('server_for_export', (string) $config->isServerForExport());
        $this->settings->set('server_for_pdf', (string) $config->isServerForPdf());
    }
}