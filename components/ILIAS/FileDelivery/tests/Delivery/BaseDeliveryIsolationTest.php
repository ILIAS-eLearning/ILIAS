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

namespace ILIAS\Tests\FileDelivery\Delivery;

use GuzzleHttp\Psr7\Response;
use ILIAS\FileDelivery\Delivery\BaseDelivery;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\HTTP\Services as HttpServices;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class BaseDeliveryIsolationTest extends TestCase
{
    private function delivery(IsolationConfig $isolation, string $request_host = 'app.example.org'): BaseDelivery
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getHost')->willReturn($request_host);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $http = $this->createStub(HttpServices::class);
        $http->method('request')->willReturn($request);

        $rb = $this->createStub(ResponseBuilder::class);

        return new class ($http, $rb, $rb, $isolation) extends BaseDelivery {
            public function exposedHostAllowed(): bool
            {
                return $this->isRequestHostAllowed();
            }

            public function exposedOnContentHost(): bool
            {
                return $this->isRequestOnContentHost();
            }

            public function exposedIsolationHeaders(ResponseInterface $r): ResponseInterface
            {
                return $this->applyIsolationHeaders($r);
            }

            public function exposedGeneralHeaders(ResponseInterface $r): ResponseInterface
            {
                return $this->setGeneralHeaders(
                    $r,
                    '/tmp/user_content.txt',
                    'text/plain',
                    'user_content.txt',
                    Disposition::INLINE
                );
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

    public function testHostAllowedWhenIsolationDisabled(): void
    {
        $delivery = $this->delivery(IsolationConfig::disabled(), 'anything.example.org');
        $this->assertTrue($delivery->exposedHostAllowed());
    }

    public function testHostAllowedWhenRequestHitsContentHost(): void
    {
        $delivery = $this->delivery($this->activeConfig(), 'content.example.org');
        $this->assertTrue($delivery->exposedHostAllowed());
    }

    public function testHostAllowedIsCaseInsensitive(): void
    {
        $delivery = $this->delivery($this->activeConfig(), 'Content.Example.ORG');
        $this->assertTrue($delivery->exposedHostAllowed());
    }

    public function testHostRejectedWhenRequestHitsIliasHost(): void
    {
        $delivery = $this->delivery($this->activeConfig(), 'app.example.org');
        $this->assertFalse($delivery->exposedHostAllowed());
    }

    public function testNotOnContentHostWhenIsolationDisabled(): void
    {
        $delivery = $this->delivery(IsolationConfig::disabled(), 'content.example.org');
        $this->assertFalse($delivery->exposedOnContentHost());
    }

    public function testOnContentHostWhenRequestHitsContentHost(): void
    {
        $delivery = $this->delivery($this->activeConfig(), 'content.example.org');
        $this->assertTrue($delivery->exposedOnContentHost());
    }

    public function testOnContentHostIsCaseInsensitive(): void
    {
        $delivery = $this->delivery($this->activeConfig(), 'CONTENT.example.org');
        $this->assertTrue($delivery->exposedOnContentHost());
    }

    public function testNotOnContentHostWhenRequestHitsIliasHost(): void
    {
        $delivery = $this->delivery($this->activeConfig(), 'app.example.org');
        $this->assertFalse($delivery->exposedOnContentHost());
    }

    public function testNoIsolationHeadersWhenDisabled(): void
    {
        $delivery = $this->delivery(IsolationConfig::disabled());
        $r = $delivery->exposedIsolationHeaders(new Response());

        $this->assertFalse($r->hasHeader('X-Content-Type-Options'));
        $this->assertFalse($r->hasHeader('Cross-Origin-Resource-Policy'));
        $this->assertFalse($r->hasHeader('Referrer-Policy'));
        $this->assertFalse($r->hasHeader('Access-Control-Allow-Origin'));
    }

    public function testIsolationHeadersWhenActive(): void
    {
        $delivery = $this->delivery($this->activeConfig());
        $r = $delivery->exposedIsolationHeaders(new Response());

        $this->assertSame('nosniff', $r->getHeaderLine('X-Content-Type-Options'));
        $this->assertSame('cross-origin', $r->getHeaderLine('Cross-Origin-Resource-Policy'));
        $this->assertSame('no-referrer', $r->getHeaderLine('Referrer-Policy'));
        $this->assertSame('https://app.example.org', $r->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame('Origin', $r->getHeaderLine('Vary'));
    }

    public function testGeneralHeadersCarryIsolationHeadersWhenActive(): void
    {
        $delivery = $this->delivery($this->activeConfig());
        $r = $delivery->exposedGeneralHeaders(new Response());

        $this->assertSame('nosniff', $r->getHeaderLine('X-Content-Type-Options'));
        $this->assertSame('cross-origin', $r->getHeaderLine('Cross-Origin-Resource-Policy'));
        $this->assertSame('no-referrer', $r->getHeaderLine('Referrer-Policy'));
        $this->assertSame('https://app.example.org', $r->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame('Origin', $r->getHeaderLine('Vary'));
    }

    public function testGeneralHeadersKeepTheirRegularContentHeaders(): void
    {
        $delivery = $this->delivery($this->activeConfig());
        $r = $delivery->exposedGeneralHeaders(new Response());

        $this->assertSame('text/plain', $r->getHeaderLine('Content-Type'));
        $this->assertSame(
            'inline; filename="user_content.txt"',
            $r->getHeaderLine('Content-Disposition')
        );
    }

    public function testGeneralHeadersCarryNoIsolationHeadersWhenDisabled(): void
    {
        $delivery = $this->delivery(IsolationConfig::disabled());
        $r = $delivery->exposedGeneralHeaders(new Response());

        $this->assertSame('text/plain', $r->getHeaderLine('Content-Type'));
        $this->assertFalse($r->hasHeader('X-Content-Type-Options'));
        $this->assertFalse($r->hasHeader('Cross-Origin-Resource-Policy'));
        $this->assertFalse($r->hasHeader('Referrer-Policy'));
        $this->assertFalse($r->hasHeader('Access-Control-Allow-Origin'));
    }
}
