<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilStyleClassAddedObjective implements Setup\Objective
{
    protected string $class;
    protected string $type;
    protected string $tag;
    protected array $parameters;
    protected int $hide;

    public function __construct(string $class, string $type, string $tag, array $parameters = [], int $hide = 0)
    {
        $this->class = $class;
        $this->type = $type;
        $this->tag = $tag;
        $this->parameters = $parameters;
        $this->hide = $hide;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Add style class";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "WHERE type = 'sty'" . PHP_EOL
        ;
        $result = $db->query($sql);

        while ($row = $db->fetchAssoc($result)) {
            $sql =
                "SELECT style_id, type, characteristic, hide" . PHP_EOL
                . "FROM style_char" . PHP_EOL
                . "WHERE style_id = " . $db->quote($row["obj_id"], "integer") . PHP_EOL
                . "AND characteristic = " . $db->quote($this->class, "text") . PHP_EOL
                . "AND type = " . $db->quote($this->type, "text") . PHP_EOL
            ;
            $res = $db->query($sql);

            if (!$db->fetchAssoc($res)) {
                $values = [
                    "style_id" => ["integer", $row["obj_id"]],
                    "type" => ["text", $this->type],
                    "characteristic" => ["text", $this->class],
                    "hide" => ["integer", $this->hide]
                ];
                $db->insert("style_char", $values);

                foreach ($this->parameters as $k => $v) {
                    $spid = $db->nextId("style_parameter");
                    $values = [
                        "id" => ["integer", $spid],
                        "style_id" => ["integer", $row["obj_id"]],
                        "tag" => ["text", $this->tag],
                        "class" => ["text", $this->class],
                        "parameter" => ["text", $k],
                        "value" => ["text", $v],
                        "type" => ["text", $this->type]
                    ];
                    $db->insert("style_parameter", $values);
                }
            }
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "WHERE type = 'sty'" . PHP_EOL
        ;

        $result = $db->query($sql);

        while ($row = $db->fetchAssoc($result)) {
            $sql =
                "SELECT style_id, type, characteristic, hide" . PHP_EOL
                . "FROM style_char" . PHP_EOL
                . "WHERE style_id = " . $db->quote($row["obj_id"], "integer") . PHP_EOL
                . "AND characteristic = " . $db->quote($this->class, "text") . PHP_EOL
                . "AND type = " . $db->quote($this->type, "text") . PHP_EOL;
            $res = $db->query($sql);

            // return true if no entry exists in style_char for obj_id from object_data
            if ($db->numRows($res) == 0) {
                return true;
            }
        }

        return false;
    }
}
