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

namespace ILIAS\COPage\History;

use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\COPage\InternalDomainService;
use ILIAS\COPage\InternalGUIService;

class HistoryTableBuilder extends CommonTableBuilder
{
    protected \ILIAS\UI\Factory $ui_factory;
    protected int $page_id;
    protected string $parent_type;
    protected string $lang;
    protected bool $rselect = false;
    protected bool $lselect = false;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        object $parent_gui,
        string $parent_cmd,
        int $page_id,
        string $parent_type,
        string $lang
    ) {
        $this->page_id = $page_id;
        $this->parent_type = $parent_type;
        $this->lang = $lang;
        $this->ui_factory = $this->gui->ui()->factory();
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "ilCOPgHistoryTable";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("content_page_history");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->historyRetrieval(
            $this->page_id,
            $this->parent_type,
            $this->lang
        );
    }

    protected function transformRow(array $data_row): array
    {
        $ctrl = $this->gui->ctrl();

        $nr = (int) $data_row["nr"];

        $ctrl->setParameter($this->parent_gui, "old_nr", $nr);
        $ctrl->setParameter($this->parent_gui, "history_mode", "1");
        $date_link = $this->ui_factory->link()->standard(
            \ilDatePresentation::formatDate(new \ilDateTime($data_row["hdate"], IL_CAL_DATETIME)),
            $ctrl->getLinkTarget($this->parent_gui, "preview")
        );
        $ctrl->setParameter($this->parent_gui, "history_mode", "");
        $ctrl->setParameter($this->parent_gui, "old_nr", "");

        $user = "";
        if (\ilObject::_exists((int) $data_row["user"])) {
            $user = \ilUserUtil::getNamePresentation(
                (int) $data_row["user"],
                true,
                true,
                $ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
            );
        }

        return [
            "id" => (string) $nr,
            "date" => $date_link,
            "user" => $user
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ($action === "rollbackConfirmation" && $data_row["nr"] > 0) {
            return true;
        }
        return false;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->linkColumn("date", $lng->txt("date"))
            ->textColumn("user", $lng->txt("user"))
            ->multiAction("compareVersion", $lng->txt("cont_page_compare"))
            ->singleAction("rollbackConfirmation", $lng->txt("cont_rollback"));

        return $table;
    }
}
