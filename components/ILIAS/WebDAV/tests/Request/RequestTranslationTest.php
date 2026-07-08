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

namespace ILIAS\WebDAV\Tests\Request;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ILIAS\WebDAV\Config;
use ILIAS\WebDAV\Request\RequestTranslation;

#[Small]
final class RequestTranslationTest extends TestCase
{
    private function buildRequest(string $path, array $query_params = []): ServerRequestInterface
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn($path);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getQueryParams')->willReturn($query_params);

        return $request;
    }

    private function buildConfig(string $client_id = 'default'): Config
    {
        return new class ($client_id) extends Config {
            public function __construct(private string $client)
            {
            }
            public function getClientId(): string
            {
                return $this->client;
            }
        };
    }

    #[Test]
    public function getRequestedPath_withClientSegment_isStripped(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/default/ref_92/sub/file.txt')
        );

        $this->assertSame('ref_92/sub/file.txt', $translation->getRequestedPath());
    }

    #[Test]
    public function getRequestedPath_withoutClientSegment_isReturnedAsIs(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/ref_92/sub/file.txt')
        );

        $this->assertSame('ref_92/sub/file.txt', $translation->getRequestedPath());
    }

    #[Test]
    public function getRequestedPath_endpointOnly_returnsEmpty(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/')
        );

        $this->assertSame('', $translation->getRequestedPath());
    }

    #[Test]
    public function getRequestedPath_clientLooksLikeRef_isNotStripped(): void
    {
        // ensure that "ref_92" is never accidentally treated as a client segment
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/ref_92')
        );

        $this->assertSame('ref_92', $translation->getRequestedPath());
    }

    #[Test]
    public function getRequestedPathAsArray_splitsOnSlashes(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/ref_92/folder/file.txt')
        );

        $this->assertSame(
            ['ref_92', 'folder', 'file.txt'],
            $translation->getRequestedPathAsArray()
        );
    }

    #[Test]
    public function getBasePath_withClientSegment_includesClientId(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/default/ref_92')
        );

        $this->assertSame('/webdav.php/default/', $translation->getBasePath());
    }

    #[Test]
    public function getBasePath_withoutClientSegment_excludesClientId(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/ref_92')
        );

        $this->assertSame('/webdav.php/', $translation->getBasePath());
    }

    #[Test]
    public function getBasePath_emptyClientId_excludesClientCheck(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig(''),
            $this->buildRequest('/webdav.php/default/ref_92')
        );

        $this->assertSame('/webdav.php/', $translation->getBasePath());
    }

    #[Test]
    public function showMountPoint_queryParamPresent_returnsTrue(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/ref_92', ['mount-instructions' => ''])
        );

        $this->assertTrue($translation->showMountPoint());
    }

    #[Test]
    public function showMountPoint_queryParamMissing_returnsFalse(): void
    {
        $translation = new RequestTranslation(
            $this->buildConfig('default'),
            $this->buildRequest('/webdav.php/ref_92', [])
        );

        $this->assertFalse($translation->showMountPoint());
    }
}
