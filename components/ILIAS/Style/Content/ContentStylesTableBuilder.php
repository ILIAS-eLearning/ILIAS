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

namespace ILIAS\Style\Content;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Style\Content\Access\StyleAccessManager;

class ContentStylesTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected array $data,
        protected int $default_style,
        protected int $fixed_style,
        protected StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "content_styles";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("content_styles");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->contentStylesRetrieval($this->data);
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $id = (int) $data_row["id"];
        $title = (string) $data_row["title"];

        $ctrl = $this->gui->ctrl();
        if ($id > 0) {
            $ctrl->setParameterByClass("ilobjstylesheetgui", "obj_id", $id);
            $title_link = $this->gui->ui()->factory()->link()->standard(
                $title,
                $ctrl->getLinkTargetByClass("ilobjstylesheetgui", "")
            );
            $ctrl->setParameterByClass("ilobjstylesheetgui", "obj_id", "");
        } else {
            $title_link = $this->gui->ui()->factory()->link()->standard(
                $title,
                ""
            )->withDisabled();
        }

        $purpose = "";
        if ($id > 0 && $id === $this->fixed_style) {
            $purpose = $lng->txt("global_fixed");
        }
        if ($id > 0 && $id === $this->default_style) {
            $purpose = $lng->txt("global_default");
        }

        $scope = "";
        if (($data_row["category"] ?? 0) > 0) {
            $scope = \ilObject::_lookupTitle(
                \ilObject::_lookupObjId((int) $data_row["category"])
            );
        }

        return [
            "id" => $id,
            "title" => $title_link,
            "lm_nr" => (int) $data_row["lm_nr"],
            "purpose" => $purpose,
            "scope" => $scope,
            "active" => $id > 0
                ? (($data_row["active"] ?? false)
                    ? $lng->txt("active")
                    : $lng->txt("inactive"))
                : ""
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        $id = (int) $data_row["id"];
        $active = (bool) ($data_row["active"] ?? false);

        return match ($action) {
            "activateStyle" => $id > 0 && !$active,
            "deactivateStyle" => $id > 0 && $active,
            "makeGlobalDefault" => $id > 0 && $active && $id !== $this->default_style,
            "removeGlobalDefault" => $id > 0 && $id === $this->default_style,
            "makeGlobalFixed" => $id > 0 && $active && $id !== $this->fixed_style,
            "removeGlobalFixed" => $id > 0 && $id === $this->fixed_style,
            "setScope", "deleteStyle" => $id > 0,
            default => true
        };
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->linkColumn("title", $lng->txt("title"), true)
            ->textColumn("lm_nr", $lng->txt("sty_nr_objects"))
            ->textColumn("purpose", $lng->txt("purpose"))
            ->textColumn("scope", $lng->txt("sty_scope"))
            ->textColumn("active", $lng->txt("active"));

        if ($this->access_manager->checkWrite()) {
            $table = $table
                ->singleAction("makeGlobalDefault", $lng->txt("sty_make_global_default"))
                ->singleAction("removeGlobalDefault", $lng->txt("sty_remove_global_default_state"))
                ->singleAction("makeGlobalFixed", $lng->txt("sty_make_global_fixed"))
                ->singleAction("removeGlobalFixed", $lng->txt("sty_remove_global_fixed_state"))
                ->singleAction("setScope", $lng->txt("sty_set_scope"))
                ->singleAction("activateStyle", $lng->txt("activate"))
                ->singleAction("deactivateStyle", $lng->txt("deactivate"))
                ->singleAction("deleteStyle", $lng->txt("delete"), true);
        }

        return $table;
    }
}
