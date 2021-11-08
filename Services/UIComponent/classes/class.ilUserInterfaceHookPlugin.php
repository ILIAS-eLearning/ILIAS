<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilUserInterfaceHookPlugin
 *
 * @author Alexander Killing <killing@leifos.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilUserInterfaceHookPlugin extends ilPlugin
{
    final public function getComponentType() : string
    {
        return IL_COMP_SERVICE;
    }

    final public function getComponentName() : string
    {
        return "UIComponent";
    }

    final public function getSlot() : string
    {
        return "UserInterfaceHook";
    }

    final public function getSlotId() : string
    {
        return "uihk";
    }


    /**
     * Object initialization done by slot.
     */
    final protected function slotInit() : void
    {
        // nothing to do here
    }

    public function getUIClassInstance() : ilUIHookPluginGUI
    {
        /**
         * @var $obj ilUIHookPluginGUI
         */
        $class = "il" . $this->getPluginName() . "UIHookGUI";
        $this->includeClass("class." . $class . ".php");
        $obj = new $class();
        $obj->setPluginObject($this);

        return $obj;
    }
}
