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

use ILIAS\Init\ErrorHandling\Application\ErrorLogDirectory;
use ILIAS\Init\ErrorHandling\Application\ErrorLogFileStorage;
use ILIAS\Init\ErrorHandling\Application\ReportErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentFactory;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentId;
use ILIAS\Init\ErrorHandling\Incident\InMemoryErrorIncidentRegistry;
use ILIAS\Init\ErrorHandling\Incident\SessionPrefixedErrorIncidentFactory;
use ILIAS\Init\ErrorHandling\Infrastructure\Logging\FileWriter;
use ILIAS\Init\ErrorHandling\Logging\FileHandler;
use ILIAS\Init\ErrorHandling\Infrastructure\Logging\ContentProcessor;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

class ReportErrorIncidentTest extends TestCase
{
    public function testDoesNothingWhenLogDirectoryIsEmpty(): void
    {
        $storage = $this->createMock(ErrorLogFileStorage::class);
        $storage->expects($this->never())->method('write');

        $registry = new InMemoryErrorIncidentRegistry();
        $report = new ReportErrorIncident(
            new readonly class () implements ErrorLogDirectory {
                public function path(): string
                {
                    return '';
                }
            },
            $storage,
            new SessionPrefixedErrorIncidentFactory(),
            $registry,
            ['password']
        );

        $result = $report->report(new Inspector(new RuntimeException('test')));

        self::assertNull($result);
        self::assertNull($registry->current());
    }

    public function testWritesLogFileAndRecordsIncident(): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfsStream::setup();
        vfsStream::create([
            'errors' => [],
        ]);

        $log_directory = vfsStream::url('root/errors');
        $log_file = vfsStream::url('root/errors/abcde_42.log');
        $inspector = new Inspector(new RuntimeException('test'));
        $incident = new ErrorIncident(new ErrorIncidentId('abcde_42'));

        $incident_factory = $this->createMock(ErrorIncidentFactory::class);
        $incident_factory->expects($this->once())
            ->method('create')
            ->willReturn($incident);

        $registry = new InMemoryErrorIncidentRegistry();
        $report = new ReportErrorIncident(
            new readonly class ($log_directory) implements ErrorLogDirectory {
                public function __construct(
                    private string $path
                ) {
                }

                public function path(): string
                {
                    return $this->path;
                }
            },
            new FileWriter(new FileHandler(), new ContentProcessor()),
            $incident_factory,
            $registry,
            ['password']
        );

        $result = $report->report($inspector);

        self::assertSame($incident, $result);
        self::assertSame($incident, $registry->current());
        self::assertFileExists($log_file);
        self::assertStringContainsString('test', (string) file_get_contents($log_file));
    }

    private function skipIfVfsStreamNotAvailable(): void
    {
        if (!class_exists(vfsStreamWrapper::class)) {
            self::markTestSkipped(
                'vfsStream (https://github.com/bovigo/vfsStream) is required for virtual filesystem tests.'
            );
        }
    }
}
