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

abstract class ilHandlerObjectHelperMock {
	use Ente\ILIAS\ilHandlerObjectHelper;
	public function _getRepository() {
		return $this->getRepository();
	}
	public function _getComponents() {
		return $this->getComponents();
	}
	public function _getComponentsOfType($t) {
		return $this->getComponentsOfType($t);
	}
}

class ilHandlerObjectHelperTest extends PHPUnit_Framework_TestCase {
	public function test_getRepository() {
		$provider_db = $this->createMock(Ente\ILIAS\ProviderDB::class);

		$mock = $this
			->getMockBuilder(ilHandlerObjectHelperMock::class)
			->setMethods(["getProviderDB", "getDIC", "getEntityRefId"])
			->getMock();

		$mock
			->expects($this->once())
			->method("getProviderDB")
			->willReturn($provider_db);

		$repository = $mock->_getRepository();

		$this->assertInstanceOf(Ente\Repository::class, $repository);
	}

	public function test_getComponents() {
		$repository = $this->createMock(Ente\Repository::class);
		$entity = $this->createMock(Ente\Entity::class);

		$mock = $this
			->getMockBuilder(ilHandlerObjectHelperMock::class)
			->setMethods(["getProviderDB", "getDIC", "getEntityRefId", "getEntity", "getRepository"])
			->getMock();

		$mock
			->expects($this->once())
			->method("getRepository")
			->willReturn($repository);

		$mock
			->expects($this->once())
			->method("getEntity")
			->willReturn($entity);

		$array = new \StdClass();

		$repository
			->expects($this->once())
			->method("componentsForEntity")
			->with($entity)
			->willReturn($array);

		$components = $mock->_getComponents();

		$this->assertSame($array, $components);
	}


	public function test_getComponentsOfType() {
		$repository = $this->createMock(Ente\Repository::class);
		$entity = $this->createMock(Ente\Entity::class);

		$mock = $this
			->getMockBuilder(ilHandlerObjectHelperMock::class)
			->setMethods(["getProviderDB", "getDIC", "getEntityRefId", "getEntity", "getRepository"])
			->getMock();

		$mock
			->expects($this->once())
			->method("getRepository")
			->willReturn($repository);

		$mock
			->expects($this->once())
			->method("getEntity")
			->willReturn($entity);

		$array = new \StdClass();
		$type = "TYPE";

		$repository
			->expects($this->once())
			->method("componentsForEntity")
			->with($entity, $type)
			->willReturn($array);

		$components = $mock->_getComponentsOfType($type);

		$this->assertSame($array, $components);
	}
}
