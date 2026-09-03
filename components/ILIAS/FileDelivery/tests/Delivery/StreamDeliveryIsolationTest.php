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
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Algorithm\SHA1;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as HttpServices;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Covers the host guard in {@see StreamDelivery::deliverFromToken()}: with a
 * *valid* token, delivery must still be refused with 404 unless the request
 * reached the configured content domain.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class StreamDeliveryIsolationTest extends TestCase
{
    private string $file;
    private DataSigner $data_signer;
    private ?ResponseInterface $saved = null;

    protected function setUp(): void
    {
        $this->file = (string) tempnam(sys_get_temp_dir(), 'fd_iso_');
        file_put_contents($this->file, 'user content');
        $this->data_signer = new DataSigner(
            new SecretKeyRotation(new SecretKey('test_key')),
            new SHA1()
        );
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    private function validToken(): string
    {
        return $this->data_signer->getSignedStreamToken(
            Streams::ofResource(fopen($this->file, 'rb')),
            basename($this->file),
            Disposition::INLINE,
            0
        );
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

    private function deliverFromToken(
        IsolationConfig $isolation,
        string $request_host,
        ResponseBuilder $response_builder
    ): void {
        $delivery = new StreamDelivery(
            $this->data_signer,
            $this->http($request_host),
            $response_builder,
            $response_builder,
            $isolation
        );

        try {
            $delivery->deliverFromToken($this->validToken());
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

    public function testValidTokenIsRefusedOnTheIliasHostWhenIsolationIsActive(): void
    {
        $this->deliverFromToken($this->activeConfig(), 'app.example.org', $this->neverBuilding());

        $this->assertNotNull($this->saved);
        $this->assertSame(404, $this->saved->getStatusCode());
    }

    public function testValidTokenIsRefusedOnAnyForeignHostWhenIsolationIsActive(): void
    {
        $this->deliverFromToken($this->activeConfig(), 'evil.example.org', $this->neverBuilding());

        $this->assertNotNull($this->saved);
        $this->assertSame(404, $this->saved->getStatusCode());
    }

    public function testValidTokenIsServedOnTheContentHost(): void
    {
        $this->deliverFromToken($this->activeConfig(), 'content.example.org', $this->building());

        $this->assertNotNull($this->saved);
        $this->assertSame(200, $this->saved->getStatusCode());
        // the delivered response carries the isolation headers
        $this->assertSame('nosniff', $this->saved->getHeaderLine('X-Content-Type-Options'));
        $this->assertSame('cross-origin', $this->saved->getHeaderLine('Cross-Origin-Resource-Policy'));
        $this->assertSame('no-referrer', $this->saved->getHeaderLine('Referrer-Policy'));
        $this->assertSame(
            'https://app.example.org',
            $this->saved->getHeaderLine('Access-Control-Allow-Origin')
        );
    }

    public function testValidTokenIsServedOnAnyHostWhenIsolationIsDisabled(): void
    {
        $this->deliverFromToken(IsolationConfig::disabled(), 'app.example.org', $this->building());

        $this->assertNotNull($this->saved);
        $this->assertSame(200, $this->saved->getStatusCode());
        $this->assertFalse($this->saved->hasHeader('X-Content-Type-Options'));
        $this->assertFalse($this->saved->hasHeader('Access-Control-Allow-Origin'));
    }
}
