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
use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Temporary files are removed by a shutdown function as soon as the request exits, header
 * based delivery (X-Sendfile, X-Accel) opens them only afterwards. They must be streamed by
 * PHP, see https://mantis.ilias.de/view.php?id=48367
 */
class StreamDeliveryTest extends TestCase
{
    private ?string $temp_file = null;

    protected function tearDown(): void
    {
        if ($this->temp_file !== null && is_file($this->temp_file)) {
            unlink($this->temp_file);
        }
    }

    public function testTemporaryFileIsDeliveredByTheFallback(): void
    {
        $this->temp_file = tempnam(sys_get_temp_dir(), 'zip');
        file_put_contents($this->temp_file, 'zip');

        $this->assertDeliveredBy(
            'fallback',
            Streams::ofResource(fopen($this->temp_file, 'rb'))
        );
    }

    public function testRegularFileIsDeliveredByTheConfiguredBuilder(): void
    {
        $this->assertDeliveredBy(
            'configured',
            Streams::ofResource(fopen(__FILE__, 'rb'))
        );
    }

    public function testMemoryStreamIsDeliveredByTheFallback(): void
    {
        $this->assertDeliveredBy(
            'fallback',
            Streams::ofString('content')
        );
    }

    private function assertDeliveredBy(string $expected, FileStream $stream): void
    {
        $response = new Response();

        $http = $this->createStub(\ILIAS\HTTP\Services::class);
        $http->method('request')->willReturn(new ServerRequest('GET', 'https://ilias.example.org/'));
        $http->method('response')->willReturn($response);
        // close() is never, the test leaves deliver() by an exception instead
        $http->method('close')->willThrowException(new \RuntimeException('closed'));

        $configured = $this->builder('configured', $expected === 'configured', $response);
        $fallback = $this->builder('fallback', $expected === 'fallback', $response);

        $delivery = new StreamDelivery(
            new DataSigner(new SecretKeyRotation(new SecretKey(str_repeat('a', 32)))),
            $http,
            $configured,
            $fallback
        );

        $this->expectExceptionMessage('closed');
        $delivery->attached($stream, 'file.zip', 'application/zip');
    }

    private function builder(string $name, bool $used, Response $response): ResponseBuilder&MockObject
    {
        $builder = $this->createMock(ResponseBuilder::class);
        $builder->method('getName')->willReturn($name);
        $builder->expects($used ? $this->once() : $this->never())
                ->method('buildForStream')
                ->willReturn($response);

        return $builder;
    }
}
