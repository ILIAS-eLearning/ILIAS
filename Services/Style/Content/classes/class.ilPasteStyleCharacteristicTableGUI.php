<?php

use \ILIAS\Style\Content;

/**
 * Paste style overview table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilPasteStyleCharacteristicTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var Content\CharacteristicManager
     */
    protected $manager;

    /**
     * Constructor
     */
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        Content\CharacteristicManager $manager
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $this->manager = $manager;
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("sty_paste_characteristics"));
        $this->setLimit(9999);
        $this->from_style_id = $this->manager->getCopyCharacteristicStyleId();
        $this->from_style_type = $this->manager->getCopyCharacteristicStyleType();
        $this->setData($this->manager->getCopyCharacteristics());
        $this->addColumn($this->lng->txt("name"));
        $this->addColumn($this->lng->txt("type"));
        $this->addColumn($this->lng->txt("sty_if_style_class_already_exists"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.paste_style_row.html", "Services/Style/Content");
        $this->disable("footer");
        $this->setEnableTitle(true);

        //$this->addMultiCommand("", $lng->txt(""));
        $this->addCommandButton("pasteCharacteristics", $lng->txt("paste"));
        $this->addCommandButton("edit", $lng->txt("cancel"));
        $this->addHiddenInput("from_style_id", $this->from_style_id);
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $char = explode(".", $a_set);
        $this->tpl->setVariable("CHAR", $a_set);
        $this->tpl->setVariable("SEL_OVERWRITE", 'checked="checked"');
        $this->tpl->setVariable("VAL_TYPE", $lng->txt("sty_type_" . $char[0]));
        $this->tpl->setVariable("VAL_TITLE", $char[2]);
        $this->tpl->setVariable("TXT_OVERWRITE", $lng->txt("sty_overwrite"));
        $this->tpl->setVariable("TXT_IGNORE", $lng->txt("sty_keep_existing"));
    }
}
