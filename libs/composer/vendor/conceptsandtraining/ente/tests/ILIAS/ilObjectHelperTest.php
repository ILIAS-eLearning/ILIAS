<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente;


if (!interface_exists("ilDBInterface")) {
    require_once(__DIR__."/ilDBInterface.php");
}

if (!interface_exists("ilTree")) {
    require_once(__DIR__."/ilTree.php");
}

if (!interface_exists("ilObjectDataCache")) {
    require_once(__DIR__."/ilObjectDataCache.php");
}

abstract class ilObjectHelperMock {
	use Ente\ILIAS\ilObjectHelper;
	public function _getProviderDB() {
		return $this->getProviderDB();
	}
}

class ilObjectHelperTest extends PHPUnit_Framework_TestCase {
	public function test_getProviderDB() {
		$db = $this->createMock(\ilDBInterface::class);
		$tree = $this->createMock(\ilTree::class);
		$objDataCache = $this->createMock(\ilObjectDataCache::class);

		$dic = [];
		$dic["ilDB"] = $db;
		$dic["tree"] = $tree;
		$dic["ilObjDataCache"] = $objDataCache;

		$mock = $this
			->getMockBuilder(ilObjectHelperMock::class)
			->setMethods(["getDIC"])
			->getMock();
	

		$mock
			->expects($this->once())
			->method("getDIC")
			->willReturn($dic);

		$provider_db = $mock->_getProviderDB();

		$this->assertInstanceOf(Ente\ILIAS\ProviderDB::class, $provider_db);
	}
}
