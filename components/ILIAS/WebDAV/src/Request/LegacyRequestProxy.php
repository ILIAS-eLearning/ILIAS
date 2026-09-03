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
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class LegacyRequestProxy implements ServerRequestInterface
{
    private function request(): ServerRequestInterface
    {
        $dic = $GLOBALS['DIC'] ?? null;
        if ($dic === null) {
            throw new \RuntimeException('Global DIC is not initialized; cannot access HTTP request.');
        }
        return $dic->http()->request();
    }

    public function getProtocolVersion(): string
    {
        return $this->request()->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        return $this->request()->withProtocolVersion($version);
    }

    public function getHeaders(): array
    {
        return $this->request()->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->request()->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->request()->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->request()->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        return $this->request()->withHeader($name, $value);
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        return $this->request()->withAddedHeader($name, $value);
    }

    public function withoutHeader(string $name): MessageInterface
    {
        return $this->request()->withoutHeader($name);
    }

    public function getBody(): StreamInterface
    {
        return $this->request()->getBody();
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->request()->withBody($body);
    }

    public function getRequestTarget(): string
    {
        return $this->request()->getRequestTarget();
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        return $this->request()->withRequestTarget($requestTarget);
    }

    public function getMethod(): string
    {
        return $this->request()->getMethod();
    }

    public function withMethod(string $method): RequestInterface
    {
        return $this->request()->withMethod($method);
    }

    public function getUri(): UriInterface
    {
        return $this->request()->getUri();
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        return $this->request()->withUri($uri, $preserveHost);
    }

    public function getServerParams(): array
    {
        return $this->request()->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->request()->getCookieParams();
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->request()->withCookieParams($cookies);
    }

    public function getQueryParams(): array
    {
        return $this->request()->getQueryParams();
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        return $this->request()->withQueryParams($query);
    }

    public function getUploadedFiles(): array
    {
        return $this->request()->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this->request()->withUploadedFiles($uploadedFiles);
    }

    public function getParsedBody()
    {
        return $this->request()->getParsedBody();
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        return $this->request()->withParsedBody($data);
    }

    public function getAttributes(): array
    {
        return $this->request()->getAttributes();
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->request()->getAttribute($name, $default);
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        return $this->request()->withAttribute($name, $value);
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        return $this->request()->withoutAttribute($name);
    }
}
