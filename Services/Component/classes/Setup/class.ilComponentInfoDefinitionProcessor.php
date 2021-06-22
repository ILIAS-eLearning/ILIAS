<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilComponentInfoDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component;
    protected ?string $type;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM il_component");
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $component;
        $this->type = $type;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
        $this->type = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name === "module") {
            $type = "Modules";
        } elseif ($name === "service") {
            $type = "Services";
        } else {
            return;
        }

        if (!isset($attributes["id"])) {
            throw new \InvalidArgumentException(
                "Expected attribute 'id' for tag '$name' in {$this->component}"
            );
        }

        if ($type !== $this->type) {
            throw new \InvalidArgumentException(
                "Type {$this->type} and tag don't match for component {$this->component}"
            );
        }

        $this->db->manipulateF(
            "INSERT INTO il_component (type, name, id) VALUES (%s,%s,%s)",
            ["text", "text", "text"],
            [$type, $this->component, $attributes["id"]]
        );
    }

    public function endTag(string $name) : void
    {
    }
}
