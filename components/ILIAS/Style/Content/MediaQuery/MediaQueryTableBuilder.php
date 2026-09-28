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

namespace ILIAS\Style\Content\MediaQuery;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\Style\Content\InternalDomainService;

class MediaQueryTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected \ilObjStyleSheet $style_obj,
        protected StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "style_media_queries";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("sty_media_queries");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->mediaQueryRetrieval($this->style_obj);
    }

    protected function getOrderingCommand(): string
    {
        return $this->access_manager->checkWrite() ? "saveMediaQueryOrder" : "";
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => (int) $data_row["id"],
            "mquery" => "@media " . (string) $data_row["mquery"],
            "order_nr" => (int) $data_row["order_nr"]
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("mquery", $lng->txt("sty_query"));

        if ($this->access_manager->checkWrite()) {
            $table = $table
                ->singleRedirectAction(
                    "editMediaQuery",
                    $lng->txt("edit"),
                    [get_class($this->parent_gui)],
                    "editMediaQuery",
                    "mq_id"
                )
                ->multiAction(
                    "deleteMediaQueryConfirmation",
                    $lng->txt("delete"),
                    true
                );
        }

        return $table;
    }
}
