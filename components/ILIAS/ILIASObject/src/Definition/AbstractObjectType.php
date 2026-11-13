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

namespace ILIAS\ILIASObject\Definition;

/**
 * Base for the object types of the components. Only the id and the class name
 * are mandatory, everything else defaults to "not set". Component and location
 * are derived from the location of the implementing class.
 */
abstract class AbstractObjectType implements ObjectType
{
    private ?string $component = null;

    public function getComponent(): string
    {
        if ($this->component === null) {
            $path = str_replace('\\', '/', (string) (new \ReflectionClass($this))->getFileName());
            $this->component = preg_match('%(components/[^/]+/[^/]+)/%', $path, $matches) === 1
                ? $matches[1]
                : '';
        }
        return $this->component;
    }

    public function getLocation(): string
    {
        return $this->getComponent() . '/classes';
    }

    public function getFlags(): array
    {
        return [ObjectTypeFlag::REPOSITORY];
    }

    final public function hasFlag(ObjectTypeFlag $flag): bool
    {
        return in_array($flag, $this->getFlags(), true);
    }

    public function getTranslationType(): string
    {
        return '0';
    }

    public function getGroup(): ?string
    {
        return null;
    }

    public function getDefaultPosition(): int
    {
        return 0;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 0;
    }

    public function getParentTypes(): array
    {
        return [];
    }

    public function getSubObjectTypes(): array
    {
        return [];
    }

    public function getSubTypes(): array
    {
        return [];
    }

    public function getLanguageModule(): ?string
    {
        return null;
    }

    public function getIconPath(): ?string
    {
        return null;
    }

    public function isLegacyPlugin(): bool
    {
        return false;
    }
}
