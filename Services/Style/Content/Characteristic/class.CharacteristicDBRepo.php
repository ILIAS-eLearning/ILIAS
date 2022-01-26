<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicDBRepo
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var DataFactory
     */
    protected $factory;

    /**
     * Constructor
     * @param \ilDBInterface $db
     * @param DataFactory   $factory
     */
    public function __construct(
        \ilDBInterface $db,
        DataFactory $factory
    ) {
        $this->db = $db;
        $this->factory = $factory;
    }

    /**
     * Add characteristic
     * @param int    $style_id
     * @param string $type
     * @param string $char
     * @param bool   $hidden
     * @param int   $order_nr
     * @param bool   $outdated
     */
    public function addCharacteristic(
        int $style_id,
        string $type,
        string $char,
        bool $hidden = false,
        int $order_nr = 0,
        bool $outdated = false
    ) {
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

    /**
     * Check if characteristic exists
     * @param int    $style_id
     * @param string $type
     * @param string $char
     */
    public function exists(
        int $style_id,
        string $type,
        string $char
    ) : bool {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_char " .
            " WHERE style_id = %s AND type = %s AND characteristic = %s",
            ["integer", "text", "text"],
            [$style_id, $type, $char]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }


    /**
     * Get characteristic by key
     * @param int    $style_id
     * @param string $type
     * @param string $characteristic
     * @return Characteristic|null
     */
    public function getByKey(
        int $style_id,
        string $type,
        string $characteristic
    ) : ?Characteristic {
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
                $rec["hide"],
                $titles,
                $style_id,
                $rec["order_nr"],
                $rec["outdated"]
            );
        }
        return null;
    }

    /**
     * Get characteristics by type
     * @param int    $style_id
     * @param string $type
     * @return Characteristic[]
     */
    public function getByType(
        int $style_id,
        string $type
    ) : array {
        return $this->getByTypes(
            $style_id,
            [$type]
        );
    }

    /**
     * Get characteristics by types
     * @param int   $style_id
     * @param array $types
     * @param bool  $include_hidden
     * @param bool  $include_outdated
     * @return array
     */
    public function getByTypes(
        int $style_id,
        array $types,
        bool $include_hidden = true,
        bool $include_outdated = true
    ) : array {
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
                $rec["hide"],
                $titles,
                $style_id,
                $rec["order_nr"],
                $rec["outdated"]
            );
        }
        return $chars;
    }

    /**
     * Get characteristics by supertype
     * @param int    $style_id
     * @param string $super_type
     * @return Characteristic[]
     */
    public function getBySuperType(
        int $style_id,
        string $super_type
    ) : array {
        $stypes = \ilObjStyleSheet::_getStyleSuperTypes();
        $types = $stypes[$super_type];

        return $this->getByTypes(
            $style_id,
            $types
        );
    }


    /**
     * Save titles for characteristic
     *
     * @param int    $style_id
     * @param string $type
     * @param string $characteristic
     * @param array  $titles
     */
    public function saveTitles(
        int $style_id,
        string $type,
        string $characteristic,
        array $titles
    ) : void {
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
     * @param int    $style_id
     * @param string $type
     * @param string $characteristic
     * @param bool   $hide
     */
    public function saveHidden(
        int $style_id,
        string $type,
        string $characteristic,
        bool $hide
    ) : void {
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
     * @param int    $style_id
     * @param string $type
     * @param string $characteristic
     * @param bool   $outdated
     */
    public function saveOutdated(
        int $style_id,
        string $type,
        string $characteristic,
        bool $outdated
    ) : void {
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

    /**
     * Save order nr
     * @param int    $style_id
     * @param string $type
     * @param string $characteristic
     * @param int   $order_nr
     */
    public function saveOrderNr(
        int $style_id,
        string $type,
        string $characteristic,
        int $order_nr
    ) : void {
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

    /**
     * Delete Characteristic
     * @param int    $style_id
     * @param string $type
     * @param string $tag
     * @param string $class
     */
    public function deleteCharacteristic(
        int $style_id,
        string $type,
        string $tag,
        string $class
    ) : void {
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

    /**
     * Replace a parameter
     *
     * @param int    $style_id
     * @param string $a_tag
     * @param string $a_class
     * @param string $a_par
     * @param string $a_val
     * @param string $a_type
     * @param int    $a_mq_id
     * @param bool   $a_custom
     */
    public function replaceParameter(
        int $style_id,
        string $a_tag,
        string $a_class,
        string $a_par,
        string $a_val,
        string $a_type,
        int $a_mq_id = 0,
        bool $a_custom = false
    ) : void {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_parameter " .
            " WHERE style_id = %s AND tag = %s AND class = %s AND mq_id = %s " .
            " AND custom = %s AND type = %s AND parameter = %s ",
            ["integer", "text", "text", "integer", "integer", "text", "text"],
            [$style_id, $a_tag, $a_class, $a_mq_id, $a_custom, $a_type, $a_par]
        );

        if ($rec = $set->fetchRow()) {
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

    /**
     * Delete a parameter
     *
     * @param int    $style_id
     * @param string $tag
     * @param string $class
     * @param string $par
     * @param string $type
     * @param int    $mq_id
     * @param bool   $custom
     */
    public function deleteParameter(
        int $style_id,
        string $tag,
        string $class,
        string $par,
        string $type,
        int $mq_id = 0,
        bool $custom = false
    ) : void {
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

    /**
     * Update color name
     * @param int    $style_id
     * @param string $old_name
     * @param string $new_name
     */
    public function updateColorName(
        int $style_id,
        string $old_name,
        string $new_name
    ) : void {
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
