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

use Psr\Http\Message\RequestInterface;

/**
 * User profile badge gui
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilUserProfileBadgeGUI implements ilBadgeTypeGUI
{
    private RequestInterface $request;

    public function __construct()
    {
        global $DIC;

        $this->request = $DIC->http()->request();
    }

    public function initConfigForm(ilPropertyFormGUI $a_form, int $a_parent_ref_id) : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $fields = new ilCheckboxGroupInputGUI($lng->txt("profile"), "profile");
        $a_form->addItem($fields);
        
        $gui = new ilPersonalProfileGUI();
        $gui->showPublicProfileFields($a_form, array(), $fields, true);
    }
    
    public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config) : void // Missing array type.
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
    
    public function getConfigFromForm(ilPropertyFormGUI $a_form) : array // Missing array type.
    {
        $fields = array();
        foreach (array_keys($this->request->getParsedBody()) as $id) {
            if (strpos($id, "chk_") === 0) {
                $fields[] = $id;
            }
        }
        
        return array("profile" => $fields);
    }
    
    public function validateForm(ilPropertyFormGUI $a_form) : bool
    {
        return true;
    }
}
