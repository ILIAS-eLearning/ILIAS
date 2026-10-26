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

namespace ILIAS\WOPI\Discovery;

use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Crawler
{
    /**
     * @var string
     */
    private const XPATH = '/wopi-discovery/net-zone/app';
    /**
     * the discovery and the URLs it publishes must be web URLs, anything else (file, php, ...)
     * would let the discovery read local resources or launch script in the ILIAS origin
     */
    public const ALLOWED_SCHEMES = ['https', 'http'];
    private const TIMEOUT_SECONDS = 10;
    private const MAX_REDIRECTS = 3;
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024;
    private array $crawl_actions = [];
    private ?string $content = null;
    private ?\SimpleXMLElement $discovery = null;
    /**
     * @var \SimpleXMLElement[]|null
     */
    private ?array $xml_app_elements = null;

    public function __construct()
    {
        $this->crawl_actions = [
            ActionTarget::VIEW->value,
            ActionTarget::EMBED_VIEW->value,
            ActionTarget::EDIT->value,
            ActionTarget::EMBED_EDIT->value,
            ActionTarget::EDIT_NEW->value,
            ActionTarget::CONVERT->value,
        ];
    }

    public static function isWebUrl(URI $url): bool
    {
        return in_array(strtolower($url->getSchema()), self::ALLOWED_SCHEMES, true);
    }

    public function validate(URI $discovery_url): bool
    {
        if (!self::isWebUrl($discovery_url)) {
            return false;
        }
        try {
            return $this->load($this->fetch($discovery_url));
        } catch (\Throwable) {
            return false;
        }
    }

    private function fetch(URI $discovery_url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::TIMEOUT_SECONDS,
                'max_redirects' => self::MAX_REDIRECTS,
            ],
        ]);
        // one byte more than allowed tells a discovery which is too large
        $content = @file_get_contents((string) $discovery_url, false, $context, 0, self::MAX_SIZE_BYTES + 1);
        if ($content === false || $content === '' || strlen($content) > self::MAX_SIZE_BYTES) {
            return null;
        }

        return $content;
    }

    private function load(?string $content): bool
    {
        try {
            $this->content = $content;
            if ($this->content === null) {
                return false;
            }

            // invalid XML is reported by returning false, not by warnings
            $use_internal_errors = libxml_use_internal_errors(true);
            $this->discovery = simplexml_load_string($this->content) ?: null;
            libxml_clear_errors();
            libxml_use_internal_errors($use_internal_errors);
            if ($this->discovery === null) {
                return false;
            }
            $this->xml_app_elements = $this->discovery->xpath(self::XPATH);

            return is_array($this->xml_app_elements);
        } catch (\Throwable) {
            return false;
        }
    }

    public function crawl(URI $discovery_url): ?Apps
    {
        if (!$this->validate($discovery_url)) {
            return null;
        }

        return $this->parse();
    }

    /**
     * Parses discovery XML that has already been fetched.
     */
    public function crawlContent(string $content): ?Apps
    {
        if (!$this->load($content)) {
            return null;
        }

        return $this->parse();
    }

    /**
     * The proof keys of the discovery read last, null if it publishes none.
     */
    public function getProofKeys(): ?ProofKeys
    {
        $element = $this->discovery?->xpath('/wopi-discovery/proof-key')[0] ?? null;
        if (!$element instanceof \SimpleXMLElement) {
            return null;
        }
        $modulus = (string) ($element['modulus'] ?? '');
        $exponent = (string) ($element['exponent'] ?? '');
        if ($modulus === '' || $exponent === '') {
            return null;
        }
        $old_modulus = (string) ($element['oldmodulus'] ?? '');
        $old_exponent = (string) ($element['oldexponent'] ?? '');

        return new ProofKeys(
            new ProofKey($modulus, $exponent),
            $old_modulus === '' || $old_exponent === '' ? null : new ProofKey($old_modulus, $old_exponent)
        );
    }

    private function parse(): Apps
    {
        // read wopi-discovery XML from $discovery_url and parse Apps with it's Actions
        $apps = [];
        foreach ($this->xml_app_elements as $app) {
            $actions = [];
            foreach ($app->action as $action) {
                $action_name = $action['name'] ?? null;
                $action_ext = $action['ext'] ?? null;
                $action_urlsrc = $action['urlsrc'] ?? null;
                // the extension of the resulting file: targetext for convert, newext for editnew
                $target_text = isset($action['targetext'])
                    ? (string) $action['targetext']
                    : (isset($action['newext']) ? (string) $action['newext'] : null);
                if (!$action_name instanceof \SimpleXMLElement) {
                    continue;
                }
                if (!$action_ext instanceof \SimpleXMLElement) {
                    continue;
                }
                if (!$action_urlsrc instanceof \SimpleXMLElement) {
                    continue;
                }

                if (!in_array((string) $action_name, $this->crawl_actions, true)) {
                    continue;
                }

                $uri_string = rtrim((string) $action_urlsrc, '?');
                // remove all after ?
                $uri = explode('?', $uri_string);
                $uri_string = $uri[0];
                try {
                    $launcher_url = new URI($uri_string);
                } catch (\Throwable) {
                    continue;
                }
                if (!self::isWebUrl($launcher_url)) {
                    continue;
                }
                $actions[] = new Action(
                    0,
                    (string) $action_name,
                    (string) $action_ext,
                    $launcher_url,
                    $uri[1] ?? null,
                    $target_text
                );
            }
            if ($actions === []) {
                continue;
            }

            $app_name = $app['name'] ?? null;
            if ($app_name === null) {
                continue;
            }
            $apps[] = new App(
                0,
                (string) $app_name,
                $actions,
                $this->favIconUrl($app)
            );
        }
        return new Apps($apps);
    }

    private function favIconUrl(\SimpleXMLElement $app): ?URI
    {
        $value = $app['favIconUrl'] ?? null;
        if ($value === null) {
            return null;
        }
        try {
            $url = new URI((string) $value);
        } catch (\Throwable) {
            return null;
        }

        return self::isWebUrl($url) ? $url : null;
    }
}
