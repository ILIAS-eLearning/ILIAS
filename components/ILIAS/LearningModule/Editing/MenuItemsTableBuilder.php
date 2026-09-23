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

namespace ILIAS\LearningModule\Editing;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\LearningModule\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class MenuItemsTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected array $entries,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "lm_menu_items";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_custom_menu_entries");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new MenuItemsRetrieval($this->entries);
    }

    protected function transformRow(array $data_row): array
    {
        $link = (string) $data_row["link"];
        if ($data_row["type"] === "intern") {
            $link = ILIAS_HTTP_PATH . "/goto.php?target=" . $link;
        }

        if (!strstr($link, '://') && !strstr($link, 'mailto:')) {
            $link = "https://" . $link;
        }

        return [
            "id" => (int) $data_row["id"],
            "link" => $this->gui->ui()->factory()->link()->standard(
                (string) $data_row["title"],
                $link
            ),
            "active" => $data_row["active"] === "y"
                ? $this->domain->lng()->txt("active")
                : $this->domain->lng()->txt("inactive")
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        $active = $data_row["active"] === "y";

        return match ($action) {
            "activateMenuEntry" => !$active,
            "deactivateMenuEntry" => $active,
            default => true
        };
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->linkColumn("link", $lng->txt("link"))
            ->textColumn("active", $lng->txt("active"))
            ->singleAction("activateMenuEntry", $lng->txt("activate"))
            ->singleAction("deactivateMenuEntry", $lng->txt("deactivate"))
            ->singleAction("editMenuEntryFromTable", $lng->txt("edit"))
            ->singleAction("confirmDeleteMenuEntry", $lng->txt("delete"), true);
    }
}
