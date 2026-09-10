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

namespace ILIAS\Style\Content\Template;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\Style\Content\InternalDomainService;

class TemplateTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected \ilObjStyleSheet $style_obj,
        protected string $temp_type,
        protected StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "style_templates";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("sty_templates");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->templateRetrieval($this->style_obj, $this->temp_type);
    }

    protected function transformRow(array $data_row): array
    {
        $preview = (string) $data_row["preview"];
        if ($preview === "") {
            $preview = \ilObjStyleSheetGUI::_getTemplatePreview(
                $this->style_obj,
                $this->temp_type,
                (int) $data_row["id"],
                true
            );
        }

        return [
            "id" => (int) $data_row["id"],
            "name" => (string) $data_row["name"],
            "preview" => $preview
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("name", $lng->txt("sty_template_name"), true)
            ->textColumn("preview", $lng->txt("sty_preview"));

        if ($this->access_manager->checkWrite()) {
            $table = $table
                ->singleRedirectAction(
                    "editTemplate",
                    $lng->txt("edit"),
                    [get_class($this->parent_gui)],
                    "editTemplate",
                    "t_id"
                )
                ->multiAction(
                    "deleteTemplateConfirmation",
                    $lng->txt("delete"),
                    true
                );
        }

        return $table;
    }
}
