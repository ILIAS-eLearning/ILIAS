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

namespace ILIAS\WebDAV\Request;

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\WebDAV\Config;
use ILIAS\HTTP\Wrapper\SuperGlobalDropInReplacement;

/**
 * @internal
 */
class RequestTranslation
{
    private string $endpoint;
    private SuperGlobalDropInReplacement|array $post;

    public function __construct(
        private Config $config,
        private ServerRequestInterface $request
    ) {
        $this->endpoint = '/' . trim($this->config->getEndpoint() . '/', '/');
    }

    public function getRequestedPath(): string
    {
        $path = $this->request->getUri()->getPath();

        $requested_path = ltrim(substr($path, strpos($path, $this->endpoint) + strlen($this->endpoint)), '/');

        // Legacy client-name segment is optional: support both
        // /webdav.php/<client_id>/ref_X and /webdav.php/ref_X
        $client_id = $this->config->getClientId();
        if ($client_id !== '') {
            $segments = explode('/', $requested_path, 2);
            if (($segments[0] ?? '') === $client_id) {
                return $segments[1] ?? '';
            }
        }

        return $requested_path;
    }

    public function getBasePath(): string
    {
        $path = $this->request->getUri()->getPath();
        $end = strpos($path, $this->endpoint);
        if ($end === false) {
            return rtrim($this->endpoint, '/') . '/';
        }
        $base = substr($path, 0, $end) . $this->endpoint . '/';

        $client_id = $this->config->getClientId();
        if ($client_id === '') {
            return $base;
        }

        $remainder = ltrim(substr($path, $end + strlen($this->endpoint)), '/');
        $first_segment = explode('/', $remainder, 2)[0] ?? '';
        if ($first_segment === $client_id) {
            return $base . $client_id . '/';
        }

        return $base;
    }

    public function getRequestedPathAsArray(): array
    {
        $path = $this->getRequestedPath();
        return explode('/', $path);
    }

    public function showMountPoint(): bool
    {
        return array_key_exists($this->config->getMountInstructionsQuery(), $this->request->getQueryParams());
    }

    public function setup(): void
    {
        $this->post = $_POST;
        $_POST = (array) $this->post;
        $HTTP_POST_VARS = $_POST;
    }

    public function close(): void
    {
        $_POST = $this->post;
    }
}
