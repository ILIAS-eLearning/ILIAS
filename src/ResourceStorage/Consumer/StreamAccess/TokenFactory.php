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

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\Filesystem\Stream\FileStream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class TokenFactory
{
    use Hasher;

    private string $storage_dir;

    public function __construct(string $storage_dir)
    {
        $this->storage_dir = $storage_dir;
        if (!is_dir($this->storage_dir)) {
            throw new \InvalidArgumentException('Storage directory does not exist');
        }
    }

    public function check(string $access_key): Token
    {
        $uri = $this->unhash($access_key);
        $expanded_uri = $this->expandUri($uri);
        $this->checkURI($expanded_uri);

        return new Token($expanded_uri, $access_key);
    }

    public function lease(FileStream $stream, bool $with_stream_attached = false): Token
    {
        $uri = $stream->getMetadata()['uri'] ?? '';
        $this->checkURI($uri);
        $reduced_uri = $this->reduceURI($uri);
        $access_key = $this->hash($reduced_uri);

        if ($with_stream_attached) {
            $token_stream = new TokenStream($stream->detach());

            return new Token($uri, $access_key, $token_stream);
        }

        return new Token($uri, $access_key);
    }


    private function checkURI(string $uri): void
    {
        if ($this->isMemoryStream($uri)) {
            return;
        }

        if (
            strpos($uri, $this->storage_dir) === false
            || !is_readable($uri)
            || !file_exists($uri)
        ) {
            throw new \InvalidArgumentException("Invalid access key or path: $uri");
        }
    }

    private function reduceURI(string $uri): string
    {
        if (!$this->isMemoryStream($uri)) {
            return str_replace($this->storage_dir, '', $uri);
        }
        return $uri;
    }

    private function expandURI(string $uri): string
    {
        if (!$this->isMemoryStream($uri)) {
            return $this->storage_dir . $uri;
        }
        return $uri;
    }

    protected function isMemoryStream(string $path): bool
    {
        return $path === StreamAccess::PHP_MEMORY;
    }
}
