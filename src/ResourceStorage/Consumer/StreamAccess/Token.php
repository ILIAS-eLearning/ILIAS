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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Token
{
    use Hasher;

    private bool $locked = false;
    /**
     * @readonly
     */
    private Encrypt $encrypt;
    private string $uri;
    private string $access_key;
    private ?\ILIAS\ResourceStorage\Consumer\StreamAccess\TokenStream $stream = null;

    public function __construct(
        string $uri,
        string $access_key,
        ?\ILIAS\ResourceStorage\Consumer\StreamAccess\TokenStream $stream = null
    ) {
        $this->uri = $uri;
        $this->access_key = $access_key;
        $this->stream = $stream;
        $this->sanityCheck();
    }

    private function sanityCheck(): void
    {
        if (!$this->hasInMemoryStream() && !is_file($this->uri)) {
            throw new \InvalidArgumentException('File does not exist');
        }
        // Check relative path
        $relative_uri = $this->unhash($this->access_key);
        if (strpos($this->uri, $relative_uri) === false) {
            throw new \InvalidArgumentException('Access key does not match');
        }
        if ($this->stream !== null && $this->stream->getMetadata()['uri'] !== $this->uri) {
            throw new \InvalidArgumentException('Stream does not match');
        }
        if ($this->stream !== null && strpos((string)$this->stream->getMetadata()['uri'], $relative_uri) === false) {
            throw new \InvalidArgumentException('Stream does not match');
        }
    }

    /**
     * @description This Method will be used to unlock Streams in the future and will be a replacement for WAC tokens
     */
    private function unlock(UnlockKey $unlock_key): bool
    {
        return $this->locked = $this->encrypt->check($this, $unlock_key);
    }

    private function isLocked(): bool
    {
        return $this->locked;
    }


    public function hasStreamableStream(): bool
    {
        return $this->stream !== null && !$this->hasInMemoryStream();
    }


    public function getAccessKey(): string
    {
        return $this->access_key;
    }

    public function resolveStream(): TokenStream
    {
        if ($this->locked) {
            throw new \BadMethodCallException('Token is locked und must be unlocked before resolving the stream.');
        }

        if ($this->stream === null) {
            $this->stream = new TokenStream(fopen($this->uri, 'rb'));
        }
        $this->sanityCheck();

        return $this->stream;
    }

    public function hasInMemoryStream(): bool
    {
        return $this->uri === StreamAccess::PHP_MEMORY;
    }
}
