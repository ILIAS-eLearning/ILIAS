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
            $this->mock(CriterionContent::class),
            $this->mock(UserCountryDefinition::class),
            $this->mock(UIFactory::class),
            new \ILIAS\Data\Privacy\Purpose\Purposes()
        ));
    }

    public function testAsComponent(): void
    {
        $legacy = $this->mock(Legacy\Content::class);
        $legacy_factory = $this->mock(Legacy\Factory::class);
        $legacy_factory
            ->expects($this->once())
            ->method('content')
            ->willReturn($legacy);

        $instance = new UserCountry(
            $this->mockTree(CriterionContent::class, ['arguments' => ['country' => 'foo']]),
            $this->mock(UserCountryDefinition::class),
            $this->mockTree(UIFactory::class, ['legacy' => $legacy_factory]),
            new \ILIAS\Data\Privacy\Purpose\Purposes()
        );

        $this->assertSame($legacy, $instance->asComponent());
    }

    public function testEval(): void
    {
        $instance = new UserCountry(
            $this->mockTree(CriterionContent::class, ['arguments' => ['country' => 'foo']]),
            $this->mock(UserCountryDefinition::class),
            $this->mock(UIFactory::class),
            new \ILIAS\Data\Privacy\Purpose\Purposes()
        );

        $this->assertTrue($instance->eval($this->mockTree(ilObjUser::class, [
            'getProfileData' => new \ILIAS\User\Profile\Data(
                postal_address: new \ILIAS\Data\Privacy\Types\PostalAddress(
                    new \ILIAS\Data\Privacy\Types\PostalAddressValue(country: 'foo'),
                    new \ILIAS\Data\Privacy\Fixtures\UnitTestSource('user_country_condition')
                )
            )
        ])));
    }

    public function testDefinition(): void
    {
        $definition = $this->mock(UserCountryDefinition::class);
        $instance = new UserCountry(
            $this->mock(CriterionContent::class),
            $definition,
            $this->mock(UIFactory::class),
            new \ILIAS\Data\Privacy\Purpose\Purposes()
        );

        $this->assertSame($definition, $instance->definition());
    }

    public function testKnownToNeverMatchWith(): void
    {
        $instance = new UserCountry(
            $this->mock(CriterionContent::class),
            $this->mock(UserCountryDefinition::class),
            $this->mock(UIFactory::class),
            new \ILIAS\Data\Privacy\Purpose\Purposes()
        );

        $second = new UserCountry(
            $this->mock(CriterionContent::class),
            $this->mock(UserCountryDefinition::class),
            $this->mock(UIFactory::class),
            new \ILIAS\Data\Privacy\Purpose\Purposes()
        );

        $this->assertTrue($instance->knownToNeverMatchWith($second));
        $this->assertFalse($instance->knownToNeverMatchWith($this->mock(Condition::class)));
    }
}
