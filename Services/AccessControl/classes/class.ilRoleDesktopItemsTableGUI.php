<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* Table for role desktop items
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilRoleDesktopItemsTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_object)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        
        $this->setId('objrolepd');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($this->lng->txt('role_assigned_desk_items') . ' (' . $a_object->getTitle() . ')');
        
        $this->addColumn('', '', 1);
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('desc'));
        $this->addColumn($this->lng->txt('path'));
        
        $this->setRowTemplate("tpl.role_desktop_item_list.html", "Services/AccessControl");
        $this->setDefaultOrderField('title');
                
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->addMultiCommand('askDeleteDesktopItem', $this->lng->txt('delete'));
        $this->setSelectAllCheckbox('del_desk_item');
        
        $this->getItems($a_object->getId());
    }
    
    protected function getItems($a_obj_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        $data = array();
        
        include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';
        $role_desk_item_obj = new ilRoleDesktopItem($a_obj_id);
        foreach ($role_desk_item_obj->getAll() as $role_item_id => $item) {
            $ref_id = $item['item_id'];
            $tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id);
            
            $data[] = array(
                "id" => $role_item_id,
                "type" => $tmp_obj->getType(),
                "title" => $tmp_obj->getTitle(),
                "desc" => $tmp_obj->getDescription(),
                "path" => $this->formatPath($tree->getPathFull($ref_id))
            );
        }

        $this->setData($data);
    }
        
    protected function formatPath($a_path_arr)
    {
        $counter = 0;

        foreach ($a_path_arr as $data) {
            if ($counter++) {
                $path .= " &raquo; ";
            }

            $path .= $data['title'];
        }

        if (strlen($path) > 50) {
            return '...' . substr($path, -50);
        }

        return $path;
    }

    public function fillRow($a_set)
    {
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_ICON_SRC", ilObject::_getIcon("", "big", $a_set["type"]));
        $this->tpl->setVariable("VAL_ICON_ALT", $this->lng->txt("obj_" . $a_set["type"]));
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_DESC", $a_set["desc"]);
        $this->tpl->setVariable("VAL_PATH", $a_set["path"]);
    }
}
