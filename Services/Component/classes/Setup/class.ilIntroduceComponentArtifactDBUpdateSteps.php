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
 ********************************************************************
 */

declare(strict_types=1);

/**
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilIntroduceComponentArtifactDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->dropTable("il_component");
    }

    public function step_2(): void
    {
        $this->db->dropTable("il_pluginslot");
    }

    public function step_3(): void
    {
        $this->db->manipulate("DELETE FROM il_plugin WHERE plugin_id IS NULL");
    }

    public function step_4(): void
    {
        try {
            $this->db->addPrimaryKey("il_plugin", ["plugin_id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey("il_plugin");
            $this->db->addPrimaryKey("il_plugin", ["plugin_id"]);
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists("il_plugin", "component_type")) {
            return;
        }

        $this->db->dropTableColumn("il_plugin", "component_type");
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists("il_plugin", "component_name")) {
            return;
        }

        $this->db->dropTableColumn("il_plugin", "component_name");
    }

    public function step_7(): void
    {
        if (!$this->db->tableColumnExists("il_plugin", "slot_id")) {
            return;
        }

        $this->db->dropTableColumn("il_plugin", "slot_id");
    }

    public function step_8(): void
    {
        if (!$this->db->tableColumnExists("il_plugin", "name")) {
            return;
        }

        $this->db->dropTableColumn("il_plugin", "name");
    }
}
