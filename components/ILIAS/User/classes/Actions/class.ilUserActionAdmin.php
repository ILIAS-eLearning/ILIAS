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



/**
 * User action administration
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionAdmin
{
    private array $data = [];

    public function __construct(
        private ilDBInterface $db
    ) {
        $this->data = $this->loadData();
    }

    public function activateAction(
        string $context_comp,
        string $context_id,
        string $action_comp,
        string $action_type,
        bool $active
    ): void {
        $this->db->replace(
            "user_action_activation",
            [
                "context_comp" => ["text", $context_comp],
                "context_id" => ["text", $context_id],
                "action_comp" => ["text", $action_comp],
                "action_type" => ["text", $action_type],
            ],
            [
                "active" => ["integer", $active]
            ]
        );

        $this->data[$context_comp][$context_id][$action_comp][$action_type] = $active;
    }

    public function isActionActive(
        string $context_comp,
        string $context_id,
        string $action_comp,
        string $action_type
    ): bool {
        if (
            !isset($this->data[$context_comp])
            || !isset($this->data[$context_comp][$context_id])
            || !isset($this->data[$context_comp][$context_id][$action_comp])
            || !isset($this->data[$context_comp][$context_id][$action_comp][$action_type])
        ) {
            return false;
        }
        return $this->data[$context_comp][$context_id][$action_comp][$action_type];
    }

    private function loadData(): array
    {
        $data = [];
        $set = $this->db->query("SELECT * FROM user_action_activation");
        while ($rec = $this->db->fetchAssoc($set)) {
            $data[$rec["context_comp"]][$rec["context_id"]][$rec["action_comp"]][$rec["action_type"]] = (bool) $rec["active"];
        }

        return $data;
    }
}
