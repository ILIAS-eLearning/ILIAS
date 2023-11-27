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

namespace ILIAS\Component\Dependencies;

/**
 * A dependency where the component gives something to the world.
 */
class Out implements Dependency
{
    protected Name|string $name;
    protected array $dependencies = [];
    protected ?OfComponent $component = null;
    protected array $resolves = [];

    public function __construct(
        protected OutType $type,
        string $name,
        // Helper field to be used to process specific type
        public readonly mixed $aux,
        array $dependencies
    ) {
        if ($type !== OutType::INTERNAL) {
            $name = new Name($name);
        }
        $this->name = $name;
        foreach ($dependencies as $d) {
            $d->addDependant($this);
        }
    }

    public function __toString(): string
    {
        return $this->type->value . ": " . $this->name;
    }

    public function getType(): OutType
    {
        return $this->type;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function setComponent(OfComponent $component): void
    {
        if (!is_null($this->component)) {
            throw new \LogicException(
                "There already is a component here."
            );
        }
        $this->component = $component;
    }

    public function getComponent(): OfComponent
    {
        if (is_null($this->component)) {
            throw new \LogicException(
                "There is no component here."
            );
        }

        return $this->component;
    }

    public function addDependency(In $in): void
    {
        $this->dependencies[(string) $in] = $in;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function addResolves(In $in): void
    {
        $this->resolves[] = $in;
    }
}
