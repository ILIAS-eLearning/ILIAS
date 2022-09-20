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

namespace ILIAS\Style\Content;

use ilDBInterface;
use ilObjStyleSheet;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicDBRepo
{
    protected ilDBInterface $db;
    protected InternalDataService $factory;

    public function __construct(
        ilDBInterface $db,
        InternalDataService $factory
    ) {
        $this->db = $db;
        $this->factory = $factory;
    }

    public function addCharacteristic(
        int $style_id,
        string $type,
        string $char,
        bool $hidden = false,
        int $order_nr = 0,
        bool $outdated = false
    ): void {
        $db = $this->db;

        $db->insert("style_char", [
            "style_id" => ["integer", $style_id],
            "type" => ["text", $type],
            "characteristic" => ["text", $char],
            "hide" => ["integer", $hidden],
            "order_nr" => ["integer", $order_nr],
            "outdated" => ["integer", $outdated]
        ]);
    }

    public function exists(
        int $style_id,
        string $type,
        string $char
    ): bool {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_char " .
            " WHERE style_id = %s AND type = %s AND characteristic = %s",
            ["integer", "text", "text"],
            [$style_id, $type, $char]
        );
        if ($db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function getByKey(
        int $style_id,
        string $type,
        string $characteristic
    ): ?Characteristic {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_char " .
            " WHERE style_id = %s AND type = %s AND characteristic = %s ",
            ["integer", "text", "text"],
            [$style_id, $type, $characteristic]
        );
        if ($rec = $db->fetchAssoc($set)) {
            $set2 = $db->queryF(
                "SELECT * FROM style_char_title " .
                " WHERE style_id = %s AND type = %s AND characteristic = %s ",
                ["integer", "text", "text"],
                [$style_id, $type, $characteristic]
            );
            $titles = [];
            while ($rec2 = $db->fetchAssoc($set2)) {
                $titles[$rec2["lang"]] = $rec2["title"];
            }
            return $this->factory->characteristic(
                $type,
                $characteristic,
                (bool) $rec["hide"],
                $titles,
                $style_id,
                (int) $rec["order_nr"],
                (bool) $rec["outdated"]
            );
        }
        return null;
    }

    public function getByType(
        int $style_id,
        string $type
    ): array {
        return $this->getByTypes(
            $style_id,
            [$type]
        );
    }

    public function getByTypes(
        int $style_id,
        array $types,
        bool $include_hidden = true,
        bool $include_outdated = true
    ): array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_char " .
            " WHERE style_id = %s AND " . $db->in("type", $types, false, "text") .
            " ORDER BY order_nr, type, characteristic",
            ["integer"],
            [$style_id]
        );
        $chars = [];
        while ($rec = $db->fetchAssoc($set)) {
            if (($rec["hide"] && !$include_hidden) ||
                ($rec["outdated"] && !$include_outdated)) {
                continue;
            }

            $set2 = $db->queryF(
                "SELECT * FROM style_char_title " .
                " WHERE style_id = %s AND type = %s AND characteristic = %s ",
                ["integer", "text", "text"],
                [$style_id, $rec["type"], $rec["characteristic"]]
            );
            $titles = [];
            while ($rec2 = $db->fetchAssoc($set2)) {
                $titles[$rec2["lang"]] = $rec2["title"];
            }
            $chars[] = $this->factory->characteristic(
                $rec["type"],
                $rec["characteristic"],
                (bool) $rec["hide"],
                $titles,
                $style_id,
                (int) $rec["order_nr"],
                (bool) $rec["outdated"]
            );
        }
        return $chars;
    }

    /**
     * Get characteristics by supertype
     * @return Characteristic[]
     */
    public function getBySuperType(
        int $style_id,
        string $super_type
    ): array {
        $stypes = ilObjStyleSheet::_getStyleSuperTypes();
        $types = $stypes[$super_type];

        return $this->getByTypes(
            $style_id,
            $types
        );
    }


    /**
     * Save titles for characteristic
     */
    public function saveTitles(
        int $style_id,
        string $type,
        string $characteristic,
        array $titles
    ): void {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM style_char_title " .
            " WHERE style_id = %s AND type = %s AND characteristic = %s ",
            ["integer", "text", "text"],
            [$style_id, $type, $characteristic]
        );

        foreach ($titles as $l => $title) {
            $db->insert("style_char_title", [
                "style_id" => ["integer", $style_id],
                "type" => ["text", $type],
                "characteristic" => ["text", $characteristic],
                "lang" => ["text", $l],
                "title" => ["text", $title]
            ]);
        }
    }

    /**
     * Save characteristic hidden status
     */
    public function saveHidden(
        int $style_id,
        string $type,
        string $characteristic,
        bool $hide
    ): void {
        $db = $this->db;

        $db->update(
            "style_char",
            [
            "hide" => ["integer", $hide]
        ],
            [    // where
                "style_id" => ["integer", $style_id],
                "type" => ["text", $type],
                "characteristic" => ["text", $characteristic]
            ]
        );
    }

    /**
     * Save characteristic outdated status
     */
    public function saveOutdated(
        int $style_id,
        string $type,
        string $characteristic,
        bool $outdated
    ): void {
        $db = $this->db;

        $db->update(
            "style_char",
            [
            "outdated" => ["integer", $outdated]
        ],
            [    // where
                "style_id" => ["integer", $style_id],
                "type" => ["text", $type],
                "characteristic" => ["text", $characteristic]
            ]
        );
    }

    public function saveOrderNr(
        int $style_id,
        string $type,
        string $characteristic,
        int $order_nr
    ): void {
        $db = $this->db;

        $db->update(
            "style_char",
            [
            "order_nr" => ["integer", $order_nr]
        ],
            [    // where
                "style_id" => ["integer", $style_id],
                "type" => ["text", $type],
                "characteristic" => ["text", $characteristic]
            ]
        );
    }

    public function deleteCharacteristic(
        int $style_id,
        string $type,
        string $tag,
        string $class
    ): void {
        $db = $this->db;

        // delete characteristic record
        $db->manipulateF(
            "DELETE FROM style_char WHERE style_id = %s AND type = %s AND characteristic = %s",
            array("integer", "text", "text"),
            array($style_id, $type, $class)
        );

        // delete parameter records
        $db->manipulateF(
            "DELETE FROM style_parameter WHERE style_id = %s AND tag = %s AND type = %s AND class = %s",
            array("integer", "text", "text", "text"),
            array($style_id, $tag, $type, $class)
        );
    }

    //
    // Parameter
    //

    public function replaceParameter(
        int $style_id,
        string $a_tag,
        string $a_class,
        string $a_par,
        string $a_val,
        string $a_type,
        int $a_mq_id = 0,
        bool $a_custom = false
    ): void {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_parameter " .
            " WHERE style_id = %s AND tag = %s AND class = %s AND mq_id = %s " .
            " AND custom = %s AND type = %s AND parameter = %s ",
            ["integer", "text", "text", "integer", "integer", "text", "text"],
            [$style_id, $a_tag, $a_class, $a_mq_id, $a_custom, $a_type, $a_par]
        );

        if ($set->fetchRow()) {
            $db->update(
                "style_parameter",
                [
                "value" => ["text", $a_val]
            ],
                [    // where
                    "style_id" => ["integer", $style_id],
                    "tag" => ["text", $a_tag],
                    "class" => ["text", $a_class],
                    "mq_id" => ["integer", $a_mq_id],
                    "custom" => ["integer", $a_custom],
                    "type" => ["text", $a_type],
                    "parameter" => ["text", $a_par]
                ]
            );
        } else {
            $id = $db->nextId("style_parameter");
            $db->insert("style_parameter", [
                "id" => ["integer", $id],
                "value" => ["text", $a_val],
                "style_id" => ["integer", $style_id],
                "tag" => ["text", $a_tag],
                "class" => ["text", $a_class],
                "type" => ["text", $a_type],
                "parameter" => ["text", $a_par],
                "mq_id" => ["integer", $a_mq_id],
                "custom" => ["integer", $a_custom]
            ]);
        }
    }

    public function deleteParameter(
        int $style_id,
        string $tag,
        string $class,
        string $par,
        string $type,
        int $mq_id = 0,
        bool $custom = false
    ): void {
        $db = $this->db;

        $q = "DELETE FROM style_parameter WHERE " .
            " style_id = " . $db->quote($style_id, "integer") . " AND " .
            " tag = " . $db->quote($tag, "text") . " AND " .
            " class = " . $db->quote($class, "text") . " AND " .
            " mq_id = " . $db->quote($mq_id, "integer") . " AND " .
            " custom = " . $db->quote($custom, "integer") . " AND " .
            " " . $db->equals("type", $type, "text", true) . " AND " .
            " parameter = " . $db->quote($par, "text");

        $db->manipulate($q);
    }

    public function updateColorName(
        int $style_id,
        string $old_name,
        string $new_name
    ): void {
        if ($old_name == $new_name) {
            return;
        }

        $db = $this->db;

        $color_attributes = [
            "background-color",
            "color",
            "border-color",
            "border-top-color",
            "border-bottom-color",
            "border-left-color",
            "border-right-color",
        ];

        $set = $db->queryF(
            "SELECT * FROM style_parameter " .
            " WHERE style_id = %s AND " . $db->in("parameter", $color_attributes, false, "text"),
            ["integer"],
            [$style_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            if ($rec["value"] == "!" . $old_name ||
                is_int(strpos($rec["value"], "!" . $old_name . "("))) {
                // parameter is based on color -> rename it
                $this->replaceParameter(
                    $style_id,
                    $rec["tag"],
                    $rec["class"],
                    $rec["parameter"],
                    str_replace($old_name, $new_name, $rec["value"]),
                    $rec["type"],
                    $rec["mq_id"],
                    $rec["custom"]
                );
            }
        }
    }
}
