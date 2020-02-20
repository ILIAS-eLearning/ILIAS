<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for importer/exporter implementers
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilImportExportFactory
{
    const PLUGINS_DIR = "Plugins";

    public static function getExporterClass($a_type)
    {
        /**
         * @var $objDefinition ilObjectDefinition
         */
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        if ($objDefinition->isPlugin($a_type)) {
            $classname = 'il' . $objDefinition->getClassName($a_type) . 'Exporter';
            $location = $objDefinition->getLocation($a_type);
            if (include_once $location . '/class.' . $classname . '.php') {
                return $classname;
            }
        } else {
            $comp = $objDefinition->getComponentForType($a_type);
            $class = array_pop(explode("/", $comp));
            $class = "il" . $class . "Exporter";

            // page component plugin exporter classes are already included
            // the component is not registered by ilObjDefinition
            if (class_exists($class)) {
                return $class;
            }
            
            // the next line had a "@" in front of the include_once
            // I removed this because it tages ages to track down errors
            // if the include class contains parse errors.
            // Alex, 20 Jul 2012
            if (include_once "./" . $comp . "/classes/class." . $class . ".php") {
                return $class;
            }
        }
            
        throw new InvalidArgumentException('Invalid exporter type given');
    }
    
    public static function getComponentForExport($a_type)
    {
        /**
         * @var $objDefinition ilObjectDefinition
         */
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        if ($objDefinition->isPlugin($a_type)) {
            return self::PLUGINS_DIR . "/" . $a_type;
        } else {
            return $objDefinition->getComponentForType($a_type);
        }
    }

    /**
     * Get the importer class of a component
     *
     * @param string $a_component	component
     * @return string	class name of the importer class (or empty if the importer should be ignored)
     * @throws	InvalidArgumentException	the importer class is not found but should not be ignored
     */
    public static function getImporterClass($a_component)
    {
        /**
         * @var $objDefinition ilObjectDefinition
         */
        global $DIC;
        $objDefinition = $DIC['objDefinition'];
        
        $parts = explode('/', $a_component);
        $component_type = $parts[0];
        $component = $parts[1];
        
        if ($component_type == self::PLUGINS_DIR &&
            $objDefinition->isPlugin($component)) {
            $classname = 'il' . $objDefinition->getClassName($component) . 'Importer';
            $location = $objDefinition->getLocation($component);
            if (include_once $location . '/class.' . $classname . '.php') {
                return $classname;
            }
        } else {
            $class = "il" . $component . "Importer";
            // treat special case of page component plugins
            // they are imported with component type PLUGINS_DIR
            // but are not yet recognized by ilObjDefinition::isPlugin()
            //
            // if they are active, then their importer class is already included by ilCOPageImporter::init()
            if (class_exists($class)) {
                return $class;
            }
            // the page component plugin is not installed or not active
            // return an empty class name instead of throwing an exception
            // in this case the import should be continued without treating the page component
            elseif ($component_type == self::PLUGINS_DIR) {
                return "";
            }

            if (is_file("./" . $a_component . "/classes/class." . $class . ".php")) {
                return $class;
            }
        }
            
        throw new InvalidArgumentException('Invalid importer type given: ' . "./" . $a_component . "/classes/class." . $class . ".php");
    }
}
