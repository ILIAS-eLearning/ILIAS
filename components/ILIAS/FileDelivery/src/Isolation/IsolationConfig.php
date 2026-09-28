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

namespace ILIAS\FileDelivery\Isolation;

use ILIAS\FileDelivery\Setup\IsolationObjective;

/**
 * Read-only configuration of the IRSS User Content Isolation feature.
 *
 * Loaded from a static PHP artefact written by the setup so that the
 * FileDelivery service can decide whether to deliver content via a
 * dedicated content domain — without any DB dependency.
 *
 * Both the content domain (admin-configured) and the ILIAS domain (derived
 * from the installed http_path at setup time) are baked into the artefact.
 * The runtime therefore never has to read ilias.ini.php to learn either value.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final readonly class IsolationConfig
{
    public const KEY_ACTIVATED = 'activated';
    public const KEY_CONTENT_DOMAIN = 'content_domain';
    public const KEY_ILIAS_DOMAIN = 'ilias_domain';

    public function __construct(
        private bool $activated,
        private ?string $content_domain,
        private ?string $ilias_domain,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $activated = (bool) ($data[self::KEY_ACTIVATED] ?? false);
        $content = self::normalizeUrl($data[self::KEY_CONTENT_DOMAIN] ?? null);
        $ilias = self::normalizeUrl($data[self::KEY_ILIAS_DOMAIN] ?? null);

        // Only the content domain is mandatory for isolation; without a separate
        // origin to deliver from there is nothing to isolate, so it stays off.
        if ($activated && $content === null) {
            $activated = false;
        }

        return new self($activated, $content, $ilias);
    }

    public static function disabled(): self
    {
        return new self(false, null, null);
    }

    /**
     * Load the configuration from the static artefact written by the setup.
     *
     * A missing, unreadable or malformed artefact keeps the feature off, so an
     * installation that never ran the FileDelivery setup behaves as before.
     *
     * @param string|null $path only passed in tests; defaults to the artefact
     *                          location the setup writes to
     */
    public static function fromArtefact(?string $path = null): self
    {
        $path ??= IsolationObjective::PATH();
        if (!is_file($path)) {
            return self::disabled();
        }

        $data = @include $path;

        return self::fromArray(is_array($data) ? $data : []);
    }

    /**
     * Validate and normalize a configured domain to a bare origin
     * ("scheme://host[:port]"), or null if it is not a usable http/https origin.
     *
     * Shared with the setup layer so that the setup and the runtime enforce one
     * identical set of rules for what a content/ILIAS domain may look like.
     */
    public static function normalizeOrigin(mixed $value): ?string
    {
        return self::normalizeUrl($value);
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function getContentDomain(): ?string
    {
        return $this->content_domain;
    }

    public function getIliasDomain(): ?string
    {
        return $this->ilias_domain;
    }

    public function getContentHost(): ?string
    {
        return $this->extractHost($this->content_domain);
    }

    public function getIliasHost(): ?string
    {
        return $this->extractHost($this->ilias_domain);
    }

    private function extractHost(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);
        return is_string($host) && $host !== '' ? $host : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            self::KEY_ACTIVATED => $this->activated,
            self::KEY_CONTENT_DOMAIN => $this->content_domain,
            self::KEY_ILIAS_DOMAIN => $this->ilias_domain,
        ];
    }

    /**
     * Normalize the configured value to a bare origin: "scheme://host[:port]".
     *
     * Security: the result is used both as the base for asset URLs and as the
     * value of the Access-Control-Allow-Origin response header. It must therefore
     * be a clean origin only — any scheme other than http/https, and any userinfo,
     * path, query or fragment, makes the value unusable (returns null, which keeps
     * isolation disabled). The setup layer rejects such input loudly; this is the
     * runtime safety net.
     */
    private static function normalizeUrl(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        // accept either a full URL ("https://host[:port]") or a bare hostname ("host.example.org")
        if (!preg_match('#^[a-z][a-z0-9+.\-]*://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        $parts = parse_url($value);
        if (!is_array($parts) || !isset($parts['host']) || $parts['host'] === '') {
            return null;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        // reject anything that is more than a bare origin
        if (isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])
            || (isset($parts['path']) && $parts['path'] !== '' && $parts['path'] !== '/')
        ) {
            return null;
        }

        $origin = $scheme . '://' . strtolower($parts['host']);
        if (isset($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin;
    }

    /**
     * Reduce a (trusted) application URL to its bare origin "scheme://host[:port]".
     *
     * Unlike {@see self::normalizeOrigin()} this tolerates a path/query/fragment and
     * simply discards them, because the source is the internally configured ILIAS
     * http_path (which may carry a sub-directory) rather than admin-provided config.
     * The result is only ever used as a CORS allow-origin, which must be an origin.
     *
     * Used by the setup layer to derive the ILIAS domain from http_path at build
     * time so the value can be baked into the artefact.
     */
    public static function originFromUrl(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (!preg_match('#^[a-z][a-z0-9+.\-]*://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        $parts = parse_url($value);
        if (!is_array($parts) || !isset($parts['host']) || $parts['host'] === '') {
            return null;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $origin = $scheme . '://' . strtolower($parts['host']);
        if (isset($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin;
    }
}
