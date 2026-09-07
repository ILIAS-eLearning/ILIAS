<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Tagging\User;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class UsersForTagTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected string $tag,
        protected string $title,
        protected string $user_label,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "users_for_tag";
    }

    protected function getTitle(): string
    {
        return $this->title;
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new UsersForTagRetrieval($this->tag);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "user" => \ilUserUtil::getNamePresentation(
                $data_row["id"],
                true,
                false,
                "",
                true
            )
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        return $table->textColumn("user", $this->user_label);
    }
}
