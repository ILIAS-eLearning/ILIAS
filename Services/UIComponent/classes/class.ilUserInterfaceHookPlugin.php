<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User interface hook plugin
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup ServicesUIComponent
 */
abstract class ilUserInterfaceHookPlugin extends ilPlugin
{

    /**
     * @return string
     */
    public final function getComponentType()
    {
        return IL_COMP_SERVICE;
    }


    /**
     * @return string
     */
    public final function getComponentName()
    {
        return "UIComponent";
    }


    /**
     * @return string
     */
    public final function getSlot()
    {
        return "UserInterfaceHook";
    }


    /**
     * @return string
     */
    public final function getSlotId()
    {
        return "uihk";
    }


    /**
     * Object initialization done by slot.
     */
    protected final function slotInit()
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
