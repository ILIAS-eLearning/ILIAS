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

namespace ILIAS\Tests\KeyValueStorage\Setup;

use ILIAS\KeyValueStorage\Setup\SubjectProviderNamesUniqueObjective;
use ILIAS\KeyValueStorage\Subject\SubjectProvider;
use ILIAS\Setup\Environment;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\UnachievableException;
use ILIAS\Tests\KeyValueStorage\Internal\ImpostorSubjectProvider;
use ILIAS\Tests\KeyValueStorage\NamedSubjectProvider;
use PHPUnit\Framework\TestCase;

class SubjectProviderNamesUniqueObjectiveTest extends TestCase
{
    public function testProvidersWithDistinctNamesAreAccepted(): void
    {
        $environment = $this->createStub(Environment::class);

        self::assertSame(
            $environment,
            $this->objectiveFinding(NamedSubjectProvider::class, OtherSubjectProvider::class)->achieve($environment)
        );
    }

    public function testTwoProvidersWithTheSameNameFailTheBuild(): void
    {
        $this->expectException(UnachievableException::class);
        $this->expectExceptionMessage(
            'The subject provider name "test" is used by both '
            . NamedSubjectProvider::class . ' and ' . ImpostorSubjectProvider::class . '.'
        );

        $this->objectiveFinding(NamedSubjectProvider::class, ImpostorSubjectProvider::class)
            ->achieve($this->createStub(Environment::class));
    }

    public function testAProviderRequiringConstructorArgumentsFailsTheBuild(): void
    {
        $this->expectException(UnachievableException::class);
        $this->expectExceptionMessage(
            'The subject provider ' . DependentSubjectProvider::class . ' must not require constructor arguments.'
        );

        $this->objectiveFinding(DependentSubjectProvider::class)->achieve($this->createStub(Environment::class));
    }

    public function testTheTestsOfAllComponentsAreIgnored(): void
    {
        $finder = $this->createMock(ImplementationOfInterfaceFinder::class);
        $finder->expects($this->once())
            ->method('getMatchingClassNames')
            ->with(SubjectProvider::class, ['.*/tests/.*'])
            ->willReturn(new \ArrayIterator([]));

        (new SubjectProviderNamesUniqueObjective($finder))->achieve($this->createStub(Environment::class));
    }

    private function objectiveFinding(string ...$classes): SubjectProviderNamesUniqueObjective
    {
        $finder = $this->createStub(ImplementationOfInterfaceFinder::class);
        $finder->method('getMatchingClassNames')->willReturn(new \ArrayIterator($classes));

        return new SubjectProviderNamesUniqueObjective($finder);
    }
}
