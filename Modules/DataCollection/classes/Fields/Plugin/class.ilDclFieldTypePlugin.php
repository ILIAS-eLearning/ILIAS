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
 ********************************************************************
 */

/**
 * Class ilDclFieldTypePlugin
 * Definition of the PluginHook
 * @author  Michael Herren
 * @extends ilPlugin
 */
abstract class ilDclFieldTypePlugin extends ilPlugin
{
    public const COMPONENT_NAME = "DataCollection";
    public const SLOT_NAME = "FieldTypeHook";
    public const SLOT_ID = "dclfth";

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
