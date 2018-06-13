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
use CaT\Ente\ILIAS\ProviderDB;
use CaT\Ente\ILIAS\Repository;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

require_once(__DIR__."/../RepositoryTest.php");

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

class ILIAS_RepositoryTest_Object extends ilObject {
    protected $id;
    public function __construct($id) {
        $this->id = $id;
    }
    public function getId() {
        return $this->id;
    }
}

class ILIAS_RepositoryTest extends RepositoryTest {
    protected function object($id) {
        return new ILIAS_RepositoryTest_Object($id);
    }

    protected function entity($id) {
        return new Entity($this->object($id));
    }

    /**
     * @inheritdocs
     */
    protected function repository() {
        $this->provider_db = $this->createMock(ProviderDB::class);

        $this->provider_1 = $this
            ->getMockBuilder(Provider::class)
            ->setMethods(["componentsOfType", "componentTypes", "entity"])
            ->getMock();

        $this->provider_1
            ->method("entity")
            ->willReturn($this->entity(1));

        $this->provider_1
            ->method("componentTypes")
            ->willReturn([AttachInt::class]);

        $this->provider_2 = $this
            ->getMockBuilder(Provider::class)
            ->setMethods(["componentsOfType", "componentTypes", "entity"])
            ->getMock();

        $this->provider_2
            ->method("entity")
            ->willReturn($this->entity(2));

        $this->provider_2
            ->method("componentTypes")
            ->willReturn([AttachString::class]);

        $this->provider_db
            ->method("providersFor")
            ->will($this->returnCallback(function($o, $ct) {
                if ($o == $this->object(1)) {
                    if ($ct === null || $ct === AttachInt::class) 
                        return [$this->provider_1];
                    return [];
                }
                if ($o == $this->object(2)) {
                    if ($ct === null || $ct === AttachString::class) 
                        return [$this->provider_2];
                    return [];
                }
                $this->assertFalse("This does not exist.");
            }));

        return new Repository($this->provider_db);
    }

    /**
     * @inheritdocs
     */
    protected function hasProvidersForEntities() {
        return [$this->entity(1), $this->entity(2)];
    }

    /**
     * @inheritdocs
     */
    protected function hasProvidersForComponentTypes() {
        return [AttachInt::class, AttachString::class];
    }

    public function test_provider_db() {
        $this->repository();
        $this->assertEquals([$this->provider_1], $this->provider_db->providersFor($this->object(1)));
        $this->assertEquals([$this->provider_2], $this->provider_db->providersFor($this->object(2)));
    }

    public function test_ILIAS_Entities_only() {
        $repository = $this->repository();
        $entity = new \CaT\Ente\Simple\Entity(1);

        $this->assertEquals([], $repository->providersForEntity($entity));
    }

    public function test_providersForEntity_calls_providersFor() {
        $repository = $this->repository();

        $this->provider_db
            ->expects($this->once())
            ->method("providersFor")
            ->with($this->object(1));

        $repository->providersForEntity($this->entity(1));
    }
}
