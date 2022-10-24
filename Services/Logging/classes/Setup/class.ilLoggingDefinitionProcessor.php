<?php

declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilLoggingDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected ilDBInterface $db;
    protected string $component_id;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge(): void
    {
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component_id = '';
    }

    public function endComponent(string $component, string $type): void
    {
        $this->component_id = '';
    }

    public function beginTag(string $name, array $attributes): void
    {
        if ($name === "module" || $name === "service") {
            $this->component_id = $attributes["id"] ?? '';
            return;
        }

        if ($name !== "logging") {
            return;
        }

        if ($this->component_id === '') {
            throw new \RuntimeException(
                "Found $name-tag outside of module or service in {$this->component_id}."
            );
        }
        ilLogComponentLevels::updateFromXML($this->component_id);
    }

    public function endTag(string $name): void
    {
        if ($name === "module" || $name === "service") {
            $this->component_id = '';
        }
    }
}
