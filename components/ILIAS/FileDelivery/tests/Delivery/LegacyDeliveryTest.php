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
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Header based delivery (X-Sendfile, X-Accel) opens the file only after PHP has finished. A file
 * which is deleted after the delivery must be streamed by PHP, see
 * https://mantis.ilias.de/view.php?id=48312
 */
class LegacyDeliveryTest extends TestCase
{
    private ?string $file = null;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'legacy');
        file_put_contents($this->file, 'content');
    }

    protected function tearDown(): void
    {
        if ($this->file !== null && is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testFileToDeleteIsDeliveredByTheFallback(): void
    {
        $this->assertDeliveredBy('fallback', false, true);
    }

    public function testFileToDeleteIsDeliveredByABuilderSupportingDeletion(): void
    {
        $this->assertDeliveredBy('configured', true, true);
    }

    public function testFileToKeepIsDeliveredByTheConfiguredBuilder(): void
    {
        $this->assertDeliveredBy('configured', false, false);
    }

    private function assertDeliveredBy(string $expected, bool $supports_deletion, bool $delete_file): void
    {
        $response = new Response();

        $http = $this->createStub(\ILIAS\HTTP\Services::class);
        $http->method('request')->willReturn(new ServerRequest('GET', 'https://ilias.example.org/'));
        $http->method('response')->willReturn($response);
        // close() is never, the test leaves deliver() by an exception instead
        $http->method('close')->willThrowException(new \RuntimeException('closed'));

        $configured = $this->builder('configured', $expected === 'configured', $response, $supports_deletion);
        $fallback = $this->builder('fallback', $expected === 'fallback', $response, true);

        $delivery = new LegacyDelivery($http, $configured, $fallback);

        $this->expectExceptionMessage('closed');
        $delivery->attached($this->file, 'file.zip', 'application/zip', $delete_file);
    }

    private function builder(
        string $name,
        bool $used,
        Response $response,
        bool $supports_deletion
    ): ResponseBuilder&MockObject {
        $builder = $this->createMock(ResponseBuilder::class);
        $builder->method('getName')->willReturn($name);
        $builder->method('supportFileDeletion')->willReturn($supports_deletion);
        $builder->expects($used ? $this->once() : $this->never())
                ->method('buildForStream')
                ->willReturn($response);

        return $builder;
    }
}
