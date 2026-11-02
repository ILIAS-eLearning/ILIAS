<?php

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

declare(strict_types=1);

namespace ILIAS\LegalDocuments\test\Condition;

use ILIAS\LegalDocuments\Condition;
use ILIAS\UI\Component\Legacy;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\Condition\Definition\RoleDefinition;
use ILIAS\LegalDocuments\Value\CriterionContent;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Condition\Role;
use ilRbacReview;
use ilObjUser;

require_once __DIR__ . '/../ContainerMock.php';

class RoleTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Role::class, new Role(
            $this->stub(CriterionContent::class),
            $this->stub(RoleDefinition::class),
            $this->stub(UIFactory::class),
            $this->stub(ilRbacReview::class)
        ));
    }

    public function testAsComponent(): void
    {
        $legacy = $this->stub(Legacy\Content::class);
        $legacy_factory = $this->mock(Legacy\Factory::class);
        $legacy_factory
            ->expects($this->once())
            ->method('content')
            ->willReturn($legacy);

        $this->assertSame($legacy, (new Role(
            $this->stubTree(CriterionContent::class, ['arguments' => ['role_id' => 78]]),
            $this->stub(RoleDefinition::class),
            $this->stubTree(UIFactory::class, ['legacy' => $legacy_factory]),
            $this->stub(ilRbacReview::class)
        ))->asComponent());
    }

    public function testEval(): void
    {
        $rbac = $this->mockMethod(ilRbacReview::class, 'isAssigned', [45, 78], true);

        $instance = new Role(
            $this->stubTree(CriterionContent::class, ['arguments' => ['role_id' => 78]]),
            $this->stub(RoleDefinition::class),
            $this->stub(UIFactory::class),
            $rbac
        );

        $this->assertTrue($instance->eval($this->stubTree(ilObjUser::class, ['getId' => 45])));
    }

    public function testDefinition(): void
    {
        $definition = $this->stub(RoleDefinition::class);

        $instance = new Role(
            $this->stub(CriterionContent::class),
            $definition,
            $this->stub(UIFactory::class),
            $this->stub(ilRbacReview::class)
        );

        $this->assertSame($definition, $instance->definition());
    }

    public function testKnownToNeverMatchWith(): void
    {
        $instance = new Role(
            $this->stub(CriterionContent::class),
            $this->stub(RoleDefinition::class),
            $this->stub(UIFactory::class),
            $this->stub(ilRbacReview::class)
        );

        $this->assertFalse($instance->knownToNeverMatchWith($this->stub(Condition::class)));
    }
}
