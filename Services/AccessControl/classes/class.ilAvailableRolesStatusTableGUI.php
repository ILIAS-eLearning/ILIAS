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
 * Table for Available Roles in Permission > Permission of User
 * @author  Fabian Wolf <wolf@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilAvailableRolesStatusTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        $this->setId('available_roles' . $a_parent_obj->user->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setEnableHeader(true);
        $this->disable('numinfo');
        $this->setLimit(100);
        $this->setRowTemplate("tpl.available_roles_status_row.html", "Services/AccessControl");

        $this->addColumn("", "status", "5%");
        $this->addColumn($this->lng->txt("role"), "role", "32%");
        $this->addColumn(
            str_replace(" ", "&nbsp;", $this->lng->txt("info_permission_source")),
            "effective_from",
            "32%"
        );
        $this->addColumn(str_replace(" ", "&nbsp;", $this->lng->txt("info_permission_origin")), "original_position");
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow(array $a_set): void
    {
        if ($a_set["img"] == ilObjectPermissionStatusGUI::IMG_OK) {
            $img_path = ilUtil::getImagePath("icon_ok.svg");
            $img_info = $this->lng->txt("info_assigned");
        } else {
            $img_path = ilUtil::getImagePath("icon_not_ok.svg");
            $img_info = $this->lng->txt("info_not_assigned");
        }
        $this->tpl->setVariable("IMG_PATH", $img_path);
        $this->tpl->setVariable("IMG_INFO", $img_info);

        $link = $this->ctrl->getLinkTargetByClass(array('ilpermissiongui'), 'perm', '', true);
        $this->tpl->setVariable("ROLE_LINK", $link);
        $this->tpl->setVariable("TXT_ROLE", $a_set["role"]);

        if ($a_set["effective_from"] != "") {
            $this->tpl->setCurrentBlock("effective_from");
            $this->tpl->setVariable("EFFECTIVE_FROM_LINK", ilLink::_getLink($a_set["effective_from_ref_id"]));
            $this->tpl->setVariable("TXT_EFFECTIVE_FROM", $a_set["effective_from"]);
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["original_position_ref_id"] !== false) {
            $this->tpl->setCurrentBlock("original_position_with_link");
            $this->tpl->setVariable("TXT_ORIGINAL_POSITION_WITH_LINK", $a_set["original_position"]);
            $this->tpl->setVariable(
                "ORIGINAL_POSITION_LINK",
                ilLink::_getLink((int) $a_set["original_position_ref_id"])
            );
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable("TXT_ORIGINAL_POSITION", $a_set["original_position"]);
        }
    }
}
