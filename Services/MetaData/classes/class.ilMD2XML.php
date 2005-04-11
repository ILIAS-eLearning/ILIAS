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
* Meta Data to XML class
*
* @package ilias-core
* @version $Id$
*/
include_once 'classes/class.ilXmlWriter.php';

class ilMD2XML extends ilXmlWriter
{
	var $md_obj = null;

	function ilMD2XML($a_rbac_id,$a_obj_id,$a_type)
	{
		$this->md_obj =& new ilMD($a_rbac_id,$a_obj_id,$a_type);

		parent::ilXmlWriter();
	}


	function startExport()
	{
		// Starts the xml export and calls all element classes
		$this->md_obj->toXML($this);
	}

	function getXML()
	{
		return $this->xmlDumpMem();
	}


}
?>