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

declare(strict_types=1);

namespace ILIAS\Help\Module;

class ModuleDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function create(): int
    {
        $id = $this->db->nextId("help_module");

        $this->db->manipulate("INSERT INTO help_module " .
            "(id) VALUES (" .
            $this->db->quote($id, "integer") .
            ")");

        return $id;
    }

    public function writeHelpModuleLmId(
        int $a_id,
        int $a_lm_id
    ): void {
        $this->db->manipulate(
            "UPDATE help_module SET " .
            " lm_id = " . $this->db->quote($a_lm_id, "integer") .
            " WHERE id = " . $this->db->quote($a_id, "integer")
        );
    }

    public function saveOrder(array $order): void
    {
        foreach ($order as $id => $order_nr) {
            $this->db->update(
                "help_module",
                [
                "order_nr" => ["integer", (int) $order_nr]
            ],
                [    // where
                    "id" => ["integer", (int) $id]
                ]
            );
        }
    }

    public function getHelpModules(): array
    {
        $set = $this->db->query("SELECT * FROM help_module ORDER BY order_nr ASC");

        $mods = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            if (\ilObject::_lookupType((int) $rec["lm_id"]) === "lm") {
                $rec["title"] = \ilObject::_lookupTitle((int) $rec["lm_id"]);
                $rec["create_date"] = \ilObject::_lookupCreationDate((int) $rec["lm_id"]);
            }

            $mods[] = $rec;
        }

        return $mods;
    }

    public function lookupModuleTitle(
        int $a_id
    ): string {

        $set = $this->db->query(
            "SELECT * FROM help_module " .
            " WHERE id = " . $this->db->quote($a_id, "integer")
        );
        $rec = $this->db->fetchAssoc($set);
        if (\ilObject::_lookupType((int) $rec["lm_id"]) === "lm") {
            return \ilObject::_lookupTitle((int) $rec["lm_id"]);
        }
        return "";
    }

    public function lookupModuleLmId(
        int $a_id
    ): int {
        $set = $this->db->query(
            "SELECT lm_id FROM help_module " .
            " WHERE id = " . $this->db->quote($a_id, "integer")
        );
        $rec = $this->db->fetchAssoc($set);
        return (int) $rec["lm_id"];
    }

    public function deleteModule(
        int $id
    ): void {
        $this->db->manipulate("DELETE FROM help_module WHERE " .
            " id = " . $this->db->quote($id, "integer"));
    }

    /**
     * Check if LM is a help LM
     */
    public function isHelpLM(
        int $a_lm_id
    ): bool {
        $set = $this->db->query(
            "SELECT id FROM help_module " .
            " WHERE lm_id = " . $this->db->quote($a_lm_id, "integer")
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function writeActive(int $module_id, bool $active): void
    {
        $this->db->update(
            "help_module",
            [
            "active" => ["integer", (int) $active]
        ],
            [    // where
                "id" => ["integer", $module_id]
            ]
        );

    }
}
