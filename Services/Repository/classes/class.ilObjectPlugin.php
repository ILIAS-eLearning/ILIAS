<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

include_once("./classes/class.ilObject.php");

/*
* Object class for plugins. This one wraps around ilObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
abstract class ilObjectPlugin extends ilObject
{
	private $object;
	
	/**
	* Constructor.
	*/
	function __construct($a_ref_id = 0)
	{
		$this->initType();
		parent::__construct($a_id = 0, true);
		$this->plugin =
			ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
				ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType()));
		if (!is_object($this->plugin))
		{
			die("ilObjectPluginGUI: Could not instantiate plugin object for type ".$this->getType().".");
		}
	}
	
	abstract function initType();
	
	/**
	* Get plugin object
	*
	* @return	object	plugin object
	*/
	final private function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	* Wrapper for txt function
	*/
	final protected function txt($a_var)
	{
		return $this->getPlugin()->txt($a_var);
	}
}
