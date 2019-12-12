<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager;
use ILIAS\BackgroundTasks\Implementation\TaskManager\MockObserver;
use ILIAS\BackgroundTasks\Implementation\TaskManager\NonPersistingObserver;
use ILIAS\BackgroundTasks\Implementation\TaskManager\SyncTaskManager;
use ILIAS\BackgroundTasks\Implementation\Tasks\DownloadInteger;
use ILIAS\BackgroundTasks\Implementation\Tasks\PlusJob;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\DI\Container;
use ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap;
use ILIAS\BackgroundTasks\Dependencies\DependencyMap\EmptyDependencyMap;
use ILIAS\BackgroundTasks\Dependencies\Injector;
use Mockery;
use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");
require_once("./Services/ActiveRecord/Connector/class.arConnector.php");
require_once("./Services/ActiveRecord/Connector/class.arConnectorMap.php");

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
class BasicPersistenceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @var  Bucket */
    protected $bucket;

    /** @var  BasicPersistence */
    protected $persistence;

    public function setUp()
    {
        $dic = new Container();
        $dic[Bucket::class] = function ($c) {
            return new BasicBucket();
        };

        $factory = new Injector($dic, new BaseDependencyMap());
        $this->persistence = BasicPersistence::instance();

        $bucket = new BasicBucket(Mockery::mock(Persistence::class));
        $bucket->setUserId(3);
        $bucket->setState(State::SCHEDULED);

        /** @var PlusJob $a */
        $a = $factory->createInstance(PlusJob::class);
        /** @var PlusJob $b */
        $b = $factory->createInstance(PlusJob::class);
        /** @var PlusJob $c */
        $c = $factory->createInstance(PlusJob::class);

        $a->setInput([1, 1]);
        $b->setInput([1, 1]);
        $c->setInput([$a, $b]);

        /** @var DownloadInteger $userInteraction */
        $userInteraction = $factory->createInstance(DownloadInteger::class);
        $userInteraction->setInput([$c]);

        $bucket->setTask($userInteraction);

        $this->bucket = $bucket;
    }

    /**
     *
     */
    public function testSave()
    {
        /** @var \arConnector $bucketConnector */
        $bucketConnector = Mockery::namedMock("bucketConnectorMock", \arConnector::class);
        /** @var \arConnector $valueConnector */
        $valueConnector = Mockery::namedMock("valueConnectorMock", \arConnector::class);
        /** @var \arConnector $taskConnector */
        $taskConnector = Mockery::namedMock("taskConnectorMock", \arConnector::class);
        /** @var \arConnector $valueToTaskConnector */
        $valueToTaskConnector = Mockery::namedMock("valueToTaskConnectorMock", \arConnector::class);

        \arConnectorMap::register(new BucketContainer(), $bucketConnector);
        \arConnectorMap::register(new ValueContainer(), $valueConnector);
        \arConnectorMap::register(new TaskContainer(), $taskConnector);
        \arConnectorMap::register(new ValueToTaskContainer(), $valueToTaskConnector);

        // Bucket is created.
        $bucketConnector->shouldReceive("nextID")->once()->andReturn(1);
        $bucketConnector->shouldReceive("create")->once();

        // Bucket is updated after tasks are added.
        $bucketConnector->shouldReceive("affectedRows")->once()->andReturn(1);
        $bucketConnector->shouldReceive("update")->once()->andReturn(true);

        //For all four tasks
        for ($i = 0; $i < 4; $i++) {
            // task is created
            $taskConnector->shouldReceive("affectedRows")->once()->andReturn(0);
            $taskConnector->shouldReceive("nextID")->once()->andReturn(1);
            $taskConnector->shouldReceive("create")->once();

            // task is updated after values are linked.
            $taskConnector->shouldReceive("affectedRows")->once()->andReturn(1);
            $taskConnector->shouldReceive("update")->once();
        }

        // Create seven values
        $valueConnector->shouldReceive("affectedRows")->times(7)->andReturn(0);
        $valueConnector->shouldReceive("nextID")->times(7)->andReturn(1);
        $valueConnector->shouldReceive("create")->times(7);

        // Connect the seven values to the
        $valueToTaskConnector->shouldReceive("affectedRows")->times(7)->andReturn(0);
        $valueToTaskConnector->shouldReceive("nextID")->times(7)->andReturn(1);
        $valueToTaskConnector->shouldReceive("create")->times(7);

        $this->persistence->setConnector($bucketConnector);
        $this->persistence->saveBucketAndItsTasks($this->bucket);
    }

    public function testCannotUpdateUnknownBucket()
    {
        // We have an unknown observer, we can't update it.
        $this->expectException(SerializationException::class);

        $this->persistence->updateBucket($this->bucket);
    }

    public function testUpdateObserver()
    {
        // We do the whole save part.
        $this->testSave();
        /** @var \arConnector $observerConnector */
        $observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);

        \arConnectorMap::register(new BucketContainer(), $observerConnector);

        // Observer is updated after tasks are added.
        $observerConnector->shouldReceive("read")->once()->andReturn(1);
        $observerConnector->shouldReceive("update")->once()->andReturn(true);

        $this->persistence->setConnector($observerConnector);
        $this->persistence->updateBucket($this->bucket);
    }

    public function testGetObserverIdsOfUser()
    {
        /** @var \arConnector $observerConnector */
        $observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);

        \arConnectorMap::register(new BucketContainer(), $observerConnector);
        $observerConnector->shouldReceive("readSet")->once()->andReturn([["id" => 2], ["id" => 3]]);

        $this->persistence->setConnector($observerConnector);
        $observer_ids = $this->persistence->getBucketIdsOfUser(5);
        $this->assertEquals($observer_ids, [0 => 2, 1 => 3]);
    }

    public function testGetObserverIdsByState()
    {
        /** @var \arConnector $observerConnector */
        $observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);

        \arConnectorMap::register(new BucketContainer(), $observerConnector);
        $observerConnector->shouldReceive("readSet")->once()->andReturn([["id" => 2], ["id" => 3]]);

        $this->persistence->setConnector($observerConnector);
        $observer_ids = $this->persistence->getBucketIdsByState(State::RUNNING);
        $this->assertEquals($observer_ids, [2 => 2, 3 => 3]);
    }


    public function testUserInteraction()
    {
        $this->setExpectedException(UserInteractionRequiredException::class);
        /** @var IntegerValue $finalValue */
        $taskManager = new SyncTaskManager(Mockery::mock(Persistence::class));
        /** @var IntegerValue $finalValue */
        $taskManager->executeTask($this->bucket->getTask(), new MockObserver());
    }

    public function testContinueUserInteraction()
    {
        /** @var IntegerValue $finalValue */
        $taskManager = new SyncTaskManager(Mockery::mock(Persistence::class));
        try {
            /** @var IntegerValue $finalValue */
            $taskManager->executeTask($this->bucket->getTask(), new NonPersistingObserver($this->bucket));
        } catch (UserInteractionRequiredException $e) {
        }

        $download_integer = new DownloadInteger();

        // We worked on the task up to the user interaction. The current task should be the download integer interaction.
        self::assertEquals($this->bucket->getCurrentTask()->getType(), $download_integer->getType());

        $options = $download_integer->getOptions([]); // Download, Dismiss (the input doesnt matter so we pass an empty array)

        $dismiss = $download_integer->getRemoveOption();

        $this->bucket->userInteraction($dismiss); // We "click" Dismiss.

        // As we dismissed the last user interaction the state is finished.
        self::assertEquals($this->bucket->getState(), State::FINISHED);
    }

    public function testContinueUserInteraction2()
    {
        $dic = new Container();

        $factory = new Injector($dic, new EmptyDependencyMap());

        $c = $this->bucket->getTask();
        /** @var PlusJob $x */
        $x = $factory->createInstance(PlusJob::class);

        $x->setInput([$c, 1]);

        // we now have (1 + 1) + (1 + 1) -> User Interaction x -> (x + 1) Where x will be the input of the user interaction so: 4.
        $this->bucket->setTask($x);

        /** @var IntegerValue $finalValue */
        $taskManager = new SyncTaskManager(Mockery::mock(Persistence::class));
        try {
            /** @var IntegerValue $finalValue */
            $taskManager->executeTask($this->bucket->getTask(), new NonPersistingObserver($this->bucket));
        } catch (UserInteractionRequiredException $e) {
        }

        $download_integer = new DownloadInteger();

        // We worked on the task up to the user interaction. The current task should be the download integer interaction.
        self::assertEquals($this->bucket->getCurrentTask()->getType(), $download_integer->getType());

        $options = $download_integer->getOptions([]); // Download, Dismiss (the input doesn't matter so we pass an empty array)

        $dismiss = $download_integer->getRemoveOption();

        $this->bucket->userInteraction($dismiss); // We "click" Dismiss.

        // As we dismissed the last user interaction the state is finished.
        /** @var IntegerValue $result */
        $result = $taskManager->executeTask($this->bucket->getTask(), new NonPersistingObserver($this->bucket));
        self::assertEquals(5, $result->getValue());
    }
}
