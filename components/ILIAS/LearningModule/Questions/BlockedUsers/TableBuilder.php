<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\LearningModule\Question\BlockedUsers;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected int $ref_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "blocked_users";
    }

    protected function getTitle(): string
    {
        return "";
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->blockedUsersRetrieval($this->ref_id);
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();

        return [
            "id" => $data_row["id"],
            "user" => $data_row["user"],
            "question" => $data_row["question"],
            "page" => $data_row["page"],
            "last_try" => $data_row["last_try"] ?? "",
            "unlocked" => ($data_row["unlocked"] ?? false)
                ? $lng->txt("yes")
                : $lng->txt("no")
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("user", $lng->txt("user"), true)
            ->textColumn("question", $lng->txt("question"))
            ->textColumn("page", $lng->txt("page"), true)
            ->textColumn("last_try", $lng->txt("cont_last_try"), true)
            ->textColumn("unlocked", $lng->txt("cont_unlocked"))
            ->standardAction("sendMailToBlockedUsers", $lng->txt("send_mail"))
            ->standardAction("resetNumberOfTries", $lng->txt("cont_reset_nr_of_tries"))
            ->standardAction("unlockQuestion", $lng->txt("cont_unlock_allow_continue"));
    }
}
