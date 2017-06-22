<?php

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValue;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\ListType;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\SingleType;
use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");

/**
 * Class BackgroundTaskTest
 *
 * @author                 Oskar Truffer <ot@studer-raimann.ch>
 *
 * @group                  needsInstalledILIAS
 */
class TypeTest extends TestCase {

	public function testSubtype() {
		$integer = new IntegerValue(3);
		$integerType = $integer->getType();

		$integer2 = new IntegerValue(2);
		$integer2Type = $integer2->getType();

		$scalar = new ScalarValue(2);
		$scalarType = $scalar->getType();

		$this->assertTrue($integerType->isSubtypeOf($scalarType));
		$this->assertTrue($integer2Type->equals($integerType));
	}


	public function testAncestors() {
		$integer = new SingleType(IntegerValue::class);
		$ancestors = $integer->getAncestors();

		$this->assertTrue($ancestors[0]->equals(new SingleType(AbstractValue::class)));
		$this->assertTrue($ancestors[1]->equals(new SingleType(ScalarValue::class)));
		$this->assertTrue($ancestors[2]->equals(new SingleType(IntegerValue::class)));
	}


	public function testListSubtypes() {
		$scalarList = new ListType(new SingleType(IntegerValue::class));
		$scalarList3 = new ListType(new SingleType(IntegerValue::class));

		$this->assertTrue($scalarList3->isSubtypeOf($scalarList));
		$this->assertTrue($scalarList->isSubtypeOf($scalarList));
	}


	public function testListAncestor() {
		$integerList = new ListType(new SingleType(IntegerValue::class));
		$ancestors = $integerList->getAncestors();

		$this->assertTrue($ancestors[0]->equals(new ListType(AbstractValue::class)));
		$this->assertTrue($ancestors[1]->equals(new ListType(ScalarValue::class)));
		$this->assertTrue($ancestors[2]->equals(new ListType(IntegerValue::class)));
	}


	public function testListOfLists() {
		$list = new ListType(IntegerValue::class);
		$list1 = new ListType(ScalarValue::class);
		$listlist = new ListType($list);
		$listlist1 = new ListType($list1);

		$this->assertTrue($listlist->equals(new ListType(new ListType(IntegerValue::class))));
		$this->assertTrue($listlist->isSubtypeOf($listlist1));
		$this->assertFalse($listlist1->isSubtypeOf($listlist));
	}
}
