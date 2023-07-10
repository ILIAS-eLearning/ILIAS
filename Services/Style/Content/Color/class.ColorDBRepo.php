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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ColorDBRepo
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

    public function addColor(
        int $style_id,
        string $a_name,
        string $a_code
    ): void {
        $db = $this->db;

        $db->insert("style_color", [
            "style_id" => ["integer", $style_id],
            "color_name" => ["text", $a_name],
            "color_code" => ["text", $a_code]
        ]);
    }

    /**
     * Check whether color exists
     */
    public function colorExists(
        int $style_id,
        string $a_color_name
    ): bool {
        $db = $this->db;

        $set = $db->query("SELECT * FROM style_color WHERE " .
            "style_id = " . $db->quote($style_id, "integer") . " AND " .
            "color_name = " . $db->quote($a_color_name, "text"));
        if ($db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function updateColor(
        int $style_id,
        string $name,
        string $new_name,
        string $code
    ): void {
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
