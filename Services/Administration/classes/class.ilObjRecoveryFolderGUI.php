<?php declare(strict_types=1);

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

use ILIAS\Administration\AdminGUIRequest;

/**
 * Class ilObjRecoveryFolderGUI
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @ilCtrl_Calls ilObjRecoveryFolderGUI: ilPermissionGUI
 */
class ilObjRecoveryFolderGUI extends ilContainerGUI
{
    protected AdminGUIRequest $admin_request;
    public ilRbacSystem $rbacsystem;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacadmin = $DIC->rbac()->admin();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->type = "recf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->admin_request = new AdminGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }
    
    public function saveObject() : void
    {
        parent::saveObject();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        exit();
    }

    public function removeFromSystemObject() : void
    {
        $ru = new ilRepositoryTrashGUI($this);
        $ru->removeObjectsFromSystem($this->admin_request->getSelectedIds(), true);
        $this->ctrl->redirect($this, "view");
    }
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    protected function showPossibleSubObjects() : void
    {
        $this->sub_objects = "";
    }
    
    public function getActions() : array
    {
        // standard actions for container
        return array(
            "cut" => array("name" => "cut", "lng" => "cut"),
            "clear" => array("name" => "clear", "lng" => "clear"),
            "removeFromSystem" => array("name" => "removeFromSystem", "lng" => "btn_remove_system")
        );
    }
}
