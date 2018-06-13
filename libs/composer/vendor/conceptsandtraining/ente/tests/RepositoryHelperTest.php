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

class RepositoryHelperTest extends PHPUnit_Framework_TestCase {
	public function test_componentsForEntity() {
		$mock = $this->getMockForTrait(Ente\RepositoryHelper::class);
		$entity = $this->createMock(Ente\Entity::class);
		$component1 = $this->createMock(Ente\Component::class);
		$component2 = $this->createMock(Ente\Component::class);
		$provider1 = $this->createMock(Ente\Provider::class); 
		$provider2 = $this->createMock(Ente\Provider::class); 

		$component_type1 = "COMPONENT_TYPE1";
		$component_type2 = "COMPONENT_TYPE2";

		$mock
			->expects($this->once())
			->method("providersForEntity")
			->with($entity)
			->willReturn([$provider1, $provider2]);

		$provider1
			->expects($this->once())
			->method("componentTypes")
			->willReturn([$component_type1, $component_type2]);

		$provider1
			->expects($this->exactly(2))
			->method("componentsOfType")
			->withConsecutive
				( [$component_type1]
				, [$component_type2]
				)
			->will($this->onConsecutiveCalls([$component1, $component2], []));

		$provider2
			->expects($this->once())
			->method("componentTypes")
			->willReturn([$component_type1, $component_type2]);

		$provider2
			->expects($this->exactly(2))
			->method("componentsOfType")
			->withConsecutive
				( [$component_type1]
				, [$component_type2]
				)
			->will($this->onConsecutiveCalls([], []));

		$components = $mock->componentsForEntity($entity);
		$this->assertEquals([$component1, $component2], $components);
	}
}
