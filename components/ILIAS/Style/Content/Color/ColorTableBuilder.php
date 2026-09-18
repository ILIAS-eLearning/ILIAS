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

namespace ILIAS\Style\Content\Color;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\Style\Content\InternalDomainService;

class ColorTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected \ilObjStyleSheet $style_obj,
        protected StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "style_colors";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("sty_colors");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->colorRetrieval($this->style_obj);
    }

    protected function transformRow(array $data_row): array
    {
        $code = (string) $data_row["code"];
        if (!preg_match("/^[a-fA-F0-9]{6}$/D", $code)) {
            $code = "000000";
        }

        $name = htmlspecialchars(
            (string) $data_row["name"],
            ENT_QUOTES | ENT_SUBSTITUTE,
            "UTF-8"
        );
        $color = "<div style=\"width:30px; height:30px; background-color:#$code;\">&nbsp;</div>";
        $flavors = [];
        for ($i = -80; $i <= 80; $i += 20) {
            $flavor_code = \ilObjStyleSheet::_getColorFlavor($code, $i);
            $flavors[] = "<td width=\"11%\">" .
                "<div style=\"width:20px; height:20px; background-color:#$flavor_code;\">&nbsp;</div>" .
                "<div class=\"small\">($i)</div>" .
                "</td>";
        }

        return [
            "id" => $data_row["id"],
            "name" => $name,
            "code" => "#$code",
            "color" => $color,
            "flavors" => "<table width=\"100%\"><tr>" . implode("", $flavors) . "</tr></table>"
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("name", $lng->txt("sty_color_name"), true)
            ->textColumn("code", $lng->txt("sty_color_code"))
            ->textColumn("color", $lng->txt("sty_color"))
            ->textColumn("flavors", $lng->txt("sty_color_flavors"));

        if ($this->access_manager->checkWrite()) {
            $table = $table
                ->singleRedirectAction(
                    "editColor",
                    $lng->txt("edit"),
                    [get_class($this->parent_gui)],
                    "editColor",
                    "c_name"
                )
                ->multiAction("confirmDeleteColors", $lng->txt("delete"), true);
        }

        return $table;
    }
}
