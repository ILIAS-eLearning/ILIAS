<?php

/**
 * Service ilViewRouterGUI
 * This service is used by LTI. It allows any plugin to get called by a http request without dependencies to a
 * certain module or service other than this.
 *
 * @author  Stefan Schneider <schneider@hrz.uni-marburg.de>
 * @version $Id:
 *
 * @ingroup ServicesRouter
 */
class ilLTIRouterGUI
{

    /** @var  ilCtrl */
    protected $ilCtrl;

    public function __construct()
    {
        global $ilCtrl;
        $this->ilCtrl = $ilCtrl;
    }

    /**
     * The only thing this execute Command does is forward the command in the command chain.
     */
    public function executeCommand()
    {
        $next_class = $this->ilCtrl->getNextClass($this);
        $class_file = $this->ilCtrl->lookupClassPath($next_class);
        
        if (is_file($class_file)) {
            include_once($class_file);
            $gui = $next_class::getInstance(); // Singleton!
            $this->ilCtrl->forwardCommand($gui);
        } else {
            ilUtil::sendFailure('GUI-Class not found! (' . $next_class . ')');
        }
    }
}
