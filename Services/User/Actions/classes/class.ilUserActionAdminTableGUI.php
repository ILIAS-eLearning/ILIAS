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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionAdminTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        array $a_data,
        bool $a_write_permission = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($a_data);
        $this->setTitle($this->lng->txt(""));

        $this->addColumn($this->lng->txt("user_action"));
        $this->addColumn($this->lng->txt("active"), "", "1");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.user_action_admin_row.html", "Services/User/Actions");

        //$this->addMultiCommand("", $this->lng->txt(""));
        if ($a_write_permission) {
            $this->addCommandButton("save", $this->lng->txt("save"));
        }
    }

    /**
     * @param array<string,string> $a_set
     */
    protected function fillRow(array $a_set): void
    {
        if ($a_set["active"]) {
            $this->tpl->touchBlock("checked");
        }
        $this->tpl->setVariable("VAL", $a_set["action_type_name"]);
        $this->tpl->setVariable("ACTION_ID", $a_set["action_comp_id"] . ":" . $a_set["action_type_id"]);
    }
}
