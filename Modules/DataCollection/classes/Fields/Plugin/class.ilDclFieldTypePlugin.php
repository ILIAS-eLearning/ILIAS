<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Class ilDclFieldTypePlugin
 *
 * Definition of the PluginHook
 *
 * @author  Michael Herren
 * @extends ilPlugin
 */
abstract class ilDclFieldTypePlugin extends ilPlugin
{
    const COMPONENT_NAME = "DataCollection";
    const SLOT_NAME = "FieldTypeHook";
    const SLOT_ID = "dclfth";
    const CONFIG_FIELD_MODEL = "field_model";
    const CONFIG_RECORD_MODEL = "record_model";
    const CONFIG_RECORD_FIELD_MODEL = "record_field_model";
    const CONFIG_FIELD_REPRESENTATION = "field_representation";
    const CONFIG_RECORD_REPRESENTATION = "record_representation";
    /**
     * @var ilDclFieldTypePlugin singleton-instance
     */
    protected static $instances = array();


    /**
     * Singleton for abstract class
     *
     * @return ilDclFieldTypePlugin
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }

        return self::$instances[$class];
    }


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
        return self::COMPONENT_NAME;
    }


    /**
     * Get Slot Name.
     *
     * @return        string        Slot Name
     */
    final public function getSlot()
    {
        return self::SLOT_NAME;
    }


    /**
     * Get Slot ID.
     *
     * @return        string        Slot Id
     */
    final public function getSlotId()
    {
        return self::SLOT_ID;
    }


    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
        // nothing to do here
    }


    public function getPluginTablePrefix()
    {
        $id = $this->getId();
        if (!$id) {
            $rec = ilPlugin::getPluginRecord($this->getComponentType(), $this->getComponentName(), $this->getSlotId(), $this->getPluginName());
            $id = $rec['plugin_id'];
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


    public function getPluginClassPrefix()
    {
        return 'il';
    }
}
