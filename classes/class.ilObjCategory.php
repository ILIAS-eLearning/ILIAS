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
* Class ilObjCategory
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @extends ilObject
*/

require_once "class.ilContainer.php";

class ilObjCategory extends ilContainer
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjCategory($a_id = 0,$a_call_by_reference = true)
	{
		global $ilBench;

		$ilBench->start("Core", "ilObjCategory_Constructor");

		$this->type = "cat";
		$this->ilContainer($a_id,$a_call_by_reference);

		$ilBench->stop("Core", "ilObjCategory_Constructor");
	}
	
	/**
	* copy all properties and subobjects of a category.
	* 
	* @access	public
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);

		// put here cat specific stuff
		
		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete category and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		// put here category specific stuff
		$query = "DELETE FROM object_translation WHERE obj_id=".$this->getId();
		$this->ilias->db->query($query);
		
		return true;
	}
	
	/**
	* get all translations from this category
	* 
	* @access	public
	* @return	array 
	*/
	function getTranslations()
	{
		$q = "SELECT * FROM object_translation WHERE obj_id = ".$this->getId()." ORDER BY lang_default DESC";
		$r = $this->ilias->db->query($q);
		
		$num = 0;

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data["Fobject"][$num]= array("title"	=> $row->title,
										  "desc"	=> $row->description,
										  "lang"	=> $row->lang_code
										  );
		$num++;
		}

		// first entry is always the default language
		$data["default_language"] = 0;

		return $data ? $data : array();	
	}
	
	// remove all Translations of current category
	function removeTranslations()
	{
		$q = "DELETE FROM object_translation WHERE obj_id= ".$this->getId();
		$this->ilias->db->query($q);
	}
	
	// add a new translation to current category
	function addTranslation($a_title,$a_desc,$a_lang,$a_lang_default)
	{
		if (empty($a_title))
		{
			$a_title = "NO TITLE";
		}

		$q = "INSERT INTO object_translation ".
			 "(obj_id,title,description,lang_code,lang_default) ".
			 "VALUES ".
			 "(".$this->getId().",'".ilUtil::prepareDBString($a_title)."','".ilUtil::prepareDBString($a_desc)."','".$a_lang."',".$a_lang_default.")";
		$this->ilias->db->query($q);

		return true;
	}

		
} // END class.ilObjCategory
?>
