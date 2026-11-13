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

namespace ILIAS\UICore\Ctrl;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ContributedCommandClassResolver implements CommandClassResolver
{
    /**
     * @var array<string, CommandClassFactory>|null lowercase class name => factory
     */
    private ?array $factories = null;

    /**
     * @param iterable<CommandClassFactory> $contributed_factories
     */
    public function __construct(
        private readonly iterable $contributed_factories
    ) {
    }

    public function create(Ctrl $ctrl, string $class_name, mixed ...$arguments): object
    {
        $factory = $this->getFactories()[$this->normalize($class_name)] ?? null;
        if ($factory === null) {
            return new $class_name(...$arguments);
        }

        return $factory->create($ctrl, $class_name, ...$arguments);
    }

    /**
     * @return array<string, CommandClassFactory>
     */
    private function getFactories(): array
    {
        if ($this->factories !== null) {
            return $this->factories;
        }

        $this->factories = [];
        foreach ($this->contributed_factories as $factory) {
            foreach ($factory->getCommandClasses() as $class_name) {
                $key = $this->normalize($class_name);
                if (isset($this->factories[$key])) {
                    throw new \LogicException(
                        "Command class '$class_name' is contributed by more than one " . CommandClassFactory::class
                    );
                }
                $this->factories[$key] = $factory;
            }
        }

        return $this->factories;
    }

    private function normalize(string $class_name): string
    {
        return strtolower(ltrim($class_name, '\\'));
    }
}
