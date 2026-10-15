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

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\BucketNotFoundException;
use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\TaskManager\AsyncTaskManager;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\Job;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\DI\Container;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AsyncTaskManagerTest extends TestCase
{
    private Container $dic;
    private MockObject&Persistence $persistence;
    private MockObject&ilLogger $logger;
    private int $scheduled_queries = 0;

    protected function setUp(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->logger = $this->createMock(ilLogger::class);
        $logger_factory = $this->createMock(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->logger);
        $this->dic['ilLoggerFactory'] = $logger_factory;

        $this->persistence = $this->createMock(Persistence::class);
        $this->dic['bt.persistence'] = $this->persistence;

        $ini = $this->createMock(ilIniFile::class);
        $ini->method('readVariable')->willReturn('5');
        $GLOBALS['ilIliasIniFile'] = $ini;
    }

    /**
     * @param list<int> ...$responses
     */
    private function schedule(array ...$responses): void
    {
        $this->scheduled_queries = 0;
        $index = 0;
        $this->persistence->method('getBucketIdsByState')->willReturnCallback(
            function (int $state) use ($responses, &$index): array {
                if ($state === State::RUNNING) {
                    return [];
                }
                $this->scheduled_queries++;
                $response = $responses[$index] ?? [];
                $index++;

                return $response;
            }
        );
    }

    private function runnableBucket(): MockObject&Bucket
    {
        $output_type = $this->createMock(Type::class);
        $value_type = $this->createMock(Type::class);
        $value_type->method('isExtensionOf')->willReturn(true);

        $value = $this->createMock(Value::class);
        $value->method('getType')->willReturn($value_type);

        $job = $this->createMock(Job::class);
        $job->method('getInput')->willReturn([]);
        $job->method('getOutputType')->willReturn($output_type);
        $job->method('run')->willReturn($value);

        $bucket = $this->createMock(Bucket::class);
        $bucket->method('getTask')->willReturn($job);

        return $bucket;
    }

    private function manager(): AsyncTaskManager
    {
        return new AsyncTaskManager($this->persistence);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function corruptContainerProvider(): array
    {
        return [
            'missing task container' => ['The requested task with container id 5 could not be found in the database.'],
            'missing value container' => ['The requested value with container id 9 could not be found in the database.'],
        ];
    }

    #[DataProvider('corruptContainerProvider')]
    public function testCorruptContainerDeletesBucketAndRunsNext(string $message): void
    {
        $this->schedule([1, 2], []);
        $bucket = $this->runnableBucket();
        $finished = false;
        $bucket->method('setState')->willReturnCallback(function (int $state) use (&$finished): void {
            if ($state === State::FINISHED) {
                $finished = true;
            }
        });

        $this->persistence->method('loadBucket')->willReturnCallback(
            function (int $id) use ($bucket, $message): Bucket {
                if ($id === 1) {
                    throw new SerializationException($message);
                }

                return $bucket;
            }
        );
        $this->persistence->expects($this->once())->method('deleteBucketById')->with(1);
        $this->persistence->expects($this->atLeastOnce())->method('updateBucket')->with($bucket);

        $this->manager()->runAsync();

        $this->assertTrue($finished);
        $this->assertSame(2, $this->scheduled_queries);
    }

    public function testDatabaseErrorStopsWorkerWithoutDeleting(): void
    {
        $this->schedule([1, 2], []);
        $loaded = [];
        $this->persistence->method('loadBucket')->willReturnCallback(
            function (int $id) use (&$loaded): Bucket {
                $loaded[] = $id;
                throw new ilDatabaseException('database unavailable');
            }
        );
        $this->persistence->expects($this->never())->method('deleteBucketById');
        $this->logger->expects($this->once())->method('error');

        $this->manager()->runAsync();

        $this->assertSame([1], $loaded);
    }

    public function testMissingBucketIsSkippedWithoutDeleteAndNextRuns(): void
    {
        $this->schedule([1, 2], []);
        $bucket = $this->runnableBucket();
        $loaded = [];
        $this->persistence->method('loadBucket')->willReturnCallback(
            function (int $id) use ($bucket, &$loaded): Bucket {
                $loaded[] = $id;
                if ($id === 1) {
                    throw new BucketNotFoundException('bucket already gone');
                }

                return $bucket;
            }
        );
        $this->persistence->expects($this->never())->method('deleteBucketById');
        $this->persistence->expects($this->atLeastOnce())->method('updateBucket')->with($bucket);

        $this->manager()->runAsync();

        $this->assertSame([1, 2], $loaded);
    }

    public function testCorruptBucketsDoNotReloadTheScheduledList(): void
    {
        $this->schedule([1, 2, 3], []);
        $bucket = $this->runnableBucket();
        $loaded = [];
        $deleted = [];
        $this->persistence->method('loadBucket')->willReturnCallback(
            function (int $id) use ($bucket, &$loaded): Bucket {
                $loaded[] = $id;
                if ($id === 1 || $id === 2) {
                    throw new SerializationException('corrupt container');
                }

                return $bucket;
            }
        );
        $this->persistence->method('deleteBucketById')->willReturnCallback(
            function (int $id) use (&$deleted): void {
                $deleted[] = $id;
            }
        );

        $this->manager()->runAsync();

        $this->assertSame([1, 2, 3], $loaded);
        $this->assertSame([1, 2], $deleted);
        $this->assertSame(2, $this->scheduled_queries);
    }
}
