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
* Class ilObjFile
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";
require_once "class.ilGroupTree.php";

class ilObjFile extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjFile($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "file";
		$this->ilObject($a_id,$a_call_by_reference);
		
		if ($a_id != 0)
		{
			$this->read();
		}
	}
	
	function read()
	{
		$q = "SELECT * FROM file_data WHERE file_id = '".$this->getId()."'";
		$r = $this->ilias->db->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		
		$this->setFileName($row->file_name);
		$this->setFileType($row->file_type);
		$this->setFilePath(ilUtil::getWebspaceDir()."/files/file_".$this->getId());
	}

	function setFileName($a_name)
	{
		$this->filename = $a_name;
	}
	
	function getFileName()
	{
		return $this->filename;
	}
	
	function setFilePath($a_path)
	{
		$this->filepath = $a_path;
	}
	
	function getFilePath()
	{
		return $this->filepath;
	}
	
	function setFileType($a_type)
	{
		$this->filetype = $a_type;
	}
	
	function getFileType()
	{
		return $this->filetype;
	}

	/**
	* insert folder into grp_tree
	*
	*/
	function putInTree($a_parent_ref)
	{
		$grp_id = $this->getGroupId($a_parent_ref);
		
		$gtree = new ilGroupTree($grp_id);
		
		$gtree->insertNode($this->getRefId(), $a_parent_ref);
	}
	
	/**
	* get the tree_id of group where folder belongs to
	* TODO: function is also in ilGroupGUI and ilObjFile. merge them!!
	* @param	string	ref_id of parent under which folder is inserted
	* @access	private
	*/
	function getGroupId($a_parent_ref = 0)
	{
		if ($a_parent_ref == 0)
		{
			$a_parent_ref = $this->getRefId();
		}
		
		$q = "SELECT DISTINCT tree FROM grp_tree WHERE child='".$a_parent_ref."'";
		$r = $this->ilias->db->query($q);
		$row = $r->fetchRow();
		
		return $row[0];
	}
} // END class.ilObjFile
?>
