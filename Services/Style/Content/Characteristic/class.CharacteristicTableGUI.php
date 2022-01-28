<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Style;
use \ILIAS\Style\Content\Access;

/**
 * TableGUI class for characteristics
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicTableGUI extends \ilTable2GUI
{
    /**
     * @var Style\Content\CharacteristicManager
     */
    protected $manager;

    /**
     * @var Access\StyleAccessManager
     */
    protected $access_manager;

    /**
     * @var \ilObjStyleSheet
     */
    protected $style;

    /**
     * @var string
     */
    protected $super_type;

    /**
     * @var bool
     */
    protected $hideable;

    /**
     * @var int
     */
    protected $order_cnt = 0;

    /**
     * @var bool
     */
    protected $expandable = false;

    /**
     * @var UIFactory
     */
    protected $service_ui;

    /**
     * @var array
     */
    protected $core_styles = [];

    /**
     * CharacteristicTableGUI constructor.
     * @param UIFactory                 $service_ui
     * @param object                    $a_parent_obj
     * @param string                    $a_parent_cmd
     * @param string                    $a_super_type
     * @param \ilObjStyleSheet          $a_style
     * @param CharacteristicManager     $manager
     * @param Access\StyleAccessManager $access_manager
     */
    public function __construct(
        UIFactory $service_ui,
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_super_type,
        \ilObjStyleSheet $a_style,
        Style\Content\CharacteristicManager $manager,
        Access\StyleAccessManager $access_manager
    ) {
        $this->service_ui = $service_ui;
        $this->manager = $manager;
        $this->access_manager = $access_manager;
        $this->super_type = $a_super_type;
        $this->style = $a_style;

        $ctrl = $this->service_ui->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setExternalSorting(true);
        $this->core_styles = \ilObjStyleSheet::_getCoreStyles();
        $this->getItems();
        $this->setTitle($this->lng->txt("sty_" . $a_super_type . "_char"));
        $this->setLimit(9999);

        // check, whether any of the types is expandable
        $this->expandable = false;
        $this->hideable = false;
        $all_super_types = \ilObjStyleSheet::_getStyleSuperTypes();
        $types = $all_super_types[$this->super_type];
        foreach ($types as $t) {
            if (\ilObjStyleSheet::_isExpandable($t)) {
                $this->expandable = true;
            }
            if (\ilObjStyleSheet::_isHideable($t)) {
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

    /**
     * Get items
     */
    protected function getItems() : void
    {
        $data = [];
        foreach ($this->manager->getBySuperType($this->super_type) as $char) {
            $data[] = [
                "obj" => $char
            ];
        }
        $this->setData($data);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow($a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->service_ui->ctrl();
        $ui = $this->service_ui->ui();

        $char = $a_set["obj"];

        if ($this->expandable) {
            $this->order_cnt = $this->order_cnt + 10;
            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("OCHAR", $char->getType() . "." .
                \ilObjStyleSheet::_determineTag($char->getType()) .
                "." . $char->getCharacteristic());
            $this->tpl->setVariable("ORDER", $this->order_cnt);
            $this->tpl->parseCurrentBlock();
        }


        $this->tpl->setCurrentBlock("checkbox");
        $this->tpl->setVariable("CHAR", $char->getType() . "." .
            \ilObjStyleSheet::_determineTag($char->getType()) .
                    "." . $char->getCharacteristic());
        $this->tpl->parseCurrentBlock();

        if ($this->hideable) {
            if (!\ilObjStyleSheet::_isHideable($char->getType()) ||
                (!empty($this->core_styles[$char->getType() . "." .
                \ilObjStyleSheet::_determineTag($char->getType()) .
                "." . $char->getCharacteristic()]))) {
                $this->tpl->touchBlock("no_hide_checkbox");
            } else {
                $this->tpl->setCurrentBlock("hide_checkbox");
                $this->tpl->setVariable("CHAR", $char->getType() . "." .
                    \ilObjStyleSheet::_determineTag($char->getType()) .
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
            \ilObjStyleSheetGUI::getStyleExampleHTML($char->getType(), $char->getCharacteristic())
        );
        $tag_str = \ilObjStyleSheet::_determineTag($char->getType()) . "." . $char->getCharacteristic();
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

            if (!\ilObjStyleSheet::isCoreStyle($char->getType(), $char->getCharacteristic())) {
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
