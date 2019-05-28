<?php

/**
 * Class ilDatabaseReservedWordsTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabaseReservedWordsTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		PHPUnit_Framework_Error_Notice::$enabled = false;
		PHPUnit\Framework\Error\Deprecated::$enabled = false;
		parent::setUp();
		global $ilDB, $DIC;
		$ilDB = new ilDBPdoMySQLInnoDB();
		$DIC['ilDB'] = $ilDB;
	}


	/**
	 * @dataProvider reservedData
	 *
	 * @param $word
	 * @param $is_reserved
	 */
	public function testReservedPDO($word, $is_reserved) {
		$this->assertEquals($is_reserved, ilDBPdoMySQLInnoDB::isReservedWord($word));
	}


	/**
	 * @return array
	 */
	public function reservedData() {
		return [
			['order', true],
			['myfield', false],
			['number', true],
			['null', true],
			['sensitive', true],
			['usage', true],
			['analyze', true],
		];
	}
}
