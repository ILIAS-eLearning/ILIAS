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

namespace ILIAS\Container\Setup;

use ILIAS\Setup\Environment;

class WebFeedCreationDeletedObjective extends \ilAccessRBACOperationDeletedObjective
{
    protected string $type;
    protected string $ops_name;

    public function __construct(string $type, string $ops_name)
    {
        $this->type = $type;
        $this->ops_name = $ops_name;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class . $this->type . $this->ops_name);
    }

    public function getLabel(): string
    {
        return "Delete webfeed creation permissions $this->type and name $this->ops_name";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $set = $db->queryF(
            "SELECT * FROM rbac_operations " .
            " WHERE operation = %s ",
            ["text"],
            [$this->ops_name]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $this->ops_id = (int) $rec["ops_id"];
            if ($this->ops_id > 0) {

                $set2 = $db->query(
                    "SELECT obj_id FROM object_data"
                    . " WHERE type = 'typ'"
                    . " AND title = " . $db->quote($this->type, 'text')
                )
                ;
                if ($rec2 = $db->fetchAssoc($set2)) {
                    $type_id = (int) $rec2["obj_id"];
                    if ($type_id > 0) {

                        $sql =
                            "DELETE FROM rbac_ta" . PHP_EOL
                            . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
                            . "AND ops_id = " . $db->quote($this->ops_id, "integer") . PHP_EOL
                        ;

                        $db->manipulate($sql);

                        $sql =
                            "DELETE FROM rbac_templates" . PHP_EOL
                            . " WHERE type = " . $db->quote($this->type, "text") . PHP_EOL
                            . " AND ops_id = " . $db->quote($this->ops_id, "integer") . PHP_EOL
                        ;

                        $db->manipulate($sql);
                    }
                }
            }
        }
        if ($this->type === "fold") {   // fold is the last one, we delete rbac_operation here
            $db->manipulateF(
                "DELETE FROM rbac_operations WHERE " .
                " operation = %s",
                ["text"],
                [$this->ops_name]
            );
        }
        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $set = $db->queryF(
            "SELECT * FROM rbac_operations " .
            " WHERE operation = %s ",
            ["text"],
            [$this->ops_name]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

}
