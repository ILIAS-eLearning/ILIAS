<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilEventDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM il_event_handling WHERE component NOT LIKE 'Plugins/%'");
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
        if ($name !== "event") {
            return;
        }

        $component = $attributes["component"] ?? null;
        if (!$component) {
            $component = $this->component;
        }
        $q = "INSERT INTO il_event_handling (component, type, id) VALUES (" .
            $this->db->quote($component, "text") . "," .
            $this->db->quote($attributes["type"], "text") . "," .
            $this->db->quote($attributes["id"], "text") . ")";
        $this->db->manipulate($q);
    }

    public function endTag(string $name) : void
    {
    }
}
