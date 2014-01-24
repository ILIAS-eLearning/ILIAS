<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Class ilCloudHookPlugin
 *
 * Definition of the PluginHook
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 * @extends ilPlugin
 * @ingroup ModulesCloud
 */
abstract class ilCloudHookPlugin extends ilPlugin
{
    /**
     * Get Component Type
     *
     * @return        string        Component Type
     */
    final function getComponentType()
    {
        return IL_COMP_MODULE;
    }

    /**
     * Get Component Name.
     *
     * @return        string        Component Name
     */
    final function getComponentName()
    {
        return "Cloud";
    }

    /**
     * Get Slot Name.
     *
     * @return        string        Slot Name
     */
    final function getSlot()
    {
        return "CloudHook";
    }

    /**
     * Get Slot ID.
     *
     * @return        string        Slot Id
     */
    final function getSlotId()
    {
        return "cldh";
    }

    /**
     * Object initialization done by slot.
     */
    protected final function slotInit()
    {
        // nothing to do here
    }

    public function getPluginTablePrefix()
    {
        $id = $this->getId();
        if(!$id)
        {
            $rec = ilPlugin::getPluginRecord($this->getComponentType(),$this->getComponentName(), $this->getSlotId(), $this->getPluginName());
            $id = $rec['plugin_id'];
        }
        return $this->getSlotObject()->getPrefix() . "_" . $id;
    }

    public function getPluginTableName()
    {
        return $this->getPluginTablePrefix()."_props";
    }

    public function getPluginConfigTableName()
    {
        return $this->getPluginTablePrefix() . "_config";
    }
}

?>