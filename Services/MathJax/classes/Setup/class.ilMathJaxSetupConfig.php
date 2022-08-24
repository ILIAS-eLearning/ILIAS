<?php

declare(strict_types=1);

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
            (bool) ($this->data['client_enabled'] ?? false),
            (string) ($this->data['client_polyfill_url'] ?? ''),
            (string) ($this->data['client_script_url'] ?? ''),
            (int) ($this->data['client_limiter'] ?? 0),
            (bool) ($this->data['server_enabled'] ?? false),
            (string) ($this->data['server_address'] ?? ''),
            (int) ($this->data['server_timeout'] ?? 0),
            (bool) ($this->data['server_for_browser'] ?? false),
            (bool) ($this->data['server_for_export'] ?? false),
            (bool) ($this->data['server_for_pdf'] ?? false)
        );
    }

    /**
     * Get a data array from a config
     */
    public function getDataFromConfig(ilMathJaxConfig $config): array
    {
        return [
            'client_enabled' => $config->isClientEnabled(),
            'client_polyfill_url' => $config->getClintPolyfillUrl(),
            'client_script_url' => $config->getClientScriptUrl(),
            'client_limiter' => $config->getClientLimiter(),
            'server_enabled' => $config->isServerEnabled(),
            'server_address' => $config->getServerAddress(),
            'server_timeout' => $config->getServerTimeout(),
            'server_for_browser' => $config->isServerForBrowser(),
            'server_for_export' => $config->isServerForExport(),
            'server_for_pdf' => $config->isServerForPdf()
        ];
    }

    /**
     * Check if the setup config can be applied to an existing config
     */
    public function isApplicableTo(ilMathJaxConfig $config): bool
    {
        return isset($this->data['client_enabled']) && $this->config->isClientEnabled() !== $config->isClientEnabled()
            || isset($this->data['client_polyfill_url']) && $this->config->getClintPolyfillUrl() !== $config->getClintPolyfillUrl()
            || isset($this->data['client_script_url']) && $this->config->getClientScriptUrl() !== $config->getClientScriptUrl()
            || isset($this->data['client_limiter']) && $this->config->getClientLimiter() !== $config->getClientLimiter()
            || isset($this->data['server_enabled']) && $this->config->isServerEnabled() !== $config->isServerEnabled()
            || isset($this->data['server_address']) && $this->config->getServerAddress() !== $config->getServerAddress()
            || isset($this->data['server_timeout']) && $this->config->getServerAddress() !== $config->getServerAddress()
            || isset($this->data['server_for_browser']) && $this->config->isServerForBrowser() !== $config->isServerForBrowser()
            || isset($this->data['server_for_export']) && $this->config->isServerForExport() !== $config->isServerForExport()
            || isset($this->data['server_for_pdf']) && $this->config->isServerForPdf() !== $config->isServerForPdf();
    }

    /**
     * Apply the setup config to an existing config
     */
    public function applyTo(ilMathJaxConfig $config): ilMathJaxConfig
    {
        if (isset($this->data['client_enabled'])) {
            $config = $config->withClientEnabled($this->config->isClientEnabled());
        }
        if (isset($this->data['client_polyfill_url'])) {
            $config = $config->withClientPolyfillUrl($this->config->getClintPolyfillUrl());
        }
        if (isset($this->data['client_script_url'])) {
            $config = $config->withClientScriptUrl($this->config->getClientScriptUrl());
        }
        if (isset($this->data['client_limiter'])) {
            $config = $config->withClientLimiter($this->config->getClientLimiter());
        }
        if (isset($this->data['server_enabled'])) {
            $config = $config->withServerEnabled($this->config->isServerEnabled());
        }
        if (isset($this->data['server_address'])) {
            $config = $config->withServerAddress($this->config->getServerAddress());
        }
        if (isset($this->data['server_timeout'])) {
            $config = $config->withServerTimeout($this->config->getServerTimeout());
        }
        if (isset($this->data['server_for_browser'])) {
            $config = $config->withServerForBrowser($this->config->isServerForBrowser());
        }
        if (isset($this->data['server_for_export'])) {
            $config = $config->withServerForExport($this->config->isServerForExport());
        }
        if (isset($this->data['server_for_pdf'])) {
            $config = $config->withServerForPdf($this->config->isServerForPdf());
        }

        return $config;
    }
}
