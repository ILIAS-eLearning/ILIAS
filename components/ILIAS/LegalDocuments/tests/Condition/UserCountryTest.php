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
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Condition\Definition\UserCountryDefinition;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Condition\UserCountry;
use ILIAS\UI\Factory as UIFactory;
use ilObjUser;

require_once __DIR__ . '/../ContainerMock.php';

class UserCountryTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UserCountry::class, new UserCountry(
            $this->stub(CriterionContent::class),
            $this->stub(UserCountryDefinition::class),
            $this->stub(UIFactory::class)
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

        $instance = new UserCountry(
            $this->stubTree(CriterionContent::class, ['arguments' => ['country' => 'foo']]),
            $this->stub(UserCountryDefinition::class),
            $this->stubTree(UIFactory::class, ['legacy' => $legacy_factory])
        );

        $this->assertSame($legacy, $instance->asComponent());
    }

    public function testEval(): void
    {
        $instance = new UserCountry(
            $this->stubTree(CriterionContent::class, ['arguments' => ['country' => 'foo']]),
            $this->stub(UserCountryDefinition::class),
            $this->stub(UIFactory::class)
        );

        $this->assertTrue($instance->eval($this->stubTree(ilObjUser::class, ['getCountry' => 'foo'])));
    }

    public function testDefinition(): void
    {
        $definition = $this->stub(UserCountryDefinition::class);
        $instance = new UserCountry(
            $this->stub(CriterionContent::class),
            $definition,
            $this->stub(UIFactory::class)
        );

        $this->assertSame($definition, $instance->definition());
    }

    public function testKnownToNeverMatchWith(): void
    {
        $instance = new UserCountry(
            $this->stub(CriterionContent::class),
            $this->stub(UserCountryDefinition::class),
            $this->stub(UIFactory::class)
        );

        $second = new UserCountry(
            $this->stub(CriterionContent::class),
            $this->stub(UserCountryDefinition::class),
            $this->stub(UIFactory::class)
        );

        $this->assertTrue($instance->knownToNeverMatchWith($second));
        $this->assertFalse($instance->knownToNeverMatchWith($this->stub(Condition::class)));
    }
}
