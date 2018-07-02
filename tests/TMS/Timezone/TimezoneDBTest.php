<?php

use ILIAS\TMS\Timezone;
use PHPUnit\Framework\TestCase;

class TimezoneDBTest extends TestCase {
	public function test_ReadFor() {
		$db = new Timezone\TimezoneDBImpl();

		$times = $db->readFor("2018");
		$this->assertEquals(DateTime::createFromFormat("Y-m-d" , "2018-03-25"), $times["start_summer"]);
		$this->assertEquals(DateTime::createFromFormat("Y-m-d" , "2018-10-28"), $times["start_winter"]);

		$times = $db->readFor("2019");
		$this->assertEquals(DateTime::createFromFormat("Y-m-d" , "2019-03-31"), $times["start_summer"]);
		$this->assertEquals(DateTime::createFromFormat("Y-m-d" , "2019-10-27"), $times["start_winter"]);
	}
}