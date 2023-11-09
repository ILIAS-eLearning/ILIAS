<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectiveCollection = new ObjectiveCollection(
            "",
            false,
            new NullObjective()
        );

        $this->closure = function (): ObjectiveCollection {
            return $this->objectiveCollection;
        };

        $this->testObj = new ObjectiveConstructor(
            "My description",
            $this->closure
        );
    }

    public function testGetDescription(): void
    {
        $this->assertEquals(
            "My description",
            $this->testObj->getDescription()
        );
    }

    public function testCreate(): void
    {
        $this->assertEquals($this->objectiveCollection, $this->testObj->create());
    }
}
