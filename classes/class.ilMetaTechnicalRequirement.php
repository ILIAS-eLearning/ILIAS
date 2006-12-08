<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilMetaTechnicalRequirement
*
* Handles Technical Requirement of Meta Data (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilMetaTechnicalRequirement
{
	var $ilias;
	var $type;
	var $name;
	var $min_version;
	var $max_version;


	/**
	* Constructor
	*
	* @param	string		$a_type		OperatingSystem | Browser
	* @access	public
	*/
	function ilMetaTechnicalRequirement($a_type)
	{
		global $ilias;

		$this->type = $a_type;
	}

	/**
	* set requirement type
	*
	* @param	string		$a_type		OperatingSystem | Browser
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* get requirement type
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* set requirement system/browser name
	*
	* @param	string		$a_name		(PC-DOS | MS-Windows | MacOS | Unix | Multi-OS | None)
	*									for type OperatingSystem
	*									(Any | NetscapeCommunicator | MS-InternetExplorer | Opera | Amaya | Mozilla)
	*									for type Browser
	*/
	function setName($a_name)
	{
		$this->name = $a_name;
	}

	/**
	* get requirement system/browser name
	*/
	function getName()
	{
		return $this->name;
	}

	/**
	* set minimum version
	*
	* @param	string		$a_min_version		minimum version
	*/
	function setMinVersion($a_min_version)
	{
		$this->min_version = $a_min_version;
	}

	/**
	* get minimum version
	*/
	function getMinVersion()
	{
		return $this->min_version;
	}

	/**
	* set maximum version
	*
	* @param	string		$a_max_version		maximum version
	*/
	function setMaxVersion($a_max_version)
	{
		$this->max_version = $a_max_version;
	}

	/**
	* get maximum version
	*/
	function getMaxVersion()
	{
		return $this->max_version;
	}

	function getXML()
	{
		$xml = "<Requirement>";
		$xml.= "<Type>";

		$min = ($this->getMinVersion != "")
			? "MinimumVersion=\"".$this->getMinVersion()."\""
			: "";
		$max = ($this->getMaxVersion != "")
			? "MaximumVersion=\"".$this->getMaxVersion()."\""
			: "";

		switch ($this->getType())
		{
			case "OperatingSystem":
				$xml.= "<OperatingSystem Name=\"".$this->getName()."\" $min $max>";
				break;

			case "Browser":
				$xml.= "<Browser Name=\"".$this->getName()."\" $min $max>";
				break;

			default:
				return false;
		}

		$xml.= "</Type>";
		$xml.= "</Requirement>";

		return $xml;
	}

}
?>
