<?php

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\BasicTaskManager;
use ILIAS\BackgroundTasks\Implementation\Observer\ObserverMock;
use ILIAS\BackgroundTasks\Implementation\Tasks\Aggregation\ConcatenationJob;
use ILIAS\BackgroundTasks\Implementation\Tasks\PlusJob;
use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\SingleType;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\DI\Container;
use ILIAS\DI\Factory;
use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");

/**
 * Class BackgroundTaskTest
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class TaskTest extends TestCase {

	public function testPlusTask() {
		$dic = new Container();
		$dic[Observer::class] = function ($c) {
			return new ObserverMock();
		};

		$factory = new Factory($dic);

		$a = new IntegerValue(1);
		$b = new IntegerValue(2);
		$c = new IntegerValue(3);

		/** @var PlusJob $t1 */
		$t1 = $factory->createInstance(PlusJob::class);
		$t1->setInput([$a, $b]);

		/** @var PlusJob $t2 */
		$t2 = $factory->createInstance(PlusJob::class);
		$t2->setInput([$t1, $c]);

		$this->assertTrue($t2->getOutputType()->equals(new SingleType(IntegerValue::class)));

		$taskManager = new BasicTaskManager();
		/** @var IntegerValue $finalValue */
		$finalValue = $taskManager->executeTask($t2, new ObserverMock());
		$this->assertEquals($finalValue->getValue(), 6);
	}

	public function testValueWrapper() {
		$dic = new Container();
		$dic[Observer::class] = function ($c) {
			return new ObserverMock();
		};
		$factory = new Factory($dic);

		$t = $factory->createInstance(PlusJob::class);
		$t->setInput([1, 4]);

		$taskManager = new BasicTaskManager();
		/** @var IntegerValue $finalValue */
		$finalValue = $taskManager->executeTask($t, new ObserverMock());
		$this->assertEquals($finalValue->getValue(), 5);
	}

	public function testTypeCheck() {
		$this->expectException(InvalidArgumentException::class);

		$dic = new Container();
		$dic[Observer::class] = function ($c) {
			return new ObserverMock();
		};
		$factory = new Factory($dic);

		$a = new IntegerValue(1);
		$b = new StringValue("hello");

		/** @var PlusJob $t1 */
		$t1 = $factory->createInstance(PlusJob::class);
		$t1->setInput([$a, $b]);
	}

	public function testAggregation() {
		$dic = new Container();
		$factory = new Factory($dic);

		$list = new ListValue([1, "hello", 3.0]);

		/** @var ConcatenationJob $t1 */
		$t1 = $factory->createInstance(ConcatenationJob::class);
		$t1->setInput([$list]);

		$output = $t1->run([$list], new ILIAS\BackgroundTasks\Implementation\Observer\ObserverMock());
		$this->assertEquals($output->getValue(), "1, hello, 3");
	}
}