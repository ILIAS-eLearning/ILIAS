<?php

/**
 * Class ilCtrlCommandClass1TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilCtrlCommandClass1TestGUI: ilCtrlBaseClassTestGUI
 * @ilCtrl_Calls      ilCtrlCommandClass1TestGUI: ilCtrlCommandClass2TestGUI
 */
final class ilCtrlCommandClass1TestGUI implements ilCtrlSecurityInterface
{
    private ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    /**
     * @inheritDoc
     */
    public function getUnsafeGetCommands() : array
    {
        return [
            'index',
        ];
    }

    public function executeCommand() : string
    {
        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilCtrlCommandClass2TestGUI::class):
                return $this->ctrl->forwardCommand(new ilCtrlCommandClass2TestGUI());

            default:
                $cmd = $this->ctrl->getCmd();
                return $this->{$cmd}();
        }
    }

    private function index() : string
    {
        return "Hello World!";
    }
}