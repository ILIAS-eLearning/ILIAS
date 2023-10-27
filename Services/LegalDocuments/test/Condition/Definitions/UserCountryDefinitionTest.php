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
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Condition\Definition\UserCountryDefinition;
use ILIAS\LegalDocuments\Condition\UserCountry;

require_once __DIR__ . '/../../ContainerMock.php';

class UserCountryDefinitionTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UserCountryDefinition::class, new UserCountryDefinition(
            $this->mock(UI::class),
            $this->fail(...)
        ));
    }

    public function testFormGroup(): void
    {
        $instance = new UserCountryDefinition(
            $this->mock(UI::class),
            fn() => $this->mock(Constraint::class)
        );

        $this->assertInstanceOf(Group::class, $instance->formGroup());
    }

    public function testWithCriterion(): void
    {
        $instance = new UserCountryDefinition($this->mock(UI::class), $this->fail(...));
        $this->assertInstanceOf(UserCountry::class, $instance->withCriterion($this->mock(CriterionContent::class)));
    }

    public function testTranslatedType(): void
    {
        $instance = new UserCountryDefinition($this->mockMethod(UI::class, 'txt', ['crit_type_usr_country'], 'foo'), $this->fail(...));
        $this->assertSame('foo', $instance->translatedType());
    }

    public function testTranslatedCountry(): void
    {
        $instance = new UserCountryDefinition($this->mockMethod(UI::class, 'txt', ['meta_c_FOO'], 'foo'), $this->fail(...));
        $this->assertSame('foo', $instance->translatedCountry('foo'));
    }
}
