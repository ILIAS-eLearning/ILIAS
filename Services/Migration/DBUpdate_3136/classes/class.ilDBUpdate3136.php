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

/**
 * Update class for step 3136
 *
 * @author alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesMigration
 */
class ilDBUpdate3136
{
    /**
     * Create style class GlossaryLink, link, IntLink
     *
     * @deprecated use Services/Style/classes/Setup/class.ilStyleClassCopiedObjective.php instead
     */
    public static function copyStyleClass(
        string $orig_class,
        string $class,
        string $type,
        string $tag,
        int $hide = 0
    ): void {
        global $ilDB;
        $db = $ilDB;

        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "WHERE type = 'sty'" . PHP_EOL
        ;
        $set = $db->query($sql);

        while ($row = $db->fetchAssoc($set)) {
            $sql =
                "SELECT style_id, type, characteristic, hide" . PHP_EOL
                . "FROM style_char" . PHP_EOL
                . "WHERE style_id = " . $db->quote($row["obj_id"], "integer") . PHP_EOL
                . "AND characteristic = " . $db->quote($class, "text") . PHP_EOL
                . "AND type = " . $db->quote($type, "text") . PHP_EOL
            ;
            $res = $db->query($sql);

            if (!$db->fetchAssoc($res)) {
                $values = [
                    "style_id" => ["integer", $row["obj_id"]],
                    "type" => ["text", $type],
                    "characteristic" => ["text", $class],
                    "hide" => ["integer", $hide]
                ];
                $db->insert("style_char", $values);

                $sql =
                    "SELECT id, style_id, tag, class, parameter, value, type, mq_id, custom" . PHP_EOL
                    . "FROM style_parameter" . PHP_EOL
                    . "WHERE style_id = " . $db->quote($row["obj_id"], "integer") . PHP_EOL
                    . "AND type = " . $db->quote($type, "text") . PHP_EOL
                    . "AND class = " . $db->quote($orig_class, "text") . PHP_EOL
                    . "AND tag = " . $db->quote($tag, "text") . PHP_EOL
                ;

                $res = $db->query($sql);

                while ($row_2 = $db->fetchAssoc($res)) {
                    $spid = $db->nextId("style_parameter");
                    $values = [
                        "id" => ["integer", $spid],
                        "style_id" => ["integer", $row["obj_id"]],
                        "tag" => ["text", $tag],
                        "class" => ["text", $class],
                        "parameter" => ["text", $row_2["parameter"]],
                        "value" => ["text", $row_2["value"]],
                        "type" => ["text", $row_2["type"]]
                    ];
                    $db->insert("style_parameter", $values);
                }
            }
        }
    }

    /**
     * Add style class
     *
     * @deprecated use Services/Style/classes/Setup/class.ilStyleClassAddedObjective.php instead
     */
    public static function addStyleClass(
        string $class,
        string $type,
        string $tag,
        array $parameters = [],
        int $hide = 0
    ): void {
        global $ilDB;
        $db = $ilDB;

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
                . "AND characteristic = " . $db->quote($class, "text") . PHP_EOL
                . "AND type = " . $db->quote($type, "text") . PHP_EOL;
            $res = $db->query($sql);

            if (!$db->fetchAssoc($res)) {
                $values = [
                    "style_id" => ["integer", $row["obj_id"]],
                    "type" => ["text", $type],
                    "characteristic" => ["text", $class],
                    "hide" => ["integer", $hide]
                ];
                $db->insert("style_char", $values);

                foreach ($parameters as $k => $v) {
                    $spid = $db->nextId("style_parameter");
                    $values = [
                        "id" => ["integer", $spid],
                        "style_id" => ["integer", $row["obj_id"]],
                        "tag" => ["text", $tag],
                        "class" => ["text", $class],
                        "parameter" => ["text", $k],
                        "value" => ["text", $v],
                        "type" => ["text", $type]
                    ];
                    $db->insert("style_parameter", $values);
                }
            }
        }
    }
}
