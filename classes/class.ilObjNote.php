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
* Class ilObjNote
*
* @author M.Maschke
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

require_once "classes/class.ilObject.php";

class ilObjNote extends ilObject
{
	var $ilias;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjNote($a_id = 0,$a_call_by_reference = false)
	{
		global $ilias;
		$this->ilias =& $ilias;
		
		$this->type = "note";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* get content of note
	* 
	* @param	integer	a_note_id
	* @return	array   note_data
	* @access	public
	*/
	function viewObject($a_note_id)
	{
		$query = "SELECT * FROM note_data WHERE note_id = '".$a_note_id."'";
		$res = $this->ilias->db->query($query);
		$note_data = $res->fetchRow(DB_FETCHMODE_OBJECT);
		return $note_data;		
	}

	/**
	* create a note object (entry) in object_data
	* 
	* @param	string  title of learning object, i.e
	* @param	string  short description of note
	* @return	integer	note_id
	* @access	public
	*/
	function createObject($lo_title, $note_text)
	{
		global $rbacadmin, $rbacsystem;		// not used (see below)

		$this->setType("note");
		$this->setTitle($lo_title);
		$this->setDescription($note_text);
		parent::create();
		$note_id = $this->getId();

		return $note_id;	
	}

	/**
	* save note object 
	* 
	* @param	integer id of note object
	* @param	integer id of referenced LO
	* @param	string	text
	* @access	public
	*/
	function saveNote($obj_id, $ref_lo, $lo_title, $text, $rate)
	{
		$create_date = date("Y-m-d G:i:s");

		$query = "INSERT INTO note_data (note_id, lo_id,  text, create_date, important, good, question, bad)".
				 " VALUES ('".$obj_id."','".$ref_lo."','".$text."','".$create_date."','".$rate["important"]."','".$rate["good"]."','".$rate["question"]."','".$rate["bad"]."')";

		$res = $this->ilias->db->query($query);
	}

	function updateNote($obj_id, $note_text, $rate)
	{
		$create_date = date("Y-m-d G:i:s");	// not used
		
		//update table note_data
		$q = "UPDATE note_data SET text='".$note_text."', question='".$rate["question"]."', ".
			 "important='".$rate["important"]."', good='".$rate["good"]."', ".
			 "bad='".$rate["bad"]."' WHERE note_id='".$obj_id."'";
		$this->ilias->db->query($q);

		//update table object_data
		$this->setTitle($note_text);
		$this->setDescription($note_text);
		$this->update();
	}
	function edit()
	{
	}
	
	function owner()
	{
	}
} //END class.NoteObject
?>
