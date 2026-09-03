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

namespace ILIAS\Tests\FileDelivery;

use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\FileDelivery\Services;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Algorithm\SHA1;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\HTTP\Services as HttpServices;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ServicesBaseUriTest extends TestCase
{
    private function services(
        IsolationConfig $isolation,
        string $scheme = 'https',
        string $host = 'app.example.org',
        ?int $port = null,
        string $path = '/data/deliver.php'
    ): object {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getScheme')->willReturn($scheme);
        $uri->method('getHost')->willReturn($host);
        $uri->method('getPort')->willReturn($port);
        $uri->method('getPath')->willReturn($path);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $http = $this->createStub(HttpServices::class);
        $http->method('request')->willReturn($request);

        $data_signer = new DataSigner(new SecretKeyRotation(new SecretKey('test_key')), new SHA1());
        $rb = $this->createStub(ResponseBuilder::class);

        // the inner deliveries are required by the constructor but unused by getBaseURI
        $stream = new StreamDelivery($data_signer, $http, $rb, $rb, IsolationConfig::disabled());
        $legacy = new LegacyDelivery($http, $rb, $rb, IsolationConfig::disabled());

        return new class ($stream, $legacy, $data_signer, $http, $isolation) extends Services {
            public function exposedBaseURI(): string
            {
                return $this->getBaseURI();
            }
        };
    }

    private function activeConfig(): IsolationConfig
    {
        return IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);
    }

    public function testUsesRequestHostWhenIsolationDisabled(): void
    {
        $services = $this->services(IsolationConfig::disabled());
        $this->assertSame('https://app.example.org/data', $services->exposedBaseURI());
    }

    public function testKeepsPortWhenIsolationDisabled(): void
    {
        $services = $this->services(IsolationConfig::disabled(), 'https', 'app.example.org', 8443);
        $this->assertSame('https://app.example.org:8443/data', $services->exposedBaseURI());
    }

    public function testRewritesToContentDomainWhenIsolationActive(): void
    {
        $services = $this->services($this->activeConfig());
        $this->assertSame('https://content.example.org/data', $services->exposedBaseURI());
    }

    public function testContentDomainRewriteIgnoresRequestHostAndPort(): void
    {
        // even if the request arrives on a different host/port, the content
        // domain is authoritative for the asset base URI
        $services = $this->services($this->activeConfig(), 'http', 'whatever.example.org', 1234);
        $this->assertSame('https://content.example.org/data', $services->exposedBaseURI());
    }
}
