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

namespace ILIAS\WebDAV\Tests\Mount;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ILIAS\WebDAV\Config;
use ILIAS\WebDAV\Mount\UriBuilder;

#[Small]
final class UriBuilderTest extends TestCase
{
    private function buildRequest(string $path, string $scheme = 'http', string $host = 'example.org', ?int $port = null): ServerRequestInterface
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn($path);
        $uri->method('getScheme')->willReturn($scheme);
        $uri->method('getHost')->willReturn($host);
        $uri->method('getPort')->willReturn($port);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

    private function buildConfig(bool $prepend_client_name, string $client_id = 'default'): Config
    {
        return new class ($prepend_client_name, $client_id) extends Config {
            public function __construct(
                private bool $prepend,
                private string $client
            ) {
            }
            public function prependClientName(): bool
            {
                return $this->prepend;
            }
            public function getClientId(): string
            {
                return $this->client;
            }
        };
    }

    #[Test]
    public function getWebDavDefaultUri_withClientNameEnabled_includesClientSegment(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_92');
        $builder = new UriBuilder($request, $this->buildConfig(true));

        $this->assertSame(
            'http://example.org/webdav.php/default/ref_92',
            $builder->getWebDavDefaultUri(92)
        );
    }

    #[Test]
    public function getWebDavDefaultUri_withClientNameDisabled_skipsClientSegment(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_92');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'http://example.org/webdav.php/ref_92',
            $builder->getWebDavDefaultUri(92)
        );
    }

    #[Test]
    public function getWebDavDefaultUri_https_emitsHttpsScheme(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_5', 'https');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'https://example.org/webdav.php/ref_5',
            $builder->getWebDavDefaultUri(5)
        );
    }

    #[Test]
    public function getWebDavKonquerorUri_https_emitsWebdavsScheme(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_1', 'https');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'webdavs://example.org/webdav.php/ref_1',
            $builder->getWebDavKonquerorUri(1)
        );
    }

    #[Test]
    public function getWebDavNautilusUri_http_emitsDavScheme(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_1');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'dav://example.org/webdav.php/ref_1',
            $builder->getWebDavNautilusUri(1)
        );
    }

    #[Test]
    public function nonStandardPort_isAppendedToHost(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_42', 'http', 'example.org', 8080);
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'http://example.org:8080/webdav.php/ref_42',
            $builder->getWebDavDefaultUri(42)
        );
    }

    #[Test]
    public function port80_isOmittedFromHost(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_42', 'http', 'example.org', 80);
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'http://example.org/webdav.php/ref_42',
            $builder->getWebDavDefaultUri(42)
        );
    }

    #[Test]
    public function basePath_resolvesEvenWhenEndpointMissingFromUri(): void
    {
        $request = $this->buildRequest('/some/path/index.html');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            'http://example.org/some/path/webdav.php/ref_7',
            $builder->getWebDavDefaultUri(7)
        );
    }

    #[Test]
    public function getUriToMountInstructionModalByRef_appendsQuery(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_92');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            '/webdav.php/ref_92?mount-instructions',
            $builder->getUriToMountInstructionModalByRef(92)
        );
    }

    #[Test]
    public function getUriToMountInstructionModalByLanguage_includesLanguageAndQuery(): void
    {
        $request = $this->buildRequest('/webdav.php/de');
        $builder = new UriBuilder($request, $this->buildConfig(false));

        $this->assertSame(
            '/webdav.php/de?mount-instructions',
            $builder->getUriToMountInstructionModalByLanguage('de')
        );
    }

    #[Test]
    public function getUriToMountInstructionModalByRef_withClientName_includesClientSegment(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_92');
        $builder = new UriBuilder($request, $this->buildConfig(true, 'default'));

        $this->assertSame(
            '/webdav.php/default/ref_92?mount-instructions',
            $builder->getUriToMountInstructionModalByRef(92)
        );
    }

    #[Test]
    public function clientName_emptyEvenIfFlagTrue_skipsClientSegment(): void
    {
        $request = $this->buildRequest('/webdav.php/ref_92');
        $builder = new UriBuilder($request, $this->buildConfig(true, ''));

        $this->assertSame(
            'http://example.org/webdav.php/ref_92',
            $builder->getWebDavDefaultUri(92)
        );
    }
}
