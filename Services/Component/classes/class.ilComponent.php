<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


define ("IL_COMP_MODULE", "Modules");
define ("IL_COMP_SERVICE", "Services");
define ("IL_COMP_PLUGIN", "Plugins");

/**
* @defgroup ServicesComponent Services/Component
*
* ILIAS Component. This is the parent class for all ILIAS components.
* Components are Modules (Modules are ressources that can be added to the
* ILIAS repository), Services (Services provide cross-sectional functionalities
* for other ILIAS components) and Plugins.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
abstract class ilComponent
{
	/**
	* Get Version Number of Component. The number should be changed
	* if anything in the code is changed. Otherwise ILIAS will not be able
	* to recognize any change in the module.
	*
	* The format must be:
	* <major number>.<minor number>.<bugfix number>
	* <bugfix number> should be increased for bugfixes
	* <minor number> should be increased for behavioural changes (and new functionalities)
	* <major number> should be increased for major revisions
	*
	* The number should be returned directly as string, e.g. return "1.0.2";
	*
	* @return	string		version number
	*/
	abstract function getVersion();
	
	abstract function isCore();
	
	abstract static function getComponentType();
	
	/**
	* Set Name.
	*
	* @param	string	$a_name	Name
	*/
	function setName($a_name)
	{
		$this->name = $a_name;
	}

	/**
	* Get Name.
	*
	* @return	string	Name
	*/
	function getName()
	{
		return $this->name;
	}

	/**
	* Set Sub Directory.
	*
	* @param	string	$a_subdirectory	Sub Directory
	*/
	function setSubDirectory($a_subdirectory)
	{
		$this->subdirectory = $a_subdirectory;
	}

	/**
	* Get Sub Directory.
	*
	* @return	string	Sub Directory
	*/
	function getSubDirectory()
	{
		return $this->subdirectory;
	}
	
}
?>
