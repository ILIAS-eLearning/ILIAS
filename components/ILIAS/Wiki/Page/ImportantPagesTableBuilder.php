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

namespace ILIAS\Wiki\Page;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalGUIService;

class ImportantPagesTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $ref_id,
        protected int $wiki_id,
        protected string $start_page,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "wiki_important_pages";
    }

    protected function getTitle(): string
    {
        return "";
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->page()->importantPagesRetrieval(
            $this->ref_id,
            $this->wiki_id,
            $this->start_page
        );
    }

    protected function getOrderingCommand(): string
    {
        return "saveOrderingAndIndent";
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "title" => $data_row["title"],
            "indentation" => (string) $data_row["indentation"],
            "purpose" => $data_row["purpose"]
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ((int) $data_row["id"] === 0 && in_array(
            $action,
            ["confirmRemoveImportantPages", "setAsStartPage", "editIndentation"],
            true
        )) {
            return false;
        }

        return true;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("indentation", $lng->txt("wiki_indentation"))
            ->textColumn("title", $lng->txt("wiki_page"))
            ->textColumn("purpose", $lng->txt("wiki_purpose"))
            ->singleAction("editIndentation", $lng->txt("wiki_indentation"), true)
            ->multiAction("confirmRemoveImportantPages", $lng->txt("remove"), true)
            ->multiAction("setAsStartPage", $lng->txt("wiki_set_as_start_page"));
    }
}
