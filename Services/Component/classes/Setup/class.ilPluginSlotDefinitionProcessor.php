<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilPluginSlotDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM il_pluginslot");
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $type . "/" . $component;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name !== "pluginslot") {
            return;
        }
        if (!isset($attributes["id"]) || !isset($attributes["name"])) {
            throw new \InvalidArgumentException(
                "Expected attributes 'id' and 'name' for tag 'pluginslot' in {$this->component}"
            );
        }

        $this->db->manipulateF(
            "INSERT INTO il_pluginslot (component, id, name) VALUES (%s, %s, %s)",
            ["text", "text", "text"],
            [$this->component, $attributes["id"], $attributes["name"]]
        );
    }

    public function endTag(string $name) : void
    {
    }
}
