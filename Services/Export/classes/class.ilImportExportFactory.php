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
		global $objDefinition;

		if($objDefinition->isPlugin($a_type))
		{
			$classname = 'il'.$objDefinition->getClassName($a_type).'Exporter';
			$location = $objDefinition->getLocation($a_type);
			if(include_once $location.'/class.'.$classname.'.php')
			{
				return $classname;
			}
		}
		else
		{
			
			$comp = $objDefinition->getComponentForType($a_type);
			$class = array_pop(explode("/", $comp));
			$class = "il".$class."Exporter";
			
			// the next line had a "@" in front of the include_once
			// I removed this because it tages ages to track down errors
			// if the include class contains parse errors.
			// Alex, 20 Jul 2012
			if(include_once "./".$comp."/classes/class.".$class.".php")
			{
				return $class;
			}
		}
			
		throw InvalidArgumentException('Invalid exporter type given');
	}
	
	public static function getComponentForExport($a_type)
	{
		/**
		 * @var $objDefinition ilObjectDefinition
		 */
		global $objDefinition;

		if($objDefinition->isPlugin($a_type))
		{
			return self::PLUGINS_DIR."/".$a_type;
		}
		else
		{
			return $objDefinition->getComponentForType($a_type);
		}		
	}
	
	public static function getImporterClass($a_component)
	{
		/**
		 * @var $objDefinition ilObjectDefinition
		 */
		global $objDefinition;
		
		$parts = explode('/', $a_component);
		$component_type = $parts[0];
		$component = $parts[1];
		
		if($component_type == self::PLUGINS_DIR &&
			$objDefinition->isPlugin($component))
		{			
			$classname = 'il'.$objDefinition->getClassName($component).'Importer';
			$location = $objDefinition->getLocation($component);
			if(include_once $location.'/class.'.$classname.'.php')
			{
				return $classname;
			}
		}
		else
		{							
			$class = "il".$component."Importer";			
			if(include_once "./".$a_component."/classes/class.".$class.".php")
			{
				return $class;
			}
		}
			
		throw InvalidArgumentException('Invalid importer type given');
	}
}
?>
