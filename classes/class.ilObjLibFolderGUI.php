<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2003 ILIAS open source, University of Cologne            |
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
* Class ilObjLibFolderGUI
* GUI class for digital library objects.
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Jens Conze <jc@databay.de>
* @author Aresch Yavari <ay@databay.de>
* @version $Id$
* @extends ilObjectGUI
*/

require_once "class.ilObjectGUI.php";

class ilObjLibFolderGUI extends ilObjectGUI
{

	/**
	* Constructor
	* @param array $a_data
	* @param integer $a_id Reference or object ID.
	* @param boolean $a_reference Treat $a_id as reference ID (true) or object ID (false).
	* @return void
	* @access public
	*/
	function ilObjLibFolderGUI($a_data, $a_id, $a_reference)
	{
		parent::ilObjectGUI($a_data, $a_id, $a_reference);		
	}

}