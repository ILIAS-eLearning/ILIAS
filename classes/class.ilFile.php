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
* Base class for all file (directory) operations
* This class is abstract and needs to be extended
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
class ilFile
{
	/**
	* Path of directory
	* @var string path
	* @access private
	*/
	var $path;

	/**
	* ilias object
	* @var object Ilias
	* @access public
	*/
	var $ilias;


	/**
	* Constructor
	* get ilias object
	* @access	public
	*/
	function ilFile()
	{
		global $ilias;

		$this->ilias = &$ilias;
	}

	/**
	* delete trailing slash of path variables
	* @param	string	path
	* @access	public
	* @return	string	path
	*/
	function deleteTrailingSlash($a_path)
	{
		// DELETE TRAILING '/'
		if (substr($a_path,-1) == '/' or substr($a_path,-1) == "\\")
		{
			$a_path = substr($a_path,0,-1);
		}

		return $a_path;
	}
}
?>