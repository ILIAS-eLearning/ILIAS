<?php
/**
* Class NoteObject
*
* @author M.Maschke
* @version $Id: 
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
	function NoteObject()
	{
		global $ilias;
		$this->ilias =& $ilias;	
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
	
		$FNoteObject["title"] 	= "todo: titel der LO";
		$FNoteObject["desc"]  	= $text;			//todo:nach 20zeichen abschneiden
		$note_id	 			= createNewObject("note",$FNoteObject);

		return $note_id;	
	}

	/**
	* save note object 
	* 
	* @param	integer id of note object
	* @param	integer id of referenced LO
	* @param	text	text
	* @access	public
	*/
	function saveNote($obj_id, $ref_lo, $text)
	{
		$create_date = date("Y-m-d G:i:s");

		$query = "INSERT INTO note_data (note_id, lo_id, text, create_date)".
				 " VALUES ('".$obj_id."','".$ref_lo."','".$text."','".$create_date."')";

		$res = $this->ilias->db->query($query);
	}

	function edit()
	{
	}
	
	function owner()
	{
	}
	
	

}


?>
