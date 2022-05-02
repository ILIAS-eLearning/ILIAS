<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\DI;

class ilAccessCustomRBACOperationAddedObjective implements Setup\Objective
{
    protected string $id;
    protected string $title;
    protected string $class;
    protected int $pos;
    protected array $types;

    public function __construct(string $id, string $title, string $class, int $pos, array $types = [])
    {
        $this->id = $id;
        $this->title = $title;
        $this->class = $class;
        $this->pos = $pos;
        $this->types = $types;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        $types = implode(",", $this->types);
        return "Add custom rbac operation (id=$this->id;title=$this->title;class=$this->class;pos=$this->pos;types=($types))";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment) : Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $dic = $this->initEnvironment($environment);

        if ($this->class == "create") {
            $this->pos = 9999;
        }

        $ops_id = ilRbacReview::_getCustomRBACOperationId($this->id);
        if (is_null($ops_id)) {
            $ops_id = $db->nextId("rbac_operations");

            $values = [
                'ops_id' => ['integer', $ops_id],
                'operation' => ['text', $this->id],
                'description' => ['text', $this->title],
                'class' => ['text', $this->class],
                'op_order' => ['integer', $this->pos]
            ];

            $db->insert("rbac_operations", $values);
        }

        foreach ($this->types as $type) {
            $type_id = ilObject::_getObjectTypeIdByTitle($type);
            if (!$type_id) {
                $type_id = $db->nextId('object_data');

                $fields = [
                    'obj_id' => ['integer', $type_id],
                    'type' => ['text', 'typ'],
                    'title' => ['text', $type],
                    'description' => ['text', $this->title],
                    'owner' => ['integer', -1],
                    'create_date' => ['timestamp', $db->now()],
                    'last_update' => ['timestamp', $db->now()]
                ];
                $db->insert('object_data', $fields);
            }

            $sql =
                "SELECT typ_id, ops_id " . PHP_EOL
                . "FROM rbac_ta" . PHP_EOL
                . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
                . "AND ops_id = " . $db->quote($ops_id, 'integer') . PHP_EOL
            ;

            $result = $db->query($sql);
            if ($db->numRows($result)) {
                continue;
            }

            $values = [
                "typ_id" => ["integer", $type_id],
                "ops_id" => ["integer", $ops_id]
            ];

            $db->insert("rbac_ta", $values);
        }

        $GLOBALS["DIC"] = $dic;
        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $dic = $this->initEnvironment($environment);

        $ops_id = ilRbacReview::_getCustomRBACOperationId($this->id);
        if (!$ops_id) {
            return true;
        }

        foreach ($this->types as $key => $type) {
            $type_id = ilObject::_getObjectTypeIdByTitle($type);
            if (is_null($type_id)) {
                return true;
            }

            $sql =
                "SELECT typ_id, ops_id " . PHP_EOL
                . "FROM rbac_ta" . PHP_EOL
                . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
                . "AND ops_id = " . $db->quote($ops_id, 'integer') . PHP_EOL
            ;

            $result = $db->query($sql);
            if ($db->numRows($result)) {
                unset($this->types[$key]);
            }
        }

        $GLOBALS["DIC"] = $dic;

        return count($this->types) && in_array($this->class, ['create', 'object', 'general']);
    }

    protected function initEnvironment(Setup\Environment $environment)
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        // ATTENTION: This is a total abomination. It only exists to allow various
        // subcomponents of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = [];
        if (isset($GLOBALS["DIC"])) {
            $DIC = $GLOBALS["DIC"];
        }
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;

        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        return $DIC;
    }
}
