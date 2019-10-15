<?php
require_once('./Services/UIComponent/classes/class.ilUIHookProcessor.php');

/**
 * Class ilUIPluginRouterGUI
 *
 * This service is used by plugins. It allows any plugin to get called by a http request without dependencies to a
 * certain module or service other than this.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>, Oskar Truffer <ot@studer-raimann.ch>
 */
class ilUIPluginRouterGUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;


    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
    }


    /**
     * The only thing this execute Command does is forward the command in the command chain.
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $class_file = $this->ctrl->lookupClassPath($next_class);
                if (is_file($class_file)) {
                    include_once($class_file);
                    $gui = new $next_class();
                    $this->ctrl->forwardCommand($gui);
                } else {
                    ilUtil::sendFailure('Plugin GUI-Class not found! (' . $next_class . ')');
                }
                break;
        }
    }
}