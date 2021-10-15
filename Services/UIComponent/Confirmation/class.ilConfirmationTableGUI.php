<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilConfirmationTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected bool $use_icons;

    public function __construct(bool $a_use_icons)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        
        $this->use_icons = $a_use_icons;
        
        parent::__construct(null, "");
        $this->setTitle($lng->txt(""));
        $this->setLimit(9999);
        
        if ($this->use_icons) {
            $this->addColumn($this->lng->txt("type"), "", "1");
        }
        $this->addColumn($this->lng->txt("title"));
        
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.confirmation_row.html", "Services/Utilities");
        $this->disable("footer");
        $this->setEnableTitle(true);
    }
    
    protected function fillRow($item)
    {
        if ($this->use_icons) {
            if ($item["img"] != "") {
                $this->tpl->setCurrentBlock("img_cell");
                $this->tpl->setVariable("IMG_ITEM", $item["img"]);
                $this->tpl->setVariable("ALT_ITEM", $item["alt"]);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock("blank_cell");
            }
        }
        $this->tpl->setVariable("TXT_ITEM", $item["text"]);
        if (isset($item['var']) && $item['var']) {
            $this->tpl->setVariable('VAR_ITEM', $item['var']);
            $this->tpl->setVariable('ID', $item['id']);
        }
    }
}
