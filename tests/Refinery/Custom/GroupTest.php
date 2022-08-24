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

namespace ILIAS\Tests\Refinery\Custom;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Custom\Group as CustomGroup;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Tests\Refinery\TestCase;
use ilLanguage;

class GroupTest extends TestCase
{
    private CustomGroup $group;
    private DataFactory $dataFactory;
    private ilLanguage $language;

    protected function setUp(): void
    {
        $this->dataFactory = new DataFactory();
        $this->language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new CustomGroup($this->dataFactory, $this->language);
    }

    public function testCustomConstraint(): void
    {
        $instance = $this->group->constraint(static function (): void {
        }, 'some error');
        $this->assertInstanceOf(CustomConstraint::class, $instance);
    }

    public function testCustomTransformation(): void
    {
        $instance = $this->group->transformation(static function (): void {
        });
        $this->assertInstanceOf(CustomTransformation::class, $instance);
    }
}
