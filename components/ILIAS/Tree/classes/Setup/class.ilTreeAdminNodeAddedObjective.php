<?php

declare(strict_types=1);

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
 *********************************************************************/

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilTreeAdminNodeAddedObjective implements Setup\Objective
{
    protected const RBAC_OP_EDIT_PERMISSIONS = 1;
    protected const RBAC_OP_VISIBLE = 2;
    protected const RBAC_OP_READ = 3;
    protected const RBAC_OP_WRITE = 4;

    protected array $rbac_ops = [
        self::RBAC_OP_EDIT_PERMISSIONS,
        self::RBAC_OP_VISIBLE,
        self::RBAC_OP_READ,
        self::RBAC_OP_WRITE
    ];

    protected string $type;
    protected string $title;
    protected string $parent_type;

    public function __construct(string $type, string $title, string $parent_type = "")
    {
        $this->type = $type;
        $this->title = $title;
        $this->parent_type = $parent_type;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class . "::" . $this->type);
    }

    public function getLabel(): string
    {
        return "Add new admin node to tree (type=$this->type;title=$this->title)";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        global $DIC;
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $DIC['ilDB'] = $DIC['ilDB'] ?? $db;

        if (!defined("ROOT_FOLDER_ID")) {
            define("ROOT_FOLDER_ID", (int) $client_ini->readVariable("system", "ROOT_FOLDER_ID"));
        }
        if (!defined("SYSTEM_FOLDER_ID")) {
            define("SYSTEM_FOLDER_ID", $client_ini->readVariable("system", "SYSTEM_FOLDER_ID"));
        }
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }

        $obj_type_id = $db->nextId("object_data");
        $values = [
            'obj_id' => ['integer', $obj_type_id],
            'type' => ['text', 'typ'],
            'title' => ['text', $this->type],
            'description' => ['text', $this->title],
            'owner' => ['integer', -1],
            'create_date' => ['timestamp', date("Y-m-d H:i:s")],
            'last_update' => ['timestamp', date("Y-m-d H:i:s")]
        ];
        $db->insert("object_data", $values);

        $obj_id = $db->nextId("object_data");
        $values = [
            'obj_id' => ['integer', $obj_id],
            'type' => ['text', $this->type],
            'title' => ['text', $this->title],
            'description' => ['text', $this->title],
            'owner' => ['integer', -1],
            'create_date' => ['timestamp', date("Y-m-d H:i:s")],
            'last_update' => ['timestamp', date("Y-m-d H:i:s")]
        ];
        $db->insert("object_data", $values);

        $ref_id = $db->nextId("object_reference");
        $values = [
            "obj_id" => ["integer", $obj_id],
            "ref_id" => ["integer", $ref_id]
        ];
        $db->insert("object_reference", $values);

        $tree = new ilTree(
            ROOT_FOLDER_ID,
            0,
            $db
        );
        if ($this->parent_type) {
            $set = $db->queryF(
                "SELECT * FROM object_data " .
                " WHERE type = %s ",
                ["text"],
                [$this->parent_type]
            );
            $rec = $db->fetchAssoc($set);

            $set = $db->queryF(
                "SELECT * FROM object_reference " .
                " WHERE obj_id = %s ",
                ["integer"],
                [$rec["obj_id"]]
            );
            $rec = $db->fetchAssoc($set);
            $parent_type_ref_id = $rec["ref_id"];

            $tree->insertNode((int) $ref_id, (int) $parent_type_ref_id);
        } else {
            $tree->insertNode((int) $ref_id, (int) SYSTEM_FOLDER_ID);
        }

        foreach ($this->rbac_ops as $ops_id) {
            if (ilRbacReview::_isRBACOperation($obj_type_id, $ops_id, $db)) {
                continue;
            }
            $values = [
                "typ_id" => ["integer", $obj_type_id],
                "ops_id" => ["integer", $ops_id]
            ];
            $db->insert("rbac_ta", $values);
        }
        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        return !((bool) ilObject::_getObjectTypeIdByTitle($this->type, $db));
    }
}
