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
 * Generic, configurable object type. Only meant for types which are not known
 * before runtime, e.g. the ones of the adapter for repository object plugins.
 * Components implement their types in own classes (see AbstractObjectType).
 */
final class ObjectTypeDefinition implements ObjectType
{
    /**
     * @var array<string, bool>
     */
    private array $flags = [
        ObjectTypeFlag::REPOSITORY->value => true
    ];
    private string $translation_type = '0';
    private ?string $group = null;
    private int $default_position = 0;
    private int $default_presentation_position = 0;
    /**
     * @var array<string, int>
     */
    private array $parent_types = [];
    /**
     * @var array<string, int>
     */
    private array $sub_object_types = [];
    /**
     * @var array<string, bool>
     */
    private array $sub_types = [];
    private bool $legacy_plugin = false;
    private ?string $language_module = null;
    private ?string $icon_path = null;

    public function __construct(
        private readonly string $id,
        private readonly string $class_name,
        private readonly string $component,
        private readonly string $location
    ) {
    }

    public function withFlags(ObjectTypeFlag ...$flags): self
    {
        $clone = clone $this;
        foreach ($flags as $flag) {
            $clone->flags[$flag->value] = true;
        }
        return $clone;
    }

    public function withoutFlags(ObjectTypeFlag ...$flags): self
    {
        $clone = clone $this;
        foreach ($flags as $flag) {
            unset($clone->flags[$flag->value]);
        }
        return $clone;
    }

    public function withTranslationType(string $translation_type): self
    {
        $clone = clone $this;
        $clone->translation_type = $translation_type;
        return $clone;
    }

    public function withGroup(string $group): self
    {
        $clone = clone $this;
        $clone->group = $group;
        return $clone;
    }

    public function withPositions(int $default_position, int $default_presentation_position): self
    {
        $clone = clone $this;
        $clone->default_position = $default_position;
        $clone->default_presentation_position = $default_presentation_position;
        return $clone;
    }

    public function withParentType(string $type, int $max = 0): self
    {
        $clone = clone $this;
        $clone->parent_types[$type] = $max;
        return $clone;
    }

    public function withSubObjectType(string $type, int $max = 0): self
    {
        $clone = clone $this;
        $clone->sub_object_types[$type] = $max;
        return $clone;
    }

    public function withSubType(string $sub_type, bool $amet): self
    {
        $clone = clone $this;
        $clone->sub_types[$sub_type] = $amet;
        return $clone;
    }

    public function withLanguageModule(string $language_module): self
    {
        $clone = clone $this;
        $clone->language_module = $language_module;
        return $clone;
    }

    public function withIconPath(string $icon_path): self
    {
        $clone = clone $this;
        $clone->icon_path = $icon_path;
        return $clone;
    }

    public function asLegacyPlugin(): self
    {
        $clone = clone $this;
        $clone->legacy_plugin = true;
        return $clone;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getFlags(): array
    {
        return array_map(
            static fn(string $flag): ObjectTypeFlag => ObjectTypeFlag::from($flag),
            array_keys(array_filter($this->flags))
        );
    }

    public function hasFlag(ObjectTypeFlag $flag): bool
    {
        return $this->flags[$flag->value] ?? false;
    }

    public function getTranslationType(): string
    {
        return $this->translation_type;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getDefaultPosition(): int
    {
        return $this->default_position;
    }

    public function getDefaultPresentationPosition(): int
    {
        return $this->default_presentation_position;
    }

    public function getParentTypes(): array
    {
        return $this->parent_types;
    }

    public function getSubObjectTypes(): array
    {
        return $this->sub_object_types;
    }

    public function getSubTypes(): array
    {
        return $this->sub_types;
    }

    public function getLanguageModule(): ?string
    {
        return $this->language_module;
    }

    public function getIconPath(): ?string
    {
        return $this->icon_path;
    }

    public function isLegacyPlugin(): bool
    {
        return $this->legacy_plugin;
    }
}
