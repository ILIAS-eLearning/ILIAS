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

namespace ILIAS\WebDAV\Mount;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ILIAS\WebDAV\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class UriBuilder
{
    /**
     * @var array<string, string>
     */
    private const array SCHEMAS = [
        'default' => 'http',
        'konqueror' => 'webdav',
        'nautilus' => 'dav',
    ];

    private ?UriInterface $uri = null;
    private ?string $host = null;
    private ?string $base_path = null;

    public function __construct(
        private ServerRequestInterface $request,
        private Config $config
    ) {
    }

    private function uri(): UriInterface
    {
        return $this->uri ??= $this->request->getUri();
    }

    private function host(): string
    {
        if ($this->host !== null) {
            return $this->host;
        }
        $host = $this->uri()->getHost();
        $port = $this->uri()->getPort();
        if ($port !== null && !in_array($port, [80, 443], true)) {
            $host .= ':' . $port;
        }
        return $this->host = $host;
    }

    private function basePath(): string
    {
        return $this->base_path ??= $this->resolveBasePath($this->uri()->getPath());
    }

    private function resolveBasePath(string $original_path): string
    {
        $endpoint = $this->config->getEndpoint();
        $segments = explode('/', $original_path);

        if (in_array($endpoint, $segments, true)) {
            $kept = [];
            foreach ($segments as $segment) {
                $kept[] = $segment;
                if ($segment === $endpoint) {
                    break;
                }
            }
            return implode('/', $kept);
        }

        $kept = array_slice($segments, 0, -1);
        return implode('/', $kept) . '/' . $endpoint;
    }

    public function getWebDavDefaultUri(int $ref_id): string
    {
        return $this->buildSchemedUri('default', $this->getWebDavPathToRef($ref_id));
    }

    public function getWebDavKonquerorUri(int $ref_id): string
    {
        return $this->buildSchemedUri('konqueror', $this->getWebDavPathToRef($ref_id));
    }

    public function getWebDavNautilusUri(int $ref_id): string
    {
        return $this->buildSchemedUri('nautilus', $this->getWebDavPathToRef($ref_id));
    }

    public function getUriToMountInstructionModalByRef(int $ref_id): string
    {
        return $this->getWebDavPathToRef($ref_id) . '?' . $this->config->getMountInstructionsQuery();
    }

    public function getUriToMountInstructionModalByLanguage(string $language): string
    {
        return $this->getWebDavBasePath() . '/' . $language . '?' . $this->config->getMountInstructionsQuery();
    }

    private function getWebDavPathToRef(int $ref_id): string
    {
        return $this->getWebDavBasePath() . '/ref_' . $ref_id;
    }

    private function getWebDavBasePath(): string
    {
        if ($this->config->prependClientName() && $this->config->getClientId() !== '') {
            return $this->basePath() . '/' . $this->config->getClientId();
        }
        return $this->basePath();
    }

    private function buildSchemedUri(string $placeholder, string $path): string
    {
        $scheme = self::SCHEMAS[$placeholder];
        if ($this->uri()->getScheme() === 'https') {
            $scheme .= 's';
        }
        return $scheme . '://' . $this->host() . $path;
    }
}
