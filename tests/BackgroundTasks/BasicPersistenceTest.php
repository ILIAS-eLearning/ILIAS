<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Implementation\BasicTaskManager;
use ILIAS\BackgroundTasks\Implementation\Observer\BasicObserver;
use ILIAS\BackgroundTasks\Implementation\Observer\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\DownloadInteger;
use ILIAS\BackgroundTasks\Implementation\Tasks\PlusJob;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\DI\Container;
use ILIAS\DI\DependencyMap\BaseDependencyMap;
use ILIAS\DI\DependencyMap\EmptyDependencyMap;
use ILIAS\DI\Injector;
use Mockery;

require_once("libs/composer/vendor/autoload.php");
require_once("./Services/ActiveRecord/Connector/class.arConnector.php");
require_once("./Services/ActiveRecord/Connector/class.arConnectorMap.php");

/**
 * Class BackgroundTaskTest
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class BasicPersistenceTest extends \PHPUnit_Framework_TestCase {

	use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/** @var  Observer */
	protected $observer;

	/** @var  BasicPersistence */
	protected $persistence;

	public function setUp() {
		$dic = new Container();
		$dic[Observer::class] = function ($c) {
			return new BasicObserver();
		};

		$factory = new Injector($dic, new BaseDependencyMap());
		$this->persistence = BasicPersistence::instance();

		$observer = new BasicObserver(Mockery::mock(Persistence::class));
		$observer->setUserId(3);
		$observer->setState(State::SCHEDULED);

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

		$observer->setTask($userInteraction);

		$this->observer = $observer;
	}

	/**
	 *
	 */
	public function testSave() {
		/** @var \arConnector $observerConnector */
		$observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);
		/** @var \arConnector $valueConnector */
		$valueConnector = Mockery::namedMock("valueConnectorMock", \arConnector::class);
		/** @var \arConnector $taskConnector */
		$taskConnector = Mockery::namedMock("taskConnectorMock", \arConnector::class);
		/** @var \arConnector $valueToTaskConnector */
		$valueToTaskConnector = Mockery::namedMock("valueToTaskConnectorMock", \arConnector::class);

		\arConnectorMap::register(new ObserverContainer(), $observerConnector);
		\arConnectorMap::register(new ValueContainer(), $valueConnector);
		\arConnectorMap::register(new TaskContainer(), $taskConnector);
		\arConnectorMap::register(new ValueToTaskContainer(), $valueToTaskConnector);

		// Observer is created.
		$observerConnector->shouldReceive("nextID")->once()->andReturn(1);
		$observerConnector->shouldReceive("create")->once();

		// Observer is updated after tasks are added.
		$observerConnector->shouldReceive("affectedRows")->once()->andReturn(1);
		$observerConnector->shouldReceive("update")->once()->andReturn(true);

		//For all four tasks
		for($i = 0; $i < 4; $i++) {
			// task is created
			$taskConnector->shouldReceive("affectedRows")->once()->andReturn(0);
			$taskConnector->shouldReceive("nextID")->once()->andReturn(1);
			$taskConnector->shouldReceive("create")->once();

			// task is updated after values are linked.
			$taskConnector->shouldReceive("affectedRows")->once()->andReturn(1);
			$taskConnector->shouldReceive("update")->once();
		}

		// Create seven values
		$valueConnector->shouldReceive("affectedRows")->time(7)->andReturn(0);
		$valueConnector->shouldReceive("nextID")->time(7)->andReturn(1);
		$valueConnector->shouldReceive("create")->time(7);

		// Connect the seven values to the
		$valueToTaskConnector->shouldReceive("affectedRows")->time(7)->andReturn(0);
		$valueToTaskConnector->shouldReceive("nextID")->time(7)->andReturn(1);
		$valueToTaskConnector->shouldReceive("create")->time(7);

		$this->persistence->setConnector($observerConnector);
		$this->persistence->saveObserverAndItsTasks($this->observer);
	}

	public function testCannotUpdateUnknownObserver() {
		// We have an unknown observer, we can't update it.
		$this->setExpectedException(SerializationException::class);

		$this->persistence->updateObserver($this->observer);
	}

	public function testUpdateObserver() {
		// We do the whole save part.
		$this->testSave();
		/** @var \arConnector $observerConnector */
		$observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);

		\arConnectorMap::register(new ObserverContainer(), $observerConnector);

		// Observer is updated after tasks are added.
		$observerConnector->shouldReceive("read")->once()->andReturn(1);
		$observerConnector->shouldReceive("update")->once()->andReturn(true);

		$this->persistence->setConnector($observerConnector);
		$this->persistence->updateObserver($this->observer);
	}

	public function testGetObserverIdsOfUser() {
		/** @var \arConnector $observerConnector */
		$observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);

		\arConnectorMap::register(new ObserverContainer(), $observerConnector);
		$observerConnector->shouldReceive("readSet")->once()->andReturn([["id" => 2], ["id" => 3]]);

		$this->persistence->setConnector($observerConnector);
		$observer_ids = $this->persistence->getObserverIdsOfUser(5);
		$this->assertEquals($observer_ids, [2 => 2, 3 => 3]);
	}

	public function testGetObserverIdsByState() {
		/** @var \arConnector $observerConnector */
		$observerConnector = Mockery::namedMock("observerConnectorMock", \arConnector::class);

		\arConnectorMap::register(new ObserverContainer(), $observerConnector);
		$observerConnector->shouldReceive("readSet")->once()->andReturn([["id" => 2], ["id" => 3]]);

		$this->persistence->setConnector($observerConnector);
		$observer_ids = $this->persistence->getObserverIdsByState(State::RUNNING);
		$this->assertEquals($observer_ids, [2 => 2, 3 => 3]);
	}


	public function testUserInteraction() {
		$this->setExpectedException(UserInteractionRequiredException::class);
		/** @var IntegerValue $finalValue */
		$taskManager = new BasicTaskManager(Mockery::mock(Persistence::class));
		/** @var IntegerValue $finalValue */
		$taskManager->executeTask($this->observer->getTask(), $this->observer);
	}

	public function testContinueUserInteraction() {
		/** @var IntegerValue $finalValue */
		$taskManager = new BasicTaskManager(Mockery::mock(Persistence::class));
		try {
			/** @var IntegerValue $finalValue */
			$taskManager->executeTask($this->observer->getTask(), $this->observer);
		} catch (UserInteractionRequiredException $e) {}

		$download_integer = new DownloadInteger();

		// We worked on the task up to the user interaction. The current task should be the download integer interaction.
		self::assertEquals($this->observer->getCurrentTask()->getType(), $download_integer->getType());

		$options = $download_integer->getOptions([]); // Download, Dismiss (the input doesnt matter so we pass an empty array)

		$this->observer->userInteraction($options[1]); // We "click" Dismiss.

		// As we dismissed the last user interaction the state is finished.
		self::assertEquals($this->observer->getState(), State::FINISHED);
	}

	public function testContinueUserInteraction2() {
		$dic = new Container();

		$factory = new Injector($dic, new EmptyDependencyMap());

		$c = $this->observer->getTask();
		/** @var PlusJob $x */
		$x = $factory->createInstance(PlusJob::class);

		$x->setInput([$c, 1]);

		// we now have (1 + 1) + (1 + 1) -> User Interaction x -> (x + 1) Where x will be the input of the user interaction so: 4.
		$this->observer->setTask($x);

		/** @var IntegerValue $finalValue */
		$taskManager = new BasicTaskManager(Mockery::mock(Persistence::class));
		try {
			/** @var IntegerValue $finalValue */
			$taskManager->executeTask($this->observer->getTask(), $this->observer);
		} catch (UserInteractionRequiredException $e) {}

		$download_integer = new DownloadInteger();

		// We worked on the task up to the user interaction. The current task should be the download integer interaction.
		self::assertEquals($this->observer->getCurrentTask()->getType(), $download_integer->getType());

		$options = $download_integer->getOptions([]); // Download, Dismiss (the input doesn't matter so we pass an empty array)

		$this->observer->userInteraction($options[1]); // We "click" Dismiss.

		// As we dismissed the last user interaction the state is finished.
		/** @var IntegerValue $result */
		$result = $taskManager->executeTask($this->observer->getTask(), $this->observer);
		self::assertEquals(5, $result->getValue());
	}

}
