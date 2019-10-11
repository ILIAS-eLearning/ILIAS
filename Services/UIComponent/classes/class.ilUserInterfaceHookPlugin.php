<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserInterfaceHookPlugin
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilUserInterfaceHookPlugin extends ilPlugin
{

    /**
     * @return string
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }


    /**
     * @return string
     */
    final public function getComponentName()
    {
        return "UIComponent";
    }


    /**
     * @return string
     */
    final public function getSlot()
    {
        return "UserInterfaceHook";
    }


    /**
     * @return string
     */
    final public function getSlotId()
    {
        return "uihk";
    }


    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
        // nothing to do here
    }


    /**
     * @return ilUIHookPluginGUI
     */
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
