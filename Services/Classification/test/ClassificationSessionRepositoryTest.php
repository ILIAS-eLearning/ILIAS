<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ClassificationSessionRepositoryTest extends TestCase
{
    protected ilClassificationSessionRepository $repo;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repo = new ilClassificationSessionRepository(14);
    }

    protected function tearDown() : void
    {
    }

    public function testValueForProvider() : void
    {
        $repo = $this->repo;
        $repo->setValueForProvider("prov", [1,2,3]);
        $this->assertEquals(
            [1,2,3],
            $repo->getValueForProvider("prov")
        );
    }

    public function testUnsetAll() : void
    {
        $repo = $this->repo;
        $repo->setValueForProvider("prov", [1,2,3]);
        $repo->unsetAll();
        $this->assertEquals(
            [],
            $repo->getValueForProvider("prov")
        );
    }

    public function testUnsetValueForProvider() : void
    {
        $repo = $this->repo;
        $repo->setValueForProvider("prov1", [1,2,3]);
        $repo->setValueForProvider("prov2", [3,4,5]);
        $repo->unsetValueForProvider("prov1");
        $this->assertEquals(
            [],
            $repo->getValueForProvider("prov1")
        );
        $this->assertEquals(
            [3,4,5],
            $repo->getValueForProvider("prov2")
        );
    }
}
