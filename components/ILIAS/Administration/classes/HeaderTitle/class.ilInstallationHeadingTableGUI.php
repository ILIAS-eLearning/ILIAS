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

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

/**
 * TableGUI class for title/description translations
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilInstallationHeadingTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected LOMServices $lom_services;

    protected bool $write_enabled = false;
    protected string $base_cmd;
    protected int $nr;

    public function __construct(
        ?object $parent_obj,
        string $parent_cmd,
        bool $write_enabled
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->lom_services = $DIC->learningObjectMetadata();

        parent::__construct($parent_obj, $parent_cmd);
        $this->write_enabled = $write_enabled;

        $this->setLimit(9999);

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("language"));
        $this->addColumn($this->lng->txt("default"));
        $this->addColumn($this->lng->txt("title"));

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.installation_heading_table_row.html", "components/ILIAS/Administration");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $this->nr = 0;
    }

    protected function prepareOutput(): void
    {
        if ($this->write_enabled) {
            $this->addMultiCommand("delete", $this->lng->txt("remove"));
            if ($this->dataExists()) {
                $this->addCommandButton("save", $this->lng->txt("save"));
            }
            $this->addCommandButton("add", $this->lng->txt("add"));
        }
    }

    protected function fillRow(array $set): void
    {
        $this->nr++;
        $this->tpl->setVariable("NR", $this->nr);

        // lang selection
        $languages = [];
        foreach ($this->lom_services->dataHelper()->getAllLanguages() as $language) {
            $languages[$language->value()] = $language->presentableLabel();
        }
        $this->tpl->setVariable(
            "LANG_SELECT",
            ilLegacyFormElementsUtil::formSelect(
                $set["lang"],
                "lang[" . $this->nr . "]",
                $languages,
                false,
                true
            )
        );

        if ($set["default"]) {
            $this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
        }

        $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($set["title"]));
    }
}
