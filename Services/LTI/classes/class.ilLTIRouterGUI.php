<?php

/**
 * Service ilViewRouterGUI
 * This service is used by LTI. It allows any plugin to get called by a http request without dependencies to a
 * certain module or service other than this.
 *
 * @author  Stean Schneider <schneider@hrz.uni-marburg.de>
 * @version $Id:
 *
 * @ingroup ServicesRouter
 */
class ilLTIRouterGUI 
{

    /** @var  ilCtrl */
    protected $ilCtrl;

    function __construct() {
        global $ilCtrl;
        $this->ctrl = $ilCtrl;
    }

    /**
     * The only thing this execute Command does is forward the command in the command chain.
     */
    function executeCommand() {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $class_file = $this->ctrl->lookupClassPath($next_class);
                if (is_file($class_file)) {
                    include_once($class_file);
                    $gui = $next_class::getInstance(); // Singleton!
                    $this->ctrl->forwardCommand($gui);
                } else {
                    ilUtil::sendFailure('GUI-Class not found! ('.$next_class.')');
                }
                break;
        }
    }
}
