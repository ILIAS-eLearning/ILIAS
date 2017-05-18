<?php
/**
 * Created by PhpStorm.
 * User: otruffer
 * Date: 05.05.17
 * Time: 13:29
 */

namespace BackgroundTasks\Implementation;

use ILIAS\BackgroundTasks\Implementation\BasicTaskManager;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Persistence\BasicPersistence;
use ILIAS\BackgroundTasks\Implementation\Tasks\DownloadInteger;
use ILIAS\BackgroundTasks\Implementation\Tasks\PlusJob;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\DI\Container;
use ILIAS\DI\DependencyMap\EmptyDependencyMap;
use ILIAS\DI\Injector;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class BasicTaskManagerTest extends \PHPUnit_Framework_TestCase {

	use MockeryPHPUnitIntegration;

	protected $taskManager;
	protected $observer;

	public function setUp() {
		$persistence = \Mockery::mock(BasicPersistence::class);
		$this->taskManager = new BasicTaskManager($persistence);
	}

	public function testBasicTaskManager() {
		$dic = new Container();

		$factory = new Injector($dic, new EmptyDependencyMap());

		$observer = new BasicBucket();
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


}
