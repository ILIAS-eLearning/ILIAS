<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilSystemCheckDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component_id;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component_id = null;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component_id = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name === "module" || $name === "service") {
            $this->component_id = $attributes["id"] ?? null;
            return;
        }
        if ($name !== "systemcheck" || $name !== "systemcheck_task") {
            return;
        }
        if ($this->component_id === null) {
            throw new \RuntimeException(
                "Found $name-tag outside of module or service."
            );
        }

        if ($name === "systemcheck") {
            ilSCGroups::getInstance()->updateFromComponentDefinition($this->component_id);
        }
        if ($name === "systemcheck_task") {
            $group_id = ilSCGroups::getInstance()->lookupGroupByComponentId($this->component_id);
            $tasks = ilSCTasks::getInstanceByGroupId($group_id);
            $tasks->updateFromComponentDefinition((string) ($attributes['identifier'] ?? ''));
        }
    }

    public function endTag(string $name) : void
    {
        if ($name === "module" || $name === "service") {
            $this->component_id = null;
        }
    }
}
