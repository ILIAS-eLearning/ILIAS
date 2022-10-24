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

namespace ILIAS\MediaCast\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaCastDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists('il_media_cast_data', 'autoplaymode')) {
            $db->addTableColumn('il_media_cast_data', 'autoplaymode', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 1,
                "default" => 0
            ));
        }
    }

    public function step_2(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists('il_media_cast_data', 'nr_initial_videos')) {
            $db->addTableColumn('il_media_cast_data', 'nr_initial_videos', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 1,
                "default" => 0
            ));
        }
    }

    public function step_3(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists('il_media_cast_data', 'new_items_in_lp')) {
            $db->addTableColumn('il_media_cast_data', 'new_items_in_lp', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 1,
                "default" => 1
            ));
        }
    }

    public function step_4(): void
    {
        $db = $this->db;
        $analyzer = new \ilDBAnalyzer($db);
        $info = $analyzer->getFieldInformation('settings');

        if ($info['value']['type'] === 'clob') {
            $type = 'clob';
        } else {
            $type = 'text';
        }

        $db = $this->db;

        $db->manipulate("DELETE FROM settings WHERE keyword = " .
            $db->quote("video_threshold", "text") . " AND module = " .
            $db->quote("mcst", "text"));

        $db->insert("settings", array(
            "module" => array("text", "mcst"),
            "keyword" => array("text", "video_threshold"),
            "value" => array($type, "80")));
    }

}
