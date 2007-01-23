<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

define("IL_NOTE_PRIVATE", 1);
define("IL_NOTE_PUBLIC", 2);

define("IL_NOTE_UNLABELED", 0);
define("IL_NOTE_IMPORTANT", 1);
define("IL_NOTE_QUESTION", 2);
define("IL_NOTE_PRO", 3);
define("IL_NOTE_CONTRA", 4);

/** @defgroup ServicesNotes Services/Notes
 */

/**
* Note class. Represents a single note.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
* @ingroup ServicesNotes
*/
class ilNote
{
	
	/**
	* constructor
	*/
	function ilNote($a_id = 0)
	{
		if ($a_id > 0)
		{
			$this->id = $a_id;
			$this->read();
		}
	}
	
	/**
	* set id
	*
	* @param	int		note id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* get id
	*
	* @return	int		note id
	*/
	function getId()
	{
		return $this->id;
	}
	
	/**
	* set assigned object
	*
	* @param	$a_type		string		type of the object (e.g st,pg,crs ...)
	* @param	$a_rep_obj_id	int		object id (NOT ref_id!) of repository object (e.g for page objects
	*									the obj_id of the learning module; for personal desktop this
	*									is set to 0)
	* @param	$a_obj_id	int			object id (e.g for page objects the obj_id of the page object)
	*									for, this is set to 0 for normal repository objects like forums ...
	*/
	function setObject($a_obj_type, $a_rep_obj_id, $a_obj_id = 0)
	{		
		$this->rep_obj_id = $a_rep_obj_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
	}
	
	function getObject()
	{
		return array("rep_obj_id" => $this->rep_obj_id,
			"obj_id" => $this->obj_id,
			"obj_type" => $this->obj_type);
	}
	
	
	/**
	* set type
	*
	* @param	int		IL_NOTE_PUBLIC | IL_NOTE_PRIVATE
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* get type
	*
	* @return	int		IL_NOTE_PUBLIC | IL_NOTE_PRIVATE
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* set author
	*
	* @param	int		author user id
	*/
	function setAuthor($a_user_id)
	{
		$this->author = $a_user_id;
	}

	/**
	* get author
	*
	* @return	int		user id
	*/
	function getAuthor()
	{
		return $this->author;
	}
	
	/**
	* set text
	*
	* @param	string		text
	*/
	function setText($a_text)
	{
		$this->text = $a_text;
	}

	/**
	* get text
	*
	* @return	string	text
	*/
	function getText()
	{
		return $this->text;
	}

	/**
	* set subject
	*
	* @param	string		text
	*/
	function setSubject($a_subject)
	{
		$this->subject = $a_subject;
	}

	/**
	* get subject
	*
	* @return	string	subject
	*/
	function getSubject()
	{
		return $this->subject;
	}
	
	/**
	* set creation date
	*
	* @param	string	creation date
	*/
	function setCreationDate($a_date)
	{
		$this->creation_date = $a_date;
	}

	/**
	* get creation date
	*
	* @return	string	creation date
	*/
	function getCreationDate()
	{
		return $this->creation_date;
	}
	
	/**
	* set update date
	*
	* @param	string	update date
	*/
	function setUpdateDate($a_date)
	{
		$this->update_date = $a_date;
	}

	/**
	* get update date
	*
	* @return	string	update date
	*/
	function getUpdateDate()
	{
		return $this->update_date;
	}
	
	/**
	* set label
	*
	* @param	int		IL_NOTE_UNLABELED | IL_NOTE_IMPORTANT | IL_NOTE_QUESTION
	*					| IL_NOTE_PRO | IL_NOTE_CONTRA
	*/
	function setLabel($a_label)
	{
		return $this->label = $a_label;
	}
	
	/**
	* get label
	*
	* @return	int		IL_NOTE_UNLABELED | IL_NOTE_IMPORTANT | IL_NOTE_QUESTION
	*					| IL_NOTE_PRO | IL_NOTE_CONTRA
	*/
	function getLabel()
	{
		return $this->label;
	}
	
	function create()
	{
		global $ilDB;
		
		$q = "INSERT INTO note (rep_obj_id, obj_id, obj_type, type,".
			"author, text, subject, label, creation_date) VALUES (".
			$ilDB->quote($this->rep_obj_id).",".
			$ilDB->quote($this->obj_id).",".
			$ilDB->quote($this->obj_type).",".
			$ilDB->quote($this->type).",".
			$ilDB->quote($this->author).",".
			$ilDB->quote($this->text).",".
			$ilDB->quote($this->subject).",".
			$ilDB->quote($this->label).",".
			"now())";
		$ilDB->query($q);
		
		$this->id = $ilDB->getLastInsertId();
		$this->creation_date = ilNote::_lookupCreationDate($this->getId());
	}

