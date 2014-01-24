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
			$c = explode("/", $comp);
			$class = "il".$c[1]."Exporter";
	
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
}
?>
