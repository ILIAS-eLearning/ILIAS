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

/**
 * Class ilUIPluginRouterGUI
 *
 * This service is used by plugins. It allows any plugin to get called by a http request without dependencies to a
 * certain module or service other than this.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>, Oskar Truffer <ot@studer-raimann.ch>
 */
class ilUIPluginRouterGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ctrl;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand(): void
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
                    $this->main_tpl->setOnScreenMessage('failure', 'Plugin GUI-Class not found! (' . $next_class . ')');
                }
                break;
        }
    }
}
