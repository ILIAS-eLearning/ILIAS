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
* Class Folder
* core functions for folder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias-folder
*/


class ilFolder
{
	/**
	* ilias object
	* @var object ilias
	* @access private
	*/
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* tree object
	* @var object tree
	* @access private
	*/
	var $gtree;

	/**
	* group_id
	* @var int group_id
	* @access private
	*/
	var $group_id;



	/**
	* table name of table mail object data
	* @var string
	* @access private
	*/
	var $table_mail_obj_data;

	/**
	* table name of tree table
	* @var string
	* @access private
	*/
	var $table_tree;

	/**
	* Constructor
	* @access	public
	*/
	function ilFolder($a_group_id)
	{
		require_once("classes/class.ilTree.php");
		global $ilias,$lng;

		$this->ilias = &$ilias;
		$this->lng = &$lng;
		$this->group_id = $a_group_id;
		
		$this->table_mail_obj_data = 'obj_data';
		$this->table_tree = 'grp_tree';
		
		$this->gtree = new ilTree($this->group_id);
		$this->gtree->setTableNames($this->table_tree,$this->table_mail_obj_data);
	}
}
?>
