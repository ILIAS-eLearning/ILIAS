<?php

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\BucketMock;
use ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager;
use ILIAS\BackgroundTasks\Implementation\TaskManager\MockObserver;
use ILIAS\BackgroundTasks\Implementation\Tasks\Aggregation\ConcatenationJob;
use ILIAS\BackgroundTasks\Implementation\Tasks\PlusJob;
use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\DI\Container;
use ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap;
use ILIAS\BackgroundTasks\Dependencies\Injector;
use ILIAS\BackgroundTasks\Types\SingleType;
use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");

/**
 * Class BackgroundTaskTest
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class TaskTest extends TestCase
{
    public function testPlusTask()
    {
        $dic = new Container();

        $factory = new Injector($dic, new BaseDependencyMap());

        $a = new IntegerValue();
        $a->setValue(1);
        $b = new IntegerValue();
        $b->setValue(2);
        $c = new IntegerValue();
        $c->setValue(3);

        /** @var PlusJob $t1 */
        $t1 = $factory->createInstance(PlusJob::class);
        $t1->setInput([$a, $b]);

        /** @var PlusJob $t2 */
        $t2 = $factory->createInstance(PlusJob::class);
        $t2->setInput([$t1, $c]);

        $this->assertTrue($t2->getOutputType()->equals(new SingleType(IntegerValue::class)));

        $taskManager = new \ILIAS\BackgroundTasks\Implementation\TaskManager\SyncTaskManager(Mockery::mock(Persistence::class));
        /** @var IntegerValue $finalValue */
        $finalValue = $taskManager->executeTask($t2, new MockObserver());
        $this->assertEquals($finalValue->getValue(), 6);
    }

    public function testValueWrapper()
    {
        $dic = new Container();
        $dic[Bucket::class] = function ($c) {
            return new BucketMock();
        };
        $factory = new Injector($dic, new BaseDependencyMap());

        $t = $factory->createInstance(PlusJob::class);
        $t->setInput([1, 4]);

        $taskManager = new \ILIAS\BackgroundTasks\Implementation\TaskManager\SyncTaskManager(Mockery::mock(Persistence::class));
        /** @var IntegerValue $finalValue */
        $finalValue = $taskManager->executeTask($t, new MockObserver());
        $this->assertEquals($finalValue->getValue(), 5);
    }

    public function testTypeCheck()
    {
        $this->expectException(InvalidArgumentException::class);

        $dic = new Container();
        $dic[Bucket::class] = function ($c) {
            return new BucketMock();
        };
        $factory = new Injector($dic, new BaseDependencyMap());

        $a = new IntegerValue(1);
        $b = new StringValue("hello");

        /** @var PlusJob $t1 */
        $t1 = $factory->createInstance(PlusJob::class);
        $t1->setInput([$a, $b]);
    }

    public function testAggregation()
    {
        $dic = new Container();
        $factory = new Injector($dic, new BaseDependencyMap());

        $list = new ListValue();
        $list->setValue([1, "hello", 3.0]);

        /** @var ConcatenationJob $t1 */
        $t1 = $factory->createInstance(ConcatenationJob::class);
        $t1->setInput([$list]);

        $output = $t1->run([$list], new MockObserver());
        $this->assertEquals($output->getValue(), "1, hello, 3");
    }

    public function testUnfoldTask()
    {
        $dic = new Container();
        $dic[Bucket::class] = function ($c) {
            return new BasicBucket();
        };

        $factory = new Injector($dic, new BaseDependencyMap());

        /**
         * @var PlusJob $t0
         */
        $t0 = $factory->createInstance(PlusJob::class);
        $t0->setInput([1, 1]);

        /** @var PlusJob $t1 */
        $t1 = $factory->createInstance(PlusJob::class);
        $t1->setInput([$t0, 2]);

        /** @var PlusJob $t25 */
        $t25 = $factory->createInstance(PlusJob::class);
        $t25->setInput([2, 2]);

        /** @var PlusJob $t2 */
        $t2 = $factory->createInstance(PlusJob::class);
        $t2->setInput([$t1, $t25]);

        $this->assertTrue($t2->getOutputType()->equals(new SingleType(IntegerValue::class)));

        $list = $t2->unfoldTask();
        $this->assertEquals($list, [$t2, $t1, $t0, $t25]);

        /** @var IntegerValue $finalValue */
        $taskManager = new \ILIAS\BackgroundTasks\Implementation\TaskManager\SyncTaskManager(Mockery::mock(Persistence::class));
        /** @var IntegerValue $finalValue */
        $finalValue = $taskManager->executeTask($t2, new MockObserver());
        $this->assertEquals($finalValue->getValue(), 8);
    }
}
