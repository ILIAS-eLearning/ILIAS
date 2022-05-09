<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclFieldTypePlugin
 * Definition of the PluginHook
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
     * @var ilDclFieldTypePlugin[] singleton-instance
     */
    protected static array $instances = array();

    /**
     * Singleton for abstract class
     */
    public static function getInstance(): ilDclFieldTypePlugin
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }

        return self::$instances[$class];
    }

    public function getPluginTablePrefix(): string
    {
        return $this->getLanguageHandler()->getPrefix();
    }

    public function getPluginTableName(): string
    {
        return $this->getPluginTablePrefix() . "_props";
    }

    public function getPluginConfigTableName(): string
    {
        return $this->getPluginTablePrefix() . "_config";
    }

    public function getPluginClassPrefix(): string
    {
        return 'il';
    }
}
