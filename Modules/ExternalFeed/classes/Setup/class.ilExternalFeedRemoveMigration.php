<?php

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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
final class ilExternalFeedRemoveMigration implements Setup\Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "Remove external feeds from repository.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        /** @var ilDBInterface $db */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->db = $db;
    }

    protected function log(string $str): void
    {
        echo "\n" . $str;
    }

    public function step(Environment $environment): void
    {
        $db = $this->db;

        $ref_ids = [];
        $obj_ids = [];

        $set = $db->queryF(
            "SELECT * FROM object_data d JOIN object_reference r ON (d.obj_id = r.obj_id) " .
            " WHERE d.type = %s ",
            ["text"],
            ["feed"]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $this->log("Found title: " . $rec["title"] . ", ref id: " . $rec["ref_id"]);
            $ref_ids[$rec["ref_id"]] = $rec["ref_id"];
            $obj_ids[$rec["obj_id"]] = $rec["obj_id"];
        }

        // local roles of feed objects
        $set = $db->queryF(
            "SELECT * FROM rbac_fa " .
            " WHERE " . $db->in("parent", $ref_ids, false, "integer"),
            [],
            []
        );
        $local_roles = [];
        while ($rec = $db->fetchAssoc($set)) {
            $local_roles[$rec["rol_id"]] = $rec["rol_id"];
        }

        // typ
        $set = $db->queryF(
            "SELECT * FROM object_data " .
            " WHERE type = %s AND title = %s",
            ["text", "text"],
            ["typ", "feed"]
        );
        $typ_id = 0;
        if ($rec = $db->fetchAssoc($set)) {
            $typ_id = $rec["obj_id"];
        }

        // create ops id
        $set = $db->queryF(
            "SELECT * FROM rbac_operations " .
            " WHERE operation = %s",
            ["text"],
            ["create_feed"]
        );
        $create_ops_id = 0;
        if ($rec = $db->fetchAssoc($set)) {
            $create_ops_id = $rec["ops_id"];
        }


        // ...tree
        $this->log("Delete tree nodes of feed objects.");
        $this->manipulate(
            "DELETE FROM tree WHERE " .
            " " . $db->in("child", $ref_ids, false, "integer")
        );

        // ...object_data (feed objects)
        $this->log("Delete object_data entries of feed objects.");
        $this->manipulate(
            "DELETE FROM object_data WHERE " .
            " " . $db->in("obj_id", $obj_ids, false, "integer")
        );
        // ...object_data (roles)
        $this->log("Delete object_data entries of local roles of feed objects.");
        $this->manipulate(
            "DELETE FROM object_data WHERE " .
            " " . $db->in("obj_id", $local_roles, false, "integer")
        );
        // ...object_data (type)
        $this->log("Delete object_data entry of feed type.");
        $this->manipulate(
            "DELETE FROM object_data WHERE " .
            " obj_id = " . $db->quote($typ_id, "integer")
        );

        // ...object_description (feed objects)
        $this->log("Delete object_description entries of feed objects.");
        $this->manipulate(
            "DELETE FROM object_description WHERE " .
            " " . $db->in("obj_id", $obj_ids, false, "integer")
        );

        //... role data of local roles
        $this->log("Delete role_data entries of local roles of feed objects.");
        $this->manipulate(
            "DELETE FROM role_data WHERE " .
            " " . $db->in("role_id", $local_roles, false, "integer")
        );

        // ...object_reference
        $this->log("Delete object_reference entries of feed objects.");
        $this->manipulate(
            "DELETE FROM object_reference WHERE " .
            " " . $db->in("ref_id", $ref_ids, false, "integer")
        );

        // ...rbac_pa (permissions on feeds)
        $this->log("Delete permissions of feed objects.");
        $this->manipulate(
            "DELETE FROM rbac_pa WHERE " .
            " " . $db->in("ref_id", $ref_ids, false, "integer")
        );

        // ...rbac_fa (local roles of feeds)
        $this->log("Delete local roles of feed objects.");
        $this->manipulate(
            "DELETE FROM rbac_fa WHERE " .
            " " . $db->in("rol_id", $local_roles, false, "integer")
        );

        // ...rbac_ta (operations per type)
        $this->log("Delete operations of feed type.");
        $this->manipulate(
            "DELETE FROM rbac_ta WHERE " .
            " typ_id = " . $db->quote($typ_id, "integer")
        );

        // ...rbac_templates (template for feed type)
        $this->log("Delete permission templates of feed type.");
        $this->manipulate(
            "DELETE FROM rbac_templates WHERE " .
            " type = " . $db->quote("feed", "text")
        );

        // ...rbac_templates (create_feed operation)
        $this->log("Delete create_feed operation from permission templates.");
        $this->manipulate(
            "DELETE FROM rbac_templates WHERE " .
            " ops_id = " . $db->quote($create_ops_id, "integer")
        );


        // ...conditions
        $this->log("Delete conditions of feed objects.");
        $this->manipulate(
            "DELETE FROM conditions WHERE " .
            " " . $db->in("target_ref_id", $ref_ids, false, "integer") .
            " OR " . $db->in("trigger_ref_id", $ref_ids, false, "integer")
        );

        // ...il_external_feed_block
        $this->log("Empty il_external_feed_block.");
        $this->manipulate("DELETE FROM il_external_feed_block");

        // ...il_custom_block
        $this->log("Delete il_custom_block entries of type 'feed'.");
        $this->manipulate("DELETE FROM il_custom_block WHERE type = " . $db->quote("feed", "text"));
    }

    protected function manipulate(string $query): void
    {
        $db = $this->db;
        $db->manipulate($query);
        $this->log($query);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM object_data d JOIN object_reference r ON (d.obj_id = r.obj_id) " .
            " WHERE d.type = %s ",
            ["text"],
            ["feed"]
        );
        $obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $obj_ids[$rec["obj_id"]] = $rec["obj_id"];
        }

        if (count($obj_ids) > 0) {
            return 1;
        }
        return 0;
    }
}
