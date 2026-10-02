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

namespace ILIAS\KeyValueStorage\Setup;

use ILIAS\KeyValueStorage\Subject\SubjectProvider;
use ILIAS\Setup;

/**
 * Fails the build if two subject providers share a name, since the name is
 * stored with every value of a subject.
 */
final readonly class SubjectProviderNamesUniqueObjective implements Setup\Objective
{
    public function __construct(
        private Setup\ImplementationOfInterfaceFinder $finder = new Setup\ImplementationOfInterfaceFinder()
    ) {
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return 'KeyValueStorage subject provider names are unique';
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $by_name = [];
        foreach ($this->finder->getMatchingClassNames(SubjectProvider::class, ['.*/tests/.*']) as $class) {
            $name = $this->providerOf($class)->name();
            if (isset($by_name[$name])) {
                throw new Setup\UnachievableException(
                    'The subject provider name "' . $name . '" is used by both '
                    . $by_name[$name] . ' and ' . $class . '.'
                );
            }
            $by_name[$name] = $class;
        }

        return $environment;
    }

    /**
     * @param class-string<SubjectProvider> $class
     */
    private function providerOf(string $class): SubjectProvider
    {
        if ((new \ReflectionClass($class))->getConstructor()?->getNumberOfRequiredParameters() > 0) {
            throw new Setup\UnachievableException(
                'The subject provider ' . $class . ' must not require constructor arguments.'
            );
        }

        return new $class();
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
