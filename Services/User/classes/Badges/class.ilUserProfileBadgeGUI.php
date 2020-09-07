<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeTypeGUI.php";

/**
 * User profile badge gui
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesUser
 */
class ilUserProfileBadgeGUI implements ilBadgeTypeGUI
{
    public function initConfigForm(ilPropertyFormGUI $a_form, $a_parent_ref_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $fields = new ilCheckboxGroupInputGUI($lng->txt("profile"), "profile");
        $a_form->addItem($fields);
        
        include_once "Services/User/classes/class.ilPersonalProfileGUI.php";
        $gui = new ilPersonalProfileGUI();
        $gui->showPublicProfileFields($a_form, array(), $fields, true);
    }
    
    public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config)
    {
        if (is_array($a_config["profile"])) {
            $group = $a_form->getItemByPostVar("profile");
            foreach ($group->getSubItems() as $field) {
                foreach ($a_config["profile"] as $id) {
                    if ($field->getPostVar() == $id) {
                        $field->setChecked(true);
                        break;
                    }
                }
            }
        }
    }
    
    public function getConfigFromForm(ilPropertyFormGUI $a_form)
    {
        $fields = array();
        foreach (array_keys($_POST) as $id) {
            if (substr($id, 0, 4) == "chk_") {
                $fields[] = $id;
            }
        }
        
        return array("profile" => $fields);
    }
    
    public function validateForm(ilPropertyFormGUI $a_form)
    {
        return true;
    }
}
