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

namespace ILIAS\Style\Content\Characteristic;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\Style\Content\CharacteristicManager;
use ILIAS\Style\Content\InternalDomainService;
use ilObjStyleSheet;
use ilObjStyleSheetGUI;

class CharacteristicTableBuilder extends CommonTableBuilder
{
    protected bool $expandable;
    protected bool $hideable;

    public function __construct(
        protected InternalDomainService $domain,
        protected string $super_type,
        protected CharacteristicManager $manager,
        protected StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->expandable = false;
        $this->hideable = false;
        $all_super_types = ilObjStyleSheet::_getStyleSuperTypes();
        foreach ($all_super_types[$this->super_type] as $type) {
            $this->expandable = $this->expandable || ilObjStyleSheet::_isExpandable($type);
            $this->hideable = $this->hideable || ilObjStyleSheet::_isHideable($type);
        }

        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "style_characteristics";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("sty_" . $this->super_type . "_char");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->characteristicRetrieval(
            $this->manager,
            $this->super_type
        );
    }

    protected function getOrderingCommand(): string
    {
        if ($this->expandable && $this->access_manager->checkWrite()) {
            return "saveStatus";
        }
        return "";
    }

    protected function transformRow(array $data_row): array
    {
        $characteristic = $data_row["obj"];
        $type = $characteristic->getType();
        $class = $characteristic->getCharacteristic();
        $is_core = ilObjStyleSheet::isCoreStyle($type, $class);
        $is_hideable = ilObjStyleSheet::_isHideable($type) && !$is_core;
        $lng = $this->domain->lng();

        return [
            "id" => $data_row["id"],
            "class_name" => $class,
            "title" => $this->manager->getPresentationTitle($type, $class, false),
            "type" => $lng->txt("sty_type_" . $type),
            "example" => ilObjStyleSheetGUI::getStyleExampleHTML($type, $class),
            "hidden" => $is_hideable
                ? ($characteristic->isHidden() ? $lng->txt("yes") : $lng->txt("no"))
                : "",
            "outdated" => $is_core
                ? ""
                : ($characteristic->isOutdated() ? $lng->txt("yes") : $lng->txt("no"))
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        $characteristic = $data_row["obj"];
        $type = $characteristic->getType();
        $class = $characteristic->getCharacteristic();
        $is_core = ilObjStyleSheet::isCoreStyle($type, $class);
        $is_hideable = ilObjStyleSheet::_isHideable($type) && !$is_core;

        return match ($action) {
            "copyCharacteristics" => ilObjStyleSheet::_isExpandable($type),
            "hideCharacteristic" => $is_hideable && !$characteristic->isHidden(),
            "showCharacteristic" => $is_hideable && $characteristic->isHidden(),
            "setOutdated" => !$is_core && !$characteristic->isOutdated(),
            "removeOutdated" => !$is_core && $characteristic->isOutdated(),
            "deleteCharacteristicConfirmation" => !$is_core && $this->expandable,
            default => true
        };
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("class_name", $lng->txt("sty_class_name"), true)
            ->textColumn("title", $lng->txt("title"), true)
            ->textColumn("type", $lng->txt("sty_type"), true)
            ->textColumn("example", $lng->txt("sty_example"));

        if ($this->hideable) {
            $table = $table->textColumn("hidden", $lng->txt("sty_hide"));
        }
        $table = $table->textColumn("outdated", $lng->txt("sty_outdated"));

        if ($this->access_manager->checkWrite()) {
            $table = $table
                ->singleAction("editTagStyle", $lng->txt("edit"))
                ->singleAction("copyCharacteristics", $lng->txt("copy"))
                ->singleAction("setOutdated", $lng->txt("sty_set_outdated"))
                ->singleAction("removeOutdated", $lng->txt("sty_remove_outdated"));

            if ($this->hideable) {
                $table = $table
                    ->singleAction("hideCharacteristic", $lng->txt("hide"))
                    ->singleAction("showCharacteristic", $lng->txt("show"));
            }
            if ($this->expandable) {
                $table = $table->singleAction(
                    "deleteCharacteristicConfirmation",
                    $lng->txt("delete"),
                    true
                );
            }
        }

        return $table;
    }
}
