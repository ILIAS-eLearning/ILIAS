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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesMetaData 
*/
class ilMDCopyrightSelectionEntry
{
	protected $db;
	
	private $entry_id;
	private $title;
	private $decription;
	private $copyright;
	private $costs;
	private $language;
	private $copyright_and_other_restrictions;
	

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int entry id
	 * 
	 */
	public function __construct($a_entry_id)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->entry_id = $a_entry_id;
	 	$this->read();
	}
	
	/**
	 * get entries
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getEntries()
	{
		global $ilDB;
		
		$query = "SELECT entry_id FROM il_md_cpr_selections ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$entries[] = new ilMDCopyrightSelectionEntry($row->entry_id);
		}
		return $entries ? $entries : array();
	}
	
	/**
	 * Lookup copyright title. 
	 * Currently used for export of meta data
	 * @param type $a_cp_string
	 */
	public static function lookupCopyyrightTitle($a_cp_string)
	{
		global $ilDB;
		
		if(!$entry_id = self::_extractEntryId($a_cp_string))
		{
			return $a_cp_string;
		}
				
		$query = "SELECT title FROM il_md_cpr_selections ".
			"WHERE entry_id = ".$ilDB->quote($entry_id)." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
		return $row->title ? $row->title : '';
	}


	/**
	 * lookup copyright by entry id
	 *
	 * @access public
	 * @static
	 *
	 * @param string copyright string il_copyright_entry__IL_INST_ID__ENTRY_ID
	 */
	public static function _lookupCopyright($a_cp_string)
	{
		global $ilDB;
		
		if(!$entry_id = self::_extractEntryId($a_cp_string))
		{
			return $a_cp_string;
		}
				
		$query = "SELECT copyright FROM il_md_cpr_selections ".
			"WHERE entry_id = ".$ilDB->quote($entry_id)." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
		return $row->copyright ? $row->copyright : '';
	}
	
	/**
	 * extract entry id
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _extractEntryId($a_cp_string)
	{
		if(!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/',$a_cp_string,$matches))
		{
			return 0;
		}
		if($matches[1] != IL_INST_ID)
		{
			return 0;
		}
		return $matches[2] ? $matches[2] : 0;
	}
	
	/**
	 * get usage
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getUsage()
	{
		return $this->usage;
	}
	
	/**
	 * get entry id
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getEntryId()
	{
	 	return $this->entry_id;
	}
	
	/**
	 * set title
	 *
	 * @access public
	 * @param string title
	 * 
	 */
	public function setTitle($a_title)
	{
	 	$this->title = $a_title;
	}
	
	/**
	 * get title
	 *
	 * @access public
	 * 
	 */
	public function getTitle()
	{
	 	return $this->title;
	}
	
	/**
	 * set description
	 *
	 * @access public
	 * @param string description
	 * 
	 */
	public function setDescription($a_desc)
	{
	 	$this->description = $a_desc;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 */
	public function getDescription()
	{
	 	return $this->description;
	}
	
	/**
	 * set copyright
	 *
	 * @access public
	 * @param string $copyright
	 * 
	 */
	public function setCopyright($a_copyright)
	{
	 	$this->copyright = $a_copyright;
	}
	
	/**
	 * get copyright
	 *
	 * @access publi 
	 */
	public function getCopyright()
	{
	 	return $this->copyright;
	}
	
	/**
	 * set costs
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setCosts($a_costs)
	{
	 	$this->costs = $a_costs;
	}
	
	/**
	 * get costs
	 *
	 * @access public
	 */
	public function getCosts()
	{
	 	return $this->costs;
	}
	
	/**
	 * set language
	 *
	 * @access public
	 * @param string language key
	 * 
	 */
	public function setLanguage($a_lang_key)
	{
	 	$this->language = $a_lang_key;
	}
	
	/**
	 * get language
	 *
	 * @access public
	 * 
	 */
	public function getLanguage()
	{
	 	return $this->language;
	}
	
	/**
	 * set copyright and other restrictions
	 *
	 * @access public
	 * @param bool copyright and other restrictions
	 */
	public function setCopyrightAndOtherRestrictions($a_status)
	{
		$this->copyright_and_other_restrictions = $a_status;
	}
	
	/**
	 * get copyright and other restrictions
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getCopyrightAndOtherRestrictions()
	{
	 	// Fixed
	 	return true;
	}
	
	/**
	 * Add entry
	 *
	 * @access public
	 */
	public function add()
	{
	 	global $ilDB;
	 	
	 	$next_id = $ilDB->nextId('il_md_cpr_selections');
	 	
	 	$ilDB->insert('il_md_cpr_selections',array(
	 		'entry_id'			=> array('integer',$next_id),
	 		'title'				=> array('text',$this->getTitle()),
	 		'description'		=> array('clob',$this->getDescription()),
	 		'copyright'			=> array('clob',$this->getCopyright()),
	 		'language'			=> array('text',$this->getLanguage()),
	 		'costs'				=> array('integer',$this->getCosts()),
	 		'cpr_restrictions'	=> array('integer',$this->getCopyrightAndOtherRestrictions())
	 	));
	 	$this->entry_id = $next_id;
		return true;
	}
	
	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
		global $ilDB;

	 	$ilDB->update('il_md_cpr_selections',array(
	 		'title'				=> array('text',$this->getTitle()),
	 		'description'		=> array('clob',$this->getDescription()),
	 		'copyright'			=> array('clob',$this->getCopyright()),
	 		'language'			=> array('text',$this->getLanguage()),
	 		'costs'				=> array('integer',$this->getCosts()),
	 		'cpr_restrictions'	=> array('integer',$this->getCopyrightAndOtherRestrictions())
		 	),array(
		 		'entry_id'			=> array('integer',$this->getEntryId())
	 	));
		return true;	 		
	}
	
	/**
	 * delete
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM il_md_cpr_selections ".
	 		"WHERE entry_id = ".$this->db->quote($this->getEntryId() ,'integer')." ";
	 	$res = $ilDB->manipulate($query);
			
	}	
	
	/**
	 * validate
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function validate()
	{
	 	if(!strlen($this->getTitle()))
	 	{
	 		return false;
	 	}
	 	return true;
	}
	
	/**
	 * Read entry
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM il_md_cpr_selections ".
	 		"WHERE entry_id = ".$this->db->quote($this->entry_id ,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
	 	{
	 		$this->setTitle($row->title);
	 		$this->setDescription($row->description);
	 		$this->setCopyright($row->copyright);
	 		$this->setLanguage($row->language);
	 		$this->setCosts($row->costs);
	 		// Fixed
	 		$this->setCopyrightAndOtherRestrictions(true);
	 	}
	 	
	 	$desc = $ilDB->quote('il_copyright_entry__'.IL_INST_ID.'__'.$this->getEntryId(),'text');
	 	$query = "SELECT count(meta_rights_id) used FROM il_meta_rights ".
	 		"WHERE description = ".$ilDB->quote($desc ,'text');
	 	$res = $this->db->query($query);
	 	$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
	 	$this->usage = $row->used;
	}
}
?>