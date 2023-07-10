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
 ********************************************************************
 */

declare(strict_types=1);

class ilComponentInfoDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected array $data = [];
    protected array $slots = [];
    protected ?string $component_id;
    protected ?string $component;
    protected ?string $type;

    public function getData(): array
    {
        return $this->data;
    }

    public function purge(): void
    {
        $this->data = [];
        $this->slots = [];
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component_id = null;
        $this->component = $component;
        $this->type = $type;
    }

    public function endComponent(string $component, string $type): void
    {
        $this->component_id = null;
        $this->component = null;
        $this->type = null;
    }

    public function beginTag(string $name, array $attributes): void
    {
        if ($name === "module") {
            $type = "Modules";
        } elseif ($name === "service") {
            $type = "Services";
        } elseif ($name === "pluginslot") {
            $type = null;
        } else {
            return;
        }

        if (!isset($attributes["id"])) {
            throw new \InvalidArgumentException(
                "Expected attribute 'id' for tag '$name' in $this->component"
            );
        }

        $id = $attributes["id"];
        if (!is_null($type)) {
            if ($type !== $this->type) {
                throw new \InvalidArgumentException(
                    "Type $this->type and tag don't match for component $this->component"
                );
            }
            if (isset($this->data[$id])) {
                throw new \LogicException(
                    "In $this->type/$this->component: Id '$id' for component is used twice. First occurence was in {$this->data[$id][0]}/{$this->data[$id][1]}."
                );
            }
            $this->component_id = $id;
            $this->data[$id] = [$this->type, $this->component, []];
        } else {
            if (!isset($attributes["name"])) {
                throw new \InvalidArgumentException(
                    "Expected attribute 'name' for tag '$name' in $this->component"
                );
            }
            if (isset($this->slots[$id])) {
                throw new \LogicException(
                    "In $this->type/$this->component: Id '$id' for plugin slot is used twice. First occurence was in {$this->slots[$id][0]}/{$this->slots[$id][1]}."
                );
            }
            $this->slots[$id] = [$this->type, $this->component];
            $this->data[$this->component_id][2][] = [$id, $attributes["name"]];
        }
    }

    public function endTag(string $name): void
    {
    }
}
