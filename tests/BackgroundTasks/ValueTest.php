<?php

use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValue;
use ILIAS\BackgroundTasks\Types\ListType;
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
class ValueTest extends TestCase
{
    public function testIntegerValue()
    {
        $integer = new IntegerValue();
        $integer->setValue(3);
        $integer2 = new IntegerValue(3);
        $integer2->setValue(3);
        $integer3 = new IntegerValue(4);
        $integer3->setValue(4);

        $this->assertEquals($integer->getValue(), 3);
        $this->assertTrue($integer->equals($integer2));
        $this->assertEquals($integer->getHash(), $integer2->getHash());
        $this->assertNotEquals($integer->getHash(), $integer3->getHash());
        $integer3->unserialize($integer->serialize());
        $this->assertTrue($integer->equals($integer3));
        $this->assertTrue($integer->getType()->equals(new SingleType(IntegerValue::class)));
    }

    public function testListValue()
    {
        $list = new ListValue();
        $list->setValue([1, 2, 3]);

        $list2 = new ListValue();
        $integer1 = new IntegerValue();
        $integer1->setValue(1);
        $string = new StringValue();
        $string->setValue("1");
        $list2->setValue([$integer1, $string]);

        $this->assertTrue($list->getType()->equals(new ListType(IntegerValue::class)));
        $this->assertTrue($list2->getType()->equals(new ListType(ScalarValue::class)));
    }
}
