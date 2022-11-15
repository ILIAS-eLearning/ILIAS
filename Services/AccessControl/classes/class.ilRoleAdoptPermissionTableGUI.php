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
 * Copy Permission Settings
 * @author  Fabian Wolf <wolf@leifos.com>
 * @ingroup ServiceAccessControl
 */
class ilRoleAdoptPermissionTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        $this->setId("adopt_permission");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("");
        $this->addColumn($this->lng->txt("title"), "title", "70%");
        $this->addColumn($this->lng->txt("type"), "type", "30%");
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.obj_role_adopt_permission_row.html",
            "Services/AccessControl"
        );
        $this->addCommandButton("perm", $this->lng->txt("cancel"));
        $this->addMultiCommand("adoptPermSave", $this->lng->txt("save"));

        $this->setLimit(9999);
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("PARAM", "adopt");
        $this->tpl->setVariable("VAL_ID", $a_set["role_id"] ?? '');
        $this->tpl->setVariable("VAL_TITLE", $a_set["role_name"] ?? '');
        if (strlen($a_set["role_desc"] ?? '')) {
            $this->tpl->setVariable("VAL_DESCRIPTION", $a_set["role_desc"] ?? '');
        }
        $this->tpl->setVariable("VAL_TYPE", $a_set["type"] ?? '');
    }
}