	function update()
	{
		global $ilDB;
		
		$q = "UPDATE note SET ".
			"rep_obj_id = ".$ilDB->quote($this->rep_obj_id).",".
			"obj_id = ".$ilDB->quote($this->obj_id).",".
			"obj_type = ".$ilDB->quote($this->obj_type).",".
			"type = ".$ilDB->quote($this->type).",".
			"author = ".$ilDB->quote($this->author).",".
			"text = ".$ilDB->quote($this->text).",".
			"subject = ".$ilDB->quote($this->subject).",".
			"update_date = now(),".
			"label = ".$ilDB->quote($this->label).
			"WHERE id =".$ilDB->quote($this->getId());

		$ilDB->query($q);
		
		$this->update_date = ilNote::_lookupUpdateDate($this->getId());
	}

	function read()
	{
		global $ilDB;
		
		$q = "SELECT * FROM note WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($q);
		$note_rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setAllData($note_rec);
	}
	
	/**
	* delete note
	*/
	function delete()
	{
		global $ilDB;
		
		$q = "DELETE FROM note WHERE id = ".
			$ilDB->quote($this->getId());
		$ilDB->query($q);
	}
	
	/**
	* set all note data by record array
	*/
	function setAllData($a_note_rec)
	{
		$this->setId($a_note_rec["id"]);
		$this->setObject($a_note_rec["obj_type"], $a_note_rec["rep_obj_id"], $a_note_rec["obj_id"]);
		$this->setType($a_note_rec["type"]);
		$this->setAuthor($a_note_rec["author"]);
		$this->setText($a_note_rec["text"]);
		$this->setSubject($a_note_rec["subject"]);
		$this->setLabel($a_note_rec["label"]);
		$this->setCreationDate($a_note_rec["creation_date"]);
		$this->setUpdateDate($a_note_rec["update_date"]);
	}
	
	/**
	* lookup creation date of note
	*/
	function _lookupCreationDate($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM note WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($q);
		$note_rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		return $note_rec["creation_date"];
	}

	/**
	* lookup update date of note
	*/
	function _lookupUpdateDate($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM note WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($q);
		$note_rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		return $note_rec["update_date"];
	}
	
	/**
	* get all notes related to a specific object
	*/
	function _getNotesOfObject($a_rep_obj_id, $a_obj_id, $a_obj_type,
		$a_type = IL_NOTE_PRIVATE, $a_incl_sub = false, $a_filter = "",
		$a_all_public = "y")
	{
		global $ilDB, $ilUser;
		
		$author_where = ($a_type == IL_NOTE_PRIVATE || $a_all_public == "n")
			? " AND author = ".$ilDB->quote($ilUser->getId())
			: "";

		$sub_where = (!$a_incl_sub)
			? " AND obj_id = ".$ilDB->quote($a_obj_id).
			  " AND obj_type = ".$ilDB->quote($a_obj_type)
			: "";
		
		$q = "SELECT * FROM note WHERE ".
			" rep_obj_id = ".$ilDB->quote($a_rep_obj_id).
			$sub_where.
			" AND type = ".$ilDB->quote($a_type).
			$author_where.
			" ORDER BY creation_date DESC";

		$set = $ilDB->query($q);
		$notes = array();
		while($note_rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($a_filter != "")
			{
				if (!is_array($a_filter))
				{
					$a_filter = array($a_filter);
				}
				if (!in_array($note_rec["id"], $a_filter))
				{
					continue;
				}
			}
			$cnt = count($notes);
			$notes[$cnt] = new ilNote();
			$notes[$cnt]->setAllData($note_rec);
		}
		
		return $notes;
	}

	/**
	* get last notes of current user
	*/
	function _getLastNotesOfUser()
	{
		global $ilDB, $ilUser;
		
		$q = "SELECT * FROM note WHERE ".
			" type = ".$ilDB->quote(IL_NOTE_PRIVATE).
			" AND author = ".$ilDB->quote($ilUser->getId()).
			" ORDER BY creation_date DESC";

		$ilDB->quote($q);
		$set = $ilDB->query($q);
		$notes = array();
		while($note_rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$cnt = count($notes);
			$notes[$cnt] = new ilNote();
			$notes[$cnt]->setAllData($note_rec);
		}
		
		return $notes;
	}
	
	/**
	* get all related objects for user
	*/
	function _getRelatedObjectsOfUser()
	{
		global $ilDB, $ilUser;
		
		$q = "SELECT DISTINCT rep_obj_id FROM note WHERE ".
			" type = ".$ilDB->quote(IL_NOTE_PRIVATE).
			" AND author = ".$ilDB->quote($ilUser->getId()).
			" ORDER BY rep_obj_id";

		$ilDB->quote($q);
		$set = $ilDB->query($q);
		$reps = array();
		while($rep_rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$reps[] = array("rep_obj_id" => $rep_rec["rep_obj_id"]);
		}
		
		return $reps;
	}

}
?>