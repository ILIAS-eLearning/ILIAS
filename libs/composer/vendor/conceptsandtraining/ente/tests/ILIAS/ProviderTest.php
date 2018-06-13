<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\Entity;
use CaT\Ente\ILIAS\Provider;
use CaT\Ente\ILIAS\UnboundProvider;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

require_once(__DIR__."/../ProviderTest.php");

class ILIAS_ProviderTest extends ProviderTest {
    /**
     * @inheritdocs
     */
    protected function provider() {
		if (isset($this->provider)) {
			return $this->provider;
		}

        $this->object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->object_id = 23;
        $this->object
            ->method("getId")
            ->willReturn($this->object_id);

		$this->entity = new Entity($this->object);

        $this->owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->owner_id = 42;
        $this->owner
            ->method("getId")
            ->willReturn($this->owner_id);

		$this->unbound_provider = $this->createMock(UnboundProvider::class);

        $this->provider = new Provider($this->object, $this->unbound_provider);

        return $this->provider;
    }

    /**
     * @inheritdocs
     */
    protected function doesNotProvideComponentType() {
        return [self::class];
    }

    protected function initUnboundProvider() {
        $this->provider();
        $this->unbound_provider
            ->method("buildComponentsOf")
            ->will($this->returnCallback(function($c) {
                if ($c === AttachString::class) {
                    return [new AttachStringMemory($this->entity, "")];
                }
                if ($c == AttachInt::class) {
                    return [new AttachIntMemory($this->entity, 0)];
                }
                return [];
            }));
    }

    public function test_only_provides_announced_component_types() {
        $this->initUnboundProvider();
        parent::test_only_provides_announced_component_types();
	}

    /**
     * @dataProvider component_types
     */
    public function test_provides_for_own_entity($component_type) {
        $this->initUnboundProvider();
        parent::test_provides_for_own_entity($component_type);
	}

    /**
     * @dataProvider component_types
     */
    public function test_provides_expected_component_types($component_type) {
        $this->initUnboundProvider();
        parent::test_provides_expected_component_types($component_type);
	}

    public function component_types() {
        return [[AttachString::class], [AttachInt::class]];
    }

    public function test_entity_id_is_object_id() {
        $provider = $this->provider();
        $this->assertEquals($this->object_id, $provider->entity()->id());
    }

    public function test_componentTypes() {
        $provider = $this->provider();
		$component_types = ["a", "b"];
		$this->unbound_provider
			->expects($this->once())
			->method("componentTypes")
			->willReturn($component_types);
        $this->assertEquals($component_types, $provider->componentTypes());
    }

    public function test_provided_components() {
        $provider = $this->provider();

		$string = "A STRING";
		$int = 1337;
		$this->unbound_provider
			->expects($this->exactly(2))
			->method("buildComponentsOf")
			->withConsecutive([AttachString::class],[AttachInt::class])
			->will($this->onConsecutiveCalls
				( [new AttachStringMemory($this->entity, $string)]
				, [new AttachIntMemory($this->entity, $int)]
				));

        $attached_strings = $provider->componentsOfType(AttachString::class);
        $this->assertCount(1, $attached_strings);
        $attached_string = $attached_strings[0];
        $this->assertEquals($string, $attached_string->attachedString());

        $attached_ints = $provider->componentsOfType(AttachInt::class);
        $this->assertCount(1, $attached_ints);
        $attached_int = $attached_ints[0];
        $this->assertEquals($int, $attached_int->attachedInt());
    }

    public function test_caching() {
        $provider = $this->provider();

		$string = "A STRING";
		$int = 1337;
		$this->unbound_provider
			->expects($this->exactly(1))
			->method("buildComponentsOf")
			->with(AttachString::class)
			->willReturn([new AttachStringMemory($this->entity, $string)]);

        $provider->componentsOfType(AttachString::class);
        $provider->componentsOfType(AttachString::class);
    }

    public function test_object() {
        $object = $this->provider()->object();
		$this->assertEquals($this->object, $object);
    }

    public function test_owners() {
        $provider = $this->provider();
		$this->unbound_provider
			->expects($this->once())
			->method("owners")
			->willReturn([$this->owner]);
        $owners = $provider->owners();
        $this->assertEquals([$this->owner], $owners);
    }

    public function test_unboundProvider() {
        $provider = $this->provider();
        $this->assertEquals($this->unbound_provider, $provider->unboundProvider());
    }
}
