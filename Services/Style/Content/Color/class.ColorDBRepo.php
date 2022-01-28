<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ColorDBRepo
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
     * @param \ilDBInterface|null $db
     * @param DataFactory|null   $factory
     */
    public function __construct(
        \ilDBInterface $db,
        DataFactory $factory
    ) {
        $this->db = $db;
        $this->factory = $factory;
    }

    /**
     * Add color
     * @param int    $style_id
     * @param string $a_name
     * @param string $a_code
     */
    public function addColor(
        int $style_id,
        string $a_name,
        string $a_code
    ) : void {
        $db = $this->db;

        $db->insert("style_color", [
            "style_id" => ["integer", $style_id],
            "color_name" => ["text", $a_name],
            "color_code" => ["text", $a_code]
        ]);
    }

    /**
     * Check whether color exists
     * @param int    $style_id
     * @param string $a_color_name
     * @return bool
     */
    public function colorExists(
        int $style_id,
        string $a_color_name
    ) : bool {
        $db = $this->db;

        $set = $db->query("SELECT * FROM style_color WHERE " .
            "style_id = " . $db->quote($style_id, "integer") . " AND " .
            "color_name = " . $db->quote($a_color_name, "text"));
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Update color
     * @param int    $style_id
     * @param string $name
     * @param string $new_name
     * @param string $code
     */
    public function updateColor(
        int $style_id,
        string $name,
        string $new_name,
        string $code
    ) : void {
        $db = $this->db;

        $db->update(
            "style_color",
            [
            "color_name" => ["text", $new_name],
            "color_code" => ["text", $code]
        ],
            [    // where
                "style_id" => ["integer", $style_id],
                "color_name" => ["text", $name]
            ]
        );
    }
}
