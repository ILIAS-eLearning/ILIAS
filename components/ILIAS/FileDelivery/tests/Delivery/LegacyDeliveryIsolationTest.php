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
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\HTTP\Services as HttpServices;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Covers the host guard in {@see LegacyDelivery}: the content domain is
 * reserved for signed token delivery, so legacy delivery must answer 404
 * there instead of serving the file.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class LegacyDeliveryIsolationTest extends TestCase
{
    private string $file;
    private ?ResponseInterface $saved = null;

    protected function setUp(): void
    {
        $this->file = (string) tempnam(sys_get_temp_dir(), 'fd_iso_');
        file_put_contents($this->file, 'legacy content');
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    private function http(string $request_host): HttpServices
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getHost')->willReturn($request_host);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $http = $this->createStub(HttpServices::class);
        $http->method('request')->willReturn($request);
        $http->method('response')->willReturn(new Response());
        $http->method('saveResponse')->willReturnCallback(
            function (ResponseInterface $r): void {
                $this->saved = $r;
            }
        );
        // deliveries are declared `never`; the doubled close() ends them observably
        $http->method('close')->willThrowException(new DeliveryTerminated());

        return $http;
    }

    private function activeConfig(): IsolationConfig
    {
        return IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);
    }

    private function deliverInline(
        IsolationConfig $isolation,
        string $request_host,
        ResponseBuilder $response_builder
    ): void {
        $delivery = new LegacyDelivery(
            $this->http($request_host),
            $response_builder,
            $response_builder,
            $isolation
        );

        try {
            $delivery->inline($this->file);
            $this->fail('delivery did not terminate');
        } catch (DeliveryTerminated) {
            // expected: the delivery ran to its end and closed the request
        }
    }

    private function neverBuilding(): ResponseBuilder
    {
        $rb = $this->createMock(ResponseBuilder::class);
        $rb->expects($this->never())->method('buildForStream');
        return $rb;
    }

    private function building(): ResponseBuilder
    {
        $rb = $this->createMock(ResponseBuilder::class);
        $rb->method('getName')->willReturn('PHP');
        $rb->expects($this->once())
           ->method('buildForStream')
           ->willReturnCallback(
               static fn(ServerRequestInterface $request, ResponseInterface $r): ResponseInterface => $r
           );
        return $rb;
    }

    public function testLegacyDeliveryIsRefusedOnTheContentHost(): void
    {
        $this->deliverInline($this->activeConfig(), 'content.example.org', $this->neverBuilding());

        $this->assertNotNull($this->saved);
        $this->assertSame(404, $this->saved->getStatusCode());
    }

    public function testLegacyDeliveryIsRefusedOnTheContentHostRegardlessOfCase(): void
    {
        $this->deliverInline($this->activeConfig(), 'CONTENT.Example.ORG', $this->neverBuilding());

        $this->assertNotNull($this->saved);
        $this->assertSame(404, $this->saved->getStatusCode());
    }

    public function testLegacyDeliveryIsServedOnTheIliasHost(): void
    {
        $this->deliverInline($this->activeConfig(), 'app.example.org', $this->building());

        $this->assertNotNull($this->saved);
        $this->assertSame(200, $this->saved->getStatusCode());
        // the delivered response carries the isolation headers
        $this->assertSame('nosniff', $this->saved->getHeaderLine('X-Content-Type-Options'));
        $this->assertSame(
            'https://app.example.org',
            $this->saved->getHeaderLine('Access-Control-Allow-Origin')
        );
    }

    public function testLegacyDeliveryIsServedOnAnyHostWhenIsolationIsDisabled(): void
    {
        $this->deliverInline(IsolationConfig::disabled(), 'content.example.org', $this->building());

        $this->assertNotNull($this->saved);
        $this->assertSame(200, $this->saved->getStatusCode());
        $this->assertFalse($this->saved->hasHeader('X-Content-Type-Options'));
        $this->assertFalse($this->saved->hasHeader('Access-Control-Allow-Origin'));
    }
}
