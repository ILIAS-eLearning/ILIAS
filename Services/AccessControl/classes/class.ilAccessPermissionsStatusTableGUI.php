<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Table for Acces Permissons in Permission > Permission of User
 *
 * @author Fabian Wolf <wolf@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
class ilAccessPermissionsStatusTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $lng = $DIC['lng'];

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId('accessperm' . $this->parent_obj->user->getId());
        $this->setEnableHeader(true);
        $this->disable('sort');
        $this->disable('numinfo');
        $this->setLimit(100);
        $this->setRowTemplate("tpl.access_permissions_status_row.html", "Services/AccessControl");

        $this->addColumn("", "status", "5%");
        $this->addColumn($lng->txt("operation"), "operation", "45%");
        $this->addColumn($lng->txt("info_from_role"), "role_ownership");
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if ($a_set["img"] == ilObjectPermissionStatusGUI::IMG_OK) {
            $img_path = ilUtil::getImagePath("icon_ok.svg");
            $img_info = $lng->txt("info_assigned");
        } else {
            $img_path = ilUtil::getImagePath("icon_not_ok.svg");
            $img_info = $lng->txt("info_not_assigned");
        }
        $this->tpl->setVariable("IMG_PATH", $img_path);
        $this->tpl->setVariable("IMG_INFO", $img_info);

        $this->tpl->setVariable("TXT_OPERATION", $a_set["operation"]);

        foreach ($a_set["role_ownership"] as $role_ownership) {
            $this->tpl->setCurrentBlock("role_ownership");
            $this->tpl->setVariable("TXT_ROLE_OWNERSHIP", $role_ownership);
            $this->tpl->parseCurrentBlock();
        }
    }
}
