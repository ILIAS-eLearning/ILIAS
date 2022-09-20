<?php

declare(strict_types=1);

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

namespace ILIAS\Style\Content;

use ILIAS\Style;
use ILIAS\Style\Content\Access;
use ilObjStyleSheet;
use ilTable2GUI;
use ilObjStyleSheetGUI;

/**
 * TableGUI class for characteristics
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicTableGUI extends ilTable2GUI
{
    protected Style\Content\CharacteristicManager $manager;
    protected Access\StyleAccessManager $access_manager;
    protected ilObjStyleSheet $style;
    protected string $super_type;
    protected bool $hideable;
    protected int $order_cnt = 0;
    protected bool $expandable = false;
    protected InternalGUIService $gui_service;
    protected array $core_styles = [];

    public function __construct(
        InternalGUIService $gui_service,
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_super_type,
        ilObjStyleSheet $a_style,
        Style\Content\CharacteristicManager $manager,
        Access\StyleAccessManager $access_manager
    ) {
        $this->gui_service = $gui_service;
        $this->manager = $manager;
        $this->access_manager = $access_manager;
        $this->super_type = $a_super_type;
        $this->style = $a_style;

        $ctrl = $this->gui_service->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setExternalSorting(true);
        $this->core_styles = ilObjStyleSheet::_getCoreStyles();
        $this->getItems();
        $this->setTitle($this->lng->txt("sty_" . $a_super_type . "_char"));
        $this->setLimit(9999);

        // check, whether any of the types is expandable
        $this->expandable = false;
        $this->hideable = false;
        $all_super_types = ilObjStyleSheet::_getStyleSuperTypes();
        $types = $all_super_types[$this->super_type];
        foreach ($types as $t) {
            if (ilObjStyleSheet::_isExpandable($t)) {
                $this->expandable = true;
            }
            if (ilObjStyleSheet::_isHideable($t)) {
                $this->hideable = true;
            }
        }

        $this->addColumn("", "", "1");	// checkbox
        if ($this->expandable) {
            $this->addColumn($this->lng->txt("sty_order"));
        }
        $this->addColumn($this->lng->txt("sty_class_name"));
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("sty_type"));
        $this->addColumn($this->lng->txt("sty_example"));
        if ($this->hideable) {
            $this->addColumn($this->lng->txt("sty_hide"));	// hide checkbox
        }
        $this->addColumn($this->lng->txt("sty_outdated"));
        $this->addColumn($this->lng->txt("actions"));
        $this->setEnableHeader(true);
        $this->setFormAction($ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_row.html", "Services/Style/Content/Characteristic");
        $this->disable("footer");

        if ($this->access_manager->checkWrite()) {
            // action commands
            if ($this->hideable || $this->expandable) {
                $txt = $this->lng->txt("sty_save_hide_status");
                if ($this->hideable && $this->expandable) {
                    $txt = $this->lng->txt("sty_save_hide_order_status");
                } elseif (!$this->hideable) {
                    $txt = $this->lng->txt("sty_save_order_status");
                }

                $this->addCommandButton("saveStatus", $txt);
            }

            $this->addMultiCommand("copyCharacteristics", $this->lng->txt("copy"));
            $this->addMultiCommand("setOutdated", $this->lng->txt("sty_set_outdated"));
            $this->addMultiCommand("removeOutdated", $this->lng->txt("sty_remove_outdated"));

            // action commands
            if ($this->expandable) {
                $this->addMultiCommand("deleteCharacteristicConfirmation", $this->lng->txt("delete"));
            }
        }

        $this->setEnableTitle(true);
    }

    protected function getItems(): void
    {
        $data = [];
        foreach ($this->manager->getBySuperType($this->super_type) as $char) {
            $data[] = [
                "obj" => $char
            ];
        }
        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->gui_service->ctrl();
        $ui = $this->gui_service->ui();

        $char = $a_set["obj"];

        if ($this->expandable) {
            $this->order_cnt = $this->order_cnt + 10;
            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("OCHAR", $char->getType() . "." .
                ilObjStyleSheet::_determineTag($char->getType()) .
                "." . $char->getCharacteristic());
            $this->tpl->setVariable("ORDER", $this->order_cnt);
            $this->tpl->parseCurrentBlock();
        }


        $this->tpl->setCurrentBlock("checkbox");
        $this->tpl->setVariable("CHAR", $char->getType() . "." .
            ilObjStyleSheet::_determineTag($char->getType()) .
                    "." . $char->getCharacteristic());
        $this->tpl->parseCurrentBlock();

        if ($this->hideable) {
            if (!ilObjStyleSheet::_isHideable($char->getType()) ||
                (!empty($this->core_styles[$char->getType() . "." .
                ilObjStyleSheet::_determineTag($char->getType()) .
                "." . $char->getCharacteristic()]))) {
                $this->tpl->touchBlock("no_hide_checkbox");
            } else {
                $this->tpl->setCurrentBlock("hide_checkbox");
                $this->tpl->setVariable("CHAR", $char->getType() . "." .
                    ilObjStyleSheet::_determineTag($char->getType()) .
                    "." . $char->getCharacteristic());
                if ($this->style->getHideStatus($char->getType(), $char->getCharacteristic())) {
                    $this->tpl->setVariable("CHECKED", "checked='checked'");
                }
                $this->tpl->parseCurrentBlock();
            }
        }

        // example
        $this->tpl->setVariable(
            "EXAMPLE",
            ilObjStyleSheetGUI::getStyleExampleHTML($char->getType(), $char->getCharacteristic())
        );
        $tag_str = ilObjStyleSheet::_determineTag($char->getType()) . "." . $char->getCharacteristic();
        $this->tpl->setVariable("TXT_TAG", $char->getCharacteristic());
        $this->tpl->setVariable("TXT_TYPE", $lng->txt("sty_type_" . $char->getType()));

        $this->tpl->setVariable("TITLE", $this->manager->getPresentationTitle(
            $char->getType(),
            $char->getCharacteristic(),
            false
        ));

        if ($this->access_manager->checkWrite()) {
            $ilCtrl->setParameter($this->parent_obj, "tag", $tag_str);
            $ilCtrl->setParameter($this->parent_obj, "style_type", $char->getType());
            $ilCtrl->setParameter($this->parent_obj, "char", $char->getCharacteristic());

            $links = [];
            $links[] = $ui->factory()->link()->standard(
                $this->lng->txt("edit"),
                $ilCtrl->getLinkTargetByClass("ilStyleCharacteristicGUI", "editTagStyle")
            );

            if (!ilObjStyleSheet::isCoreStyle($char->getType(), $char->getCharacteristic())) {
                if ($char->isOutdated()) {
                    $this->tpl->setVariable("OUTDATED", $lng->txt("yes"));
                    $links[] = $ui->factory()->link()->standard(
                        $this->lng->txt("sty_remove_outdated"),
                        $ilCtrl->getLinkTargetByClass("ilStyleCharacteristicGUI", "removeOutdated")
                    );
                } else {
                    $this->tpl->setVariable("OUTDATED", $lng->txt("no"));
                    $links[] = $ui->factory()->link()->standard(
                        $this->lng->txt("sty_set_outdated"),
                        $ilCtrl->getLinkTargetByClass("ilStyleCharacteristicGUI", "setOutdated")
                    );
                }
            }

            $dd = $ui->factory()->dropdown()->standard($links);

            $this->tpl->setVariable(
                "ACTIONS",
                $ui->renderer()->render($dd)
            );
        }
    }
}
