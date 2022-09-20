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

namespace ILIAS\Tests\Refinery\Logical;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Integer\GreaterThan;
use ILIAS\Refinery\Integer\LessThan;
use ILIAS\Refinery\Logical\LogicalOr;
use ILIAS\Refinery\Logical\Not;
use ILIAS\Refinery\Logical\Parallel;
use ILIAS\Refinery\Logical\Sequential;
use ILIAS\Refinery\Logical\Group as LogicalGroup;
use ILIAS\Tests\Refinery\TestCase;
use ilLanguage;

class GroupTest extends TestCase
{
    private LogicalGroup $group;
    private DataFactory $dataFactory;
    private ilLanguage $language;
    private Constraint $greaterThanConstraint;
    private Constraint $lessThanConstaint;

    protected function setUp(): void
    {
        $this->dataFactory = new DataFactory();
        $this->language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new LogicalGroup($this->dataFactory, $this->language);

        $this->greaterThanConstraint = new GreaterThan(2, $this->dataFactory, $this->language);
        $this->lessThanConstaint = new LessThan(5, $this->dataFactory, $this->language);
    }

    public function testLogicalOrGroup(): void
    {
        $instance = $this->group->logicalOr([$this->greaterThanConstraint, $this->lessThanConstaint]);
        $this->assertInstanceOf(LogicalOr::class, $instance);
    }

    public function testNotGroup(): void
    {
        $instance = $this->group->not($this->greaterThanConstraint);
        $this->assertInstanceOf(Not::class, $instance);
    }

    public function testParallelGroup(): void
    {
        $instance = $this->group->parallel([$this->greaterThanConstraint, $this->lessThanConstaint]);
        $this->assertInstanceOf(Parallel::class, $instance);
    }

    public function testSequentialGroup(): void
    {
        $instance = $this->group->sequential([$this->greaterThanConstraint, $this->lessThanConstaint]);
        $this->assertInstanceOf(Sequential::class, $instance);
    }
}
