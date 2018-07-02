<?php

use ILIAS\TMS\Timezone;
use PHPUnit\Framework\TestCase;

class TimezoneCheckerTest extends TestCase {
	public function test_SummerTime() {
		$db = new Timezone\TimezoneDBImpl();

		$checker = new Timezone\TimezoneCheckerImpl($db);
		$checktime =  DateTime::createFromFormat("Y-m-d" , "2018-03-25");
		$this->assertTrue($checker->isSummerTime($checktime));
		$this->assertFalse(!$checker->isSummerTime($checktime));

		$checktime =  DateTime::createFromFormat("Y-m-d" , "2018-10-27");
		$this->assertTrue($checker->isSummerTime($checktime));
		$this->assertFalse(!$checker->isSummerTime($checktime));

		$checktime =  DateTime::createFromFormat("Y-m-d" , "2018-06-19");
		$this->assertTrue($checker->isSummerTime($checktime));
		$this->assertFalse(!$checker->isSummerTime($checktime));
	}

	public function test_WinterTime() {
		$db = new Timezone\TimezoneDBImpl();

		$checker = new Timezone\TimezoneCheckerImpl($db);
		$checktime =  DateTime::createFromFormat("Y-m-d" , "2018-10-28");
		$this->assertTrue(!$checker->isSummerTime($checktime));
		$this->assertFalse($checker->isSummerTime($checktime));

		$checktime =  DateTime::createFromFormat("Y-m-d" , "2018-12-31");
		$this->assertTrue(!$checker->isSummerTime($checktime));
		$this->assertFalse($checker->isSummerTime($checktime));

		$checktime =  DateTime::createFromFormat("Y-m-d" , "2018-11-03");
		$this->assertTrue(!$checker->isSummerTime($checktime));
		$this->assertFalse($checker->isSummerTime($checktime));
	}
}