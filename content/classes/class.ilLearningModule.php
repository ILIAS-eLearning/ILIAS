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

require_once("content/classes/class.ilMetaData.php");

/**
* Class ilLearningModule
*
* This class handles Learning Modules like ilObjLearningModule
* , maybe they will be merged sometime. This class is only an
* intermediate test class. All object_data storage and the like is done
* by ilObjLearningModule. This class represents a LearningModule of ILIAS DTD.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
class ilLearningModule
{
	var $ilias;
	var $meta_data;

	/**
	* Constructor
	* @access	public
	*/
	function ilLearningModule()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}


	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

}
?>
