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

namespace ILIAS\Wiki\Page;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalGUIService;

class ExportOrderTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected array $all_pages,
        protected array $page_ids,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "wiki_export_order";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("wiki_show_print_view");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->page()->exportOrderRetrieval(
            $this->all_pages,
            $this->page_ids
        );
    }

    protected function getOrderingCommand(): string
    {
        return "printViewOrderList";
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        return $table->textColumn("title", $this->domain->lng()->txt("wiki_page"));
    }
}
