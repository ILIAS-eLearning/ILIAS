<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Class ilCloudHookPlugin
 *
 * Definition of the PluginHook
 *
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
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
    final public function getComponentType()
    {
        return IL_COMP_MODULE;
    }


    /**
     * Get Component Name.
     *
     * @return        string        Component Name
     */
    final public function getComponentName()
    {
        return "Cloud";
    }


    /**
     * Get Slot Name.
     *
     * @return        string        Slot Name
     */
    final public function getSlot()
    {
        return "CloudHook";
    }


    /**
     * Get Slot ID.
     *
     * @return        string        Slot Id
     */
    final public function getSlotId()
    {
        return "cldh";
    }


    public function getPluginTablePrefix()
    {
        $id = $this->getId();
        if (!$id) {
            $id = $this->getPluginInfo()->getId();
        }
        return $this->getSlotObject()->getPrefix() . "_" . $id;
    }


    public function getPluginTableName()
    {
        return $this->getPluginTablePrefix() . "_props";
    }


    public function getPluginConfigTableName()
    {
        return $this->getPluginTablePrefix() . "_config";
    }
}
