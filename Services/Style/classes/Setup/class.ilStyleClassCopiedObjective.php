<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilStyleClassCopiedObjective implements Setup\Objective
{
    protected string $orig_class;
    protected string $class;
    protected string $type;
    protected string $tag;
    protected int $hide;

    public function __construct(string $orig_class, string $class, string $type, string $tag, int $hide = 0)
    {
        $this->orig_class = $orig_class;
        $this->class = $class;
        $this->type = $type;
        $this->tag = $tag;
        $this->hide = $hide;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Copy style class";
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

                $sql =
                    "SELECT id, style_id, tag, class, parameter, value, type, mq_id, custom" . PHP_EOL
                    . "FROM style_parameter" . PHP_EOL
                    . "WHERE style_id = " . $db->quote($row["obj_id"], "integer") . PHP_EOL
                    . "AND type = " . $db->quote($this->type, "text") . PHP_EOL
                    . "AND class = " . $db->quote($this->orig_class, "text") . PHP_EOL
                    . "AND tag = " . $db->quote($this->tag, "text") . PHP_EOL
                ;

                $res = $db->query($sql);

                while ($row_2 = $db->fetchAssoc($res)) {
                    $spid = $db->nextId("style_parameter");
                    $values = [
                        "id" => ["integer", $spid],
                        "style_id" => ["integer", $row["obj_id"]],
                        "tag" => ["text", $this->tag],
                        "class" => ["text", $this->class],
                        "parameter" => ["text", $row_2["parameter"]],
                        "value" => ["text", $row_2["value"]],
                        "type" => ["text", $row_2["type"]]
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

        if ($db->numRows($result) == 0) {
            return false;
        }

        while ($row = $db->fetchAssoc($result)) {
            $sql =
                "SELECT style_id, type, characteristic, hide" . PHP_EOL
                . "FROM style_char" . PHP_EOL
                . "WHERE style_id = " . $db->quote($row["obj_id"], "integer") . PHP_EOL
                . "AND characteristic = " . $db->quote($this->class, "text") . PHP_EOL
                . "AND type = " . $db->quote($this->type, "text") . PHP_EOL
            ;

            $res = $db->query($sql);

            if ($db->numRows($res)) {
                return false;
            }
        }
        return true;
    }
}
