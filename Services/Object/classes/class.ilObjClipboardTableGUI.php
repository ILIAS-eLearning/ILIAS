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

/**
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjClipboardTableGUI extends ilTable2GUI
{
    public function __construct(?object $parent_obj, string $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setTitle($this->lng->txt("clipboard"));

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("action"));

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.obj_cliboard_row.html", "Services/Object");
    }

    protected function fillRow(array $set): void
    {
        $this->tpl->setVariable(
            "ICON",
            ilUtil::img(ilObject::_getIcon((int) $set["obj_id"], "tiny"), $set["type_txt"])
        );
        $this->tpl->setVariable("TITLE", $set["title"]);
        $this->tpl->setVariable("CMD", $set["cmd"]);
    }
}
