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

namespace ILIAS\LegalDocuments\test\Condition\Definition;

use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\test\ContainerMock;
use ilRbacReview;
use ilObjectDataCache;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Condition\Definition\RoleDefinition;
use ILIAS\LegalDocuments\Condition\Role;

require_once __DIR__ . '/../../ContainerMock.php';

class RoleDefinitionTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(RoleDefinition::class, new RoleDefinition(
            $this->mock(UI::class),
            $this->mock(ilObjectDataCache::class),
            $this->mock(ilRbacReview::class),
            $this->fail(...)
        ));
    }

    public function testFormGroup(): void
    {
        $instance = new RoleDefinition(
            $this->mock(UI::class),
            $this->mock(ilObjectDataCache::class),
            $this->mock(ilRbacReview::class),
            fn() => $this->mock(Constraint::class)
        );

        $this->assertInstanceOf(Group::class, $instance->formGroup());
    }

    public function testTranslatedRole(): void
    {
        $instance = new RoleDefinition(
            $this->mock(UI::class),
            $this->mockMethod(ilObjectDataCache::class, 'lookupTitle', [87], 'foo'),
            $this->mock(ilRbacReview::class),
            $this->fail(...)
        );

        $this->assertSame('foo', $instance->translatedRole(87));
    }

    public function testTranslatedType(): void
    {
        $instance = new RoleDefinition(
            $this->mockMethod(UI::class, 'txt', ['crit_type_usr_global_role'], 'foo'),
            $this->mock(ilObjectDataCache::class),
            $this->mock(ilRbacReview::class),
            $this->fail(...)
        );

        $this->assertSame('foo', $instance->translatedType());
    }

    public function testWithCriterion(): void
    {
        $instance = new RoleDefinition(
            $this->mock(UI::class),
            $this->mock(ilObjectDataCache::class),
            $this->mock(ilRbacReview::class),
            $this->fail(...)
        );

        $this->assertInstanceOf(Role::class, $instance->withCriterion($this->mock(CriterionContent::class)));
    }
}
