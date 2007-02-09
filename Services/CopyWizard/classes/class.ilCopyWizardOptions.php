<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCopyWizard 
*/

class ilCopyWizardOptions
{
	private $db;
	
	private $copy_id;
	private $source_id;
	private $options = array();	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_copy_id = 0)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->copy_id = $a_copy_id;
		
		if($this->copy_id)
		{
			$this->read();
		}	
	}
	
	/**
	 * Allocate a copy for further entries
	 *
	 * @access public
	 * 
	 */
	public function allocateCopyId()
	{
	 	$query = "SELECT MAX(copy_id) as latest FROM copy_wizard_options ";
	 	$res = $this->db->query($query);
	 	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
	 	
	 	$query = "INSERT INTO copy_wizard_options ".
	 		"SET copy_id = ".$this->db->quote($row->latest + 1);
	 	$this->db->query($query);
	 	
	 	return $this->copy_id = $row->latest + 1;
	}
	
	/**
	 * Get entry by source
	 *
	 * @access public
	 * @param int source ref_id
	 * 
	 */
	public function getOptions($a_source_id)
	{
		if(isset($this->options[$a_source_id]) and is_array($this->options[$a_source_id]))
		{
			return $this->options[$a_source_id];
		}
		return array();
	}
	
	/**
	 * Add new entry
	 *
	 * @access public
	 * @param int ref_id of source
	 * @param array array of options
	 * 
	 */
	public function addEntry($a_source_id,$a_options)
	{
		if(!is_array($a_options))
		{
			return false;
		}
		
		$query 	= "INSERT INTO copy_wizard_options ".
			"SET copy_id = ".$this->db->quote($this->copy_id).", ".
			"source_id = ".$this->db->quote($a_source_id).", ".
			"options = '".addslashes(serialize($a_options))."' ";
		$res = $this->db->query($query);
		return true;
	}
	
	/**
	 * Delete all entries
	 *
	 * @access public
	 * 
	 */
	public function deleteAll()
	{
	 	$query = "DELETE FROM copy_wizard_options ".
	 		"WHERE copy_id = ".$this->db->quote($this->copy_id);
	 	$this->db->query($query);
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function read()
	{
	 	$query = "SELECT * FROM copy_wizard_options ".
	 		"WHERE copy_id = ".$this->db->quote($this->copy_id);
	 	$res = $this->db->query($query);
	 	
	 	$this->options = array();
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->options[$row->source_id] = unserialize(stripslashes($row->options));
	 	}

		return true;
	}
}


?>