<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Table for Available Roles in Permission > Permission of User
 *
 * @author Fabian Wolf <wolf@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
class ilAvailableRolesStatusTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId('available_roles' . $this->parent_obj->user->getId());
        $this->setEnableHeader(true);
        $this->disable('numinfo');
        $this->setLimit(100);
        $this->setRowTemplate("tpl.available_roles_status_row.html", "Services/AccessControl");

        $this->addColumn("", "status", "5%");
        $this->addColumn($lng->txt("role"), "role", "32%");
        $this->addColumn(str_replace(" ", "&nbsp;", $lng->txt("info_permission_source")), "effective_from", "32%");
        $this->addColumn(str_replace(" ", "&nbsp;", $lng->txt("info_permission_origin")), "original_position");

        include_once('./Services/Link/classes/class.ilLink.php');
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if ($a_set["img"] == ilObjectPermissionStatusGUI::IMG_OK) {
            $img_path = ilUtil::getImagePath("icon_ok.svg");
            $img_info = $lng->txt("info_assigned");
        } else {
            $img_path = ilUtil::getImagePath("icon_not_ok.svg");
            $img_info = $lng->txt("info_not_assigned");
        }
        $this->tpl->setVariable("IMG_PATH", $img_path);
        $this->tpl->setVariable("IMG_INFO", $img_info);

        $link = $ilCtrl->getLinkTargetByClass(array('ilpermissiongui'), 'perm', '', true);
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
            $this->tpl->setVariable("ORIGINAL_POSITION_LINK", ilLink::_getLink($a_set["original_position_ref_id"]));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable("TXT_ORIGINAL_POSITION", $a_set["original_position"]);
        }
    }
}
