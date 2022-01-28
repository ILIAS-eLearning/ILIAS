<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use PHPUnit\Framework\TestCase;
use ILIAS\Setup\ObjectiveConstructor;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\NullObjective;

/**
 * Class ObjectiveConstructorTest
 * @package ILIAS\Tests\Setup
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ObjectiveConstructorTest extends TestCase
{
    private ObjectiveConstructor $testObj;
    private ObjectiveCollection $objectiveCollection;
    private \Closure $closure;

    protected function setUp() : void
    {
        parent::setUp();
        $this->objectiveCollection = new ObjectiveCollection(
            "",
            false,
            new NullObjective()
        );

        $this->closure = function () : ObjectiveCollection {
            return $this->objectiveCollection;
        };

        $this->testObj = new ObjectiveConstructor(
            "My description",
            $this->closure
        );
    }

    public function testGetDescription() : void
    {
        $this->assertEquals(
            "My description",
            $this->testObj->getDescription()
        );
    }

    public function testCreate() : void
    {
        $this->assertEquals($this->objectiveCollection, $this->testObj->create());
    }
}
