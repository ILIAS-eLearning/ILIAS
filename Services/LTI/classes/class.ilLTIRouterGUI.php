<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
class ilLTIRouterGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ilCtrl;
    protected ilGlobalTemplateInterface $main_tpl;
    
    public function __construct()
    {
        global $DIC;
        $this->ilCtrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * The only thing this execute Command does is forward the command in the command chain.
     */
    public function executeCommand() : void
    {
        $next_class = $this->ilCtrl->getNextClass($this);
        $class_file = $this->ilCtrl->lookupClassPath($next_class);

        if (is_file($class_file)) {
            //ToDo: check - was $gui = $next_class::getInstance(); // Singleton!
            $gui = $next_class::getInstance();
//            $gui = call_user_func([$next_class, 'getInstance']);
            $this->ilCtrl->forwardCommand($gui);
        } else {
            $this->main_tpl->setOnScreenMessage('failure', 'GUI-Class not found! (' . $next_class . ')');
        }
    }
}
