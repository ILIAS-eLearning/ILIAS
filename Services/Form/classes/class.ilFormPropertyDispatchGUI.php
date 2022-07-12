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

/**
 * Form property dispatcher. Forwards control flow to property form input GUI
 * classes.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilFormPropertyDispatchGUI:
 */
class ilFormPropertyDispatchGUI
{
    protected ilCtrl $ctrl;
    protected ilFormPropertyGUI $item;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    public function setItem(ilFormPropertyGUI $a_val) : void
    {
        $this->item = $a_val;
    }
    
    public function getItem() : ilFormPropertyGUI
    {
        return $this->item;
    }
    
    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        if (strtolower(get_class($this->getItem())) != $next_class) {
            die("ilFormPropertyDispatch: Forward Error. (" . get_class($this->getItem()) . "-" . $next_class . ")");
        }
        
        return $ilCtrl->forwardCommand($this->getItem());
    }
}
