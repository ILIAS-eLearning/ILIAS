<?php
/**
* Class NoteObject
*
* @author M.Maschke
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
require_once "classes/class.Object.php";

class NoteObject extends Object
{
	var $ilias;

	/**
	* Constructor
	* @access	public
	*/
	function NoteObject($a_id = 0,$a_call_by_reference = false)
	{
		global $ilias;
		$this->ilias =& $ilias;
		
		$this->Object($a_id,$a_call_by_reference);
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
		
		$note_id = createNewObject("note",$lo_title,$note_text);

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
