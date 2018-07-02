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

if (!interface_exists("ilObject")) {
	require_once(__DIR__."/ilObject.php");
}

abstract class ilProviderObjectHelperMock extends ilObject {
	use Ente\ILIAS\ilProviderObjectHelper;
	public function _deleteUnboundProviders() {
		$this->deleteUnboundProviders();
	}
	public function _createUnboundProvider($object_type, $class_name, $path) {
		$this->createUnboundProvider($object_type, $class_name, $path);
	}
}

class _SeparatedUnboundProvider extends Ente\ILIAS\SeparatedUnboundProvider {
	public function componentTypes() {}
	public function buildComponentsOf($c, Ente\ILIAS\Entity $e) {}
}

class _SharedUnboundProvider extends Ente\ILIAS\SharedUnboundProvider {
	public function componentTypes() {}
	public function buildComponentsOf($c, Ente\ILIAS\Entity $e) {}
}

class ilProviderObjectHelperTest extends PHPUnit_Framework_TestCase {
	public function test_deleteUnboundProviders() {
		$provider_db = $this->createMock(Ente\ILIAS\ilProviderDB::class);
		$up1 = $this->createMock(Ente\ILIAS\UnboundProvider::class);
		$up2 = $this->createMock(Ente\ILIAS\UnboundProvider::class);

		$mock = $this
			->getMockBuilder(ilProviderObjectHelperMock::class)
			->setMethods(["getProviderDB", "getDIC"])
			->getMock();

		$mock
			->expects($this->atLeast(1))
			->method("getProviderDB")
			->willReturn($provider_db);

		$provider_db
			->expects($this->once())
			->method("unboundProvidersOf")
			->with($mock)
			->willReturn([$up1, $up2]);

		$provider_db
			->expects($this->exactly(2))
			->method("delete")
			->withConsecutive([$up1], [$up2]);

		$mock->_deleteUnboundProviders();
	}

	public function test_createSeperatedUnboundProvider() {
		$provider_db = $this->createMock(Ente\ILIAS\ilProviderDB::class);

		$mock = $this
			->getMockBuilder(ilProviderObjectHelperMock::class)
			->setMethods(["getProviderDB", "getDIC"])
			->getMock();

		$mock
			->expects($this->once())
			->method("getProviderDB")
			->willReturn($provider_db);

		$object_type = "TYPE";
		$class_name = _SeparatedUnboundProvider::class;
		$path = "PATH";

		$provider_db
			->expects($this->once())
			->method("createSeparatedUnboundProvider")
			->with($mock, $object_type, $class_name, $path);

		$mock->_createUnboundProvider($object_type, $class_name, $path);
	}

	public function test_createSharedUnboundProvider() {
		$provider_db = $this->createMock(Ente\ILIAS\ilProviderDB::class);

		$mock = $this
			->getMockBuilder(ilProviderObjectHelperMock::class)
			->setMethods(["getProviderDB", "getDIC"])
			->getMock();

		$mock
			->expects($this->once())
			->method("getProviderDB")
			->willReturn($provider_db);

		$object_type = "TYPE";
		$class_name = _SharedUnboundProvider::class;
		$path = "PATH";

		$provider_db
			->expects($this->once())
			->method("createSharedUnboundProvider")
			->with($mock, $object_type, $class_name, $path);

		$mock->_createUnboundProvider($object_type, $class_name, $path);
	}

	public function test_createUnboundProvider_with_non_provider_class() {
		$object_type = "TYPE";
		$class_name = "CLASS";
		$path = "PATH";

		$thrown = true;
		try {
			$mock->_createUnboundProvider($object_type, $class_name, $path);
			$this->assertFalse("This should not happen.");
		}
		catch (\Exception $e) {
			$thrown = true;
		}
		$this->assertTrue($thrown);
	}
}
