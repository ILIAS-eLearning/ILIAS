<?php
require_once('./Services/UIComponent/classes/class.ilUIHookProcessor.php');

/**
 * Service ilRouterGUI
 * This service is used by plugins. It allows any plugin to get called by a http request without dependencies to a
 * certain module or service other than this.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>, Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ServicesRouter
 */
class ilUIPluginRouterGUI {

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
                    $gui = new $next_class();
                    $this->ctrl->forwardCommand($gui);
                } else {
                    ilUtil::sendFailure('Plugin GUI-Class not found! ('.$next_class.')');
                }
                break;
        }
    }
}