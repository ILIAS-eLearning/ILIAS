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

namespace ILIAS\Container\Filter;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class ContainerFilterTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilContainerFilterService $container_filter_service,
        protected int $ref_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "cont_filter";
    }

    protected function getTitle(): string
    {
        return "";
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->containerFilterRetrieval(
            $this->container_filter_service,
            $this->ref_id
        );
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "record_set_id" => $data_row["record_set_id"],
            "record_title" => $data_row["record_title"],
            "field_title" => $data_row["field_title"],
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("record_title", $lng->txt("cont_filter_record"))
            ->textColumn("field_title", $lng->txt("cont_filter_field"));
    }
}
