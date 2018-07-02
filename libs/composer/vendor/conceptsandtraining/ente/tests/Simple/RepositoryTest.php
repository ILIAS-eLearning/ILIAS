<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\Simple\Entity;
use CaT\Ente\Simple\Repository;
use CaT\Ente\Simple\Provider;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;

require_once(__DIR__."/../RepositoryTest.php");

class _Repository extends Repository {
    public function _providers() {
        return $this->providers;
    }
}

class Simple_RepositoryTest extends RepositoryTest {
    protected function entities() {
        return 
            [new Entity(0),
             new Entity(1),
             new Entity(2),
             new Entity(3)];
    }

    /**
     * @inheritdocs
     */
    protected function repository() {
        $entities = $this->entities();
        $repo = new _Repository();

        foreach ($entities as $e) {
            $p = new Provider($e);
            $p->addComponent(new AttachStringMemory($e, "id: {$e->id()}"));
            $repo->addProvider($p);
        }

        return $repo;
    }

    /**
     * @inheritdocs
     */
    protected function hasProvidersForEntities() {
        return $this->entities();
    }

    /**
     * The component types the repository has providers for.
     *
     * @return  string[]
     */
    protected function hasProvidersForComponentTypes() {
        return [AttachString::class];
    }

    public function test_providers() {
        $providers = $this->repository()->_providers();
        $this->assertCount(4, $providers);
        $this->assertEquals([serialize(0),serialize(1),serialize(2),serialize(3)], array_keys($providers));
        $this->assertCount(1, $providers[serialize(0)]);
        $this->assertCount(1, $providers[serialize(1)]);
        $this->assertCount(1, $providers[serialize(2)]);
        $this->assertCount(1, $providers[serialize(3)]);
    }
}
