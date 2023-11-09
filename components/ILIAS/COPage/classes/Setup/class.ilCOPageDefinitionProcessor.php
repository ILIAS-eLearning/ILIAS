<?php

declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilCOPageDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component = null;
    protected ?string $type = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge(): void
    {
        $this->db->manipulate("DELETE FROM copg_pc_def");
        $this->db->manipulate("DELETE FROM copg_pobj_def");
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component = $component;
        $this->type = $type;
    }

    public function endComponent(string $component, string $type): void
    {
        $this->component = null;
        $this->type = null;
    }

    public function beginTag(string $name, array $attributes): void
    {
        switch ($name) {
            case "pagecontent":
                $this->db->manipulate("INSERT INTO copg_pc_def " .
                    "(pc_type, name, component, directory, int_links, style_classes, xsl, def_enabled, top_item, order_nr) VALUES (" .
                    $this->db->quote($attributes["pc_type"], "text") . "," .
                    $this->db->quote($attributes["name"], "text") . "," .
                    $this->db->quote($this->type . "/" . $this->component, "text") . "," .
                    $this->db->quote($attributes["directory"], "text") . "," .
                    $this->db->quote($attributes["int_links"], "integer") . "," .
                    $this->db->quote($attributes["style_classes"], "integer") . "," .
                    $this->db->quote($attributes["xsl"], "integer") . "," .
                    $this->db->quote($attributes["def_enabled"], "integer") . "," .
                    $this->db->quote($attributes["top_item"], "integer") . "," .
                    $this->db->quote($attributes["order_nr"], "integer") .
                    ")");
                break;

            case "pageobject":
                $this->db->manipulate("INSERT INTO copg_pobj_def " .
                    "(parent_type, class_name, component, directory) VALUES (" .
                    $this->db->quote($attributes["parent_type"], "text") . "," .
                    $this->db->quote($attributes["class_name"], "text") . "," .
                    $this->db->quote($this->type . "/" . $this->component, "text") . "," .
                    $this->db->quote($attributes["directory"], "text") .
                    ")");
                break;
        }
    }

    public function endTag(string $name): void
    {
    }
}
