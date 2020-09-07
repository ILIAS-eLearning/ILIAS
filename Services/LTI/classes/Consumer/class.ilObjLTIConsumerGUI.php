<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObjectGUI.php';

/**
 * Class ilObjLTIConsumer
 * @author Jesús López <lopez@leifos.com>
 *
 * @package ServicesLTI
 */
class ilObjLTIConsumer extends ilObjectGUI
{
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output)
    {
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "initConsumerForm";
                }
                $this->$cmd();
                break;
        }
    }
}
