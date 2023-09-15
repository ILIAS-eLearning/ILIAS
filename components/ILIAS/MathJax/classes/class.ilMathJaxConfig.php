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

/**
 * Global Mathjax configuration
 */
class ilMathJaxConfig
{
    private const LIMITER_MATHJAX = 0;
    private const LIMITER_TEX = 1;
    private const LIMITER_SPAN = 2;

    protected bool $client_enabled;
    protected string $client_polyfill_url;
    protected string $client_script_url;
    protected int $client_limiter;
    protected bool $server_enabled;
    protected string $server_address;
    protected int $server_timeout;
    protected bool $server_for_browser;
    protected bool $server_for_export;
    protected bool $server_for_pdf;

    /**
     * Constructor
     */
    public function __construct(
        bool $client_enabled,
        string $client_polyfill_url,
        string $client_script_url,
        int $client_limiter,
        bool $server_enabled,
        string $server_address,
        int $server_timeout,
        bool $server_for_browser,
        bool $server_for_export,
        bool $server_for_pdf
    ) {
        $this->client_enabled = $client_enabled;
        $this->client_polyfill_url = trim($client_polyfill_url);
        $this->client_script_url = trim($client_script_url);
        $this->client_limiter = (in_array(
            $client_limiter,
            [self::LIMITER_MATHJAX, self::LIMITER_TEX, self::LIMITER_SPAN]
        ) ? $client_limiter : self::LIMITER_MATHJAX);
        $this->server_enabled = $server_enabled;
        $this->server_address = trim($server_address);
        $this->server_timeout = (empty($server_timeout) ? 5 : $server_timeout);
        $this->server_for_browser = $server_for_browser;
        $this->server_for_export = $server_for_export;
        $this->server_for_pdf = $server_for_pdf;
    }

    /**
     * Should latex code be rendered in the browser
     */
    public function isClientEnabled(): bool
    {
        return $this->client_enabled;
    }

    /**
     * Url of a javascript polyfill (needed by MathJax 3)
     */
    public function getClintPolyfillUrl(): string
    {
        return $this->client_polyfill_url;
    }

    /**
     * Url of Mathjax script to be embedded with script tag on the page
     */
    public function getClientScriptUrl(): string
    {
        return $this->client_script_url;
    }

    /**
     * Type of enclosing limiters for wich the embedded client-side Mathjax is configured
     */
    public function getClientLimiter(): int
    {
        return $this->client_limiter;
    }

    /**
     * Get the avaliable options for the client limiters
     * @return array limiter => display text
     */
    public function getClientLimiterOptions(): array
    {
        return [
            self::LIMITER_MATHJAX => '\&#8203;(...\&#8203;)',
            self::LIMITER_TEX => '[tex]...[/tex]',
            self::LIMITER_SPAN => '&lt;span class="math"&gt;...&lt;/span&gt;'
        ];
    }

    /**
     * Start limiter of Latex code which the client-side Mathjax searches for
     */
    public function getClientLimiterStart(): string
    {
        switch ($this->client_limiter) {
            case self::LIMITER_TEX:
                return '[tex]';
            case self::LIMITER_SPAN:
                return '<span class="math">';
            case self::LIMITER_MATHJAX:
            default:
                return '\(';
        }
    }

    /**
     * End limiter of Latex code which the client-side Mathjax searches for
     */
    public function getClientLimiterEnd(): string
    {
        switch ($this->client_limiter) {
            case self::LIMITER_TEX:
                return '[/tex]';
            case self::LIMITER_SPAN:
                return '</span>';
            case self::LIMITER_MATHJAX:
            default:
                return '\)';
        }
    }

    /**
     * Is a server side rendering engine configured and enabled
     */
    public function isServerEnabled(): bool
    {
        return $this->server_enabled;
    }

    /**
     * Url of Mathjax server
     */
    public function getServerAddress(): string
    {
        return $this->server_address;
    }

    /**
     * timeout (s) to wait for the result of the rendering server
     */
    public function getServerTimeout(): int
    {
        return $this->server_timeout;
    }

    /**
     * Should the server-side rendingeing be used for browser output
     */
    public function isServerForBrowser(): bool
    {
        return $this->server_for_browser;
    }

    /**
     * Should the server-side rendingeing be used for HTML exports
     */
    public function isServerForExport(): bool
    {
        return $this->server_for_export;
    }

    /**
     * Should the server-side rendingeing be used for PDF generation
     */
    public function isServerForPdf(): bool
    {
        return $this->server_for_pdf;
    }

    /**
     * Enable latex code bing rendered in the browser
     */
    public function withClientEnabled(bool $client_enabled): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->client_enabled = $client_enabled;
        return $clone;
    }

    /**
     * Set the url of a polyfill script neededby MathJax 3
     */
    public function withClientPolyfillUrl(string $client_js_url): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->client_polyfill_url = $client_js_url;
        return $clone;
    }

    /**
     * Set the url of Mathjax script to be embedded on the page (for MathJax 3)
     */
    public function withClientScriptUrl(string $client_async_url): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->client_script_url = $client_async_url;
        return $clone;
    }

    /**
     * Set the type of enclosing limiters for wich the embedded client-side Mathjax is configured
     */
    public function withClientLimiter(int $client_limiter): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->client_limiter = $client_limiter;
        return $clone;
    }

    /**
     * Enable a server side rendering engine configured and enabled
     */
    public function withServerEnabled(bool $server_enabled): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->server_enabled = $server_enabled;
        return $clone;
    }

    /**
     * Set the url of the Mathjax server
     */
    public function withServerAddress(string $server_address): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->server_address = $server_address;
        return $clone;
    }

    /**
     * Set the timeout (s) to wait for the result of the rendering server
     */
    public function withServerTimeout(int $server_timeout): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->server_timeout = $server_timeout;
        return $clone;
    }

    /**
     * Enable the server-side rendingeing for browser output
     */
    public function withServerForBrowser(bool $server_for_browser): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->server_for_browser = $server_for_browser;
        return $clone;
    }

    /**
     * Enable the server-side rendingeing for HTML exports
     */
    public function withServerForExport(bool $server_for_export): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->server_for_export = $server_for_export;
        return $clone;
    }

    /**
     * Enable the server-side rendingeing for PDF generation
     */
    public function withServerForPdf(bool $server_for_pdf): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->server_for_pdf = $server_for_pdf;
        return $clone;
    }
}
