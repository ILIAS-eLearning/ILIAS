<?php declare(strict_types=1);

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
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilAccessRBACOperationClonedObjective implements Setup\Objective
{
    protected string $type;
    protected int $src_id;
    protected int $dest_id;

    public function __construct(string $type, int $src_id, int $dest_id)
    {
        $this->type = $type;
        $this->src_id = $src_id;
        $this->dest_id = $dest_id;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Clone rbac operation from $this->src_id to $this->dest_id";
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

        $sql =
            "SELECT rpa.rol_id, rpa.ops_id, rpa.ref_id" . PHP_EOL
            . "FROM rbac_pa rpa" . PHP_EOL
            . "JOIN object_reference ref ON (ref.ref_id = rpa.ref_id)" . PHP_EOL
            . "JOIN object_data od ON (od.obj_id = ref.obj_id AND od.type = " . $db->quote($this->type, "text") . ")" . PHP_EOL
            . "WHERE (" . $db->like("ops_id", "text", "%i:" . $this->src_id . "%") . PHP_EOL
            . "OR " . $db->like("ops_id", "text", "%:\"" . $this->src_id . "\";%") . ")" . PHP_EOL
        ;

        $result = $db->query($sql);

        while ($row = $db->fetchAssoc($result)) {
            $ops = unserialize($row["ops_id"]);
            // the query above could match by array KEY, we need extra checks
            if (in_array($this->src_id, $ops) && !in_array($this->dest_id, $ops)) {
                $ops[] = $this->dest_id;

                $sql =
                    "UPDATE rbac_pa" . PHP_EOL
                    . "SET ops_id = " . $db->quote(serialize($ops), "text") . PHP_EOL
                    . "WHERE rol_id = " . $db->quote($row["rol_id"], "integer") . PHP_EOL
                    . "AND ref_id = " . $db->quote($row["ref_id"], "integer") . PHP_EOL
                ;

                $db->manipulate($sql);
            }
        }

        // rbac_templates
        $tmp = array();
        $sql =
            "SELECT rol_id, parent, ops_id" . PHP_EOL
            . "FROM rbac_templates" . PHP_EOL
            . "WHERE type = " . $db->quote($this->type, "text") . PHP_EOL
            . "AND (ops_id = " . $db->quote($this->src_id, "integer") . PHP_EOL
            . "OR ops_id = " . $db->quote($this->dest_id, "integer") . ")" . PHP_EOL
        ;

        $result = $db->query($sql);

        while ($row = $db->fetchAssoc($result)) {
            $tmp[$row["rol_id"]][$row["parent"]][] = $row["ops_id"];
        }

        foreach ($tmp as $role_id => $parents) {
            foreach ($parents as $parent_id => $ops_ids) {
                // only if the target op is missing
                if (sizeof($ops_ids) < 2 && in_array($this->src_id, $ops_ids)) {
                    $values = [
                        "rol_id" => ["integer", $role_id],
                        "type" => ["text", $this->type],
                        "ops_id" => ["integer", $this->dest_id],
                        "parent" => ["integer", $parent_id]
                    ];

                    $db->insert("rbac_templates", $values);
                }
            }
        }

        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        return true;
    }
}
