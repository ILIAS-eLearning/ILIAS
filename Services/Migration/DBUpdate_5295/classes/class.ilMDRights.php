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
* Meta Data class (element rights)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDRights extends ilMDBase
{
	// SET/GET
	function setCosts($a_costs)
	{
		switch($a_costs)
		{
			case 'Yes':
			case 'No':
				$this->costs = $a_costs;
				return true;

			default:
				return false;
		}
	}
	function getCosts()
	{
		return $this->costs;
	}
	function setCopyrightAndOtherRestrictions($a_caor)
	{
		switch($a_caor)
		{
			case 'Yes':
			case 'No':
				$this->caor = $a_caor;
				return true;

			default:
				return false;
		}
	}
	function getCopyrightAndOtherRestrictions()
	{
		return $this->caor;
	}
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setDescriptionLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->description_language = $lng_obj;
		}
	}
	function &getDescriptionLanguage()
	{
		return is_object($this->description_language) ? $this->description_language : false;
	}
	function getDescriptionLanguageCode()
	{
		return is_object($this->description_language) ? $this->description_language->getLanguageCode() : false;
	}

	function save()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$fields = $this->__getFields();
		$fields['meta_rights_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_rights'));
		
		if($this->db->insert('il_meta_rights',$fields))
		{
			$this->setMetaId($next_id);
			return $this->getMetaId();
		}
		return false;
	}

	function update()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if($this->getMetaId())
		{
			if($this->db->update('il_meta_rights',
									$this->__getFields(),
									array("meta_rights_id" => array('integer',$this->getMetaId()))))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_rights ".
				"WHERE meta_rights_id = ".$ilDB->quote($this->getMetaId() ,'integer');
			
			$this->db->query($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> array('integer',$this->getRBACId()),
					 'obj_id'	=> array('integer',$this->getObjId()),
					 'obj_type'	=> array('text',$this->getObjType()),
					 'costs'	=> array('text',$this->getCosts()),
					 'cpr_and_or' => array('text',$this->getCopyrightAndOtherRestrictions()),
					 'description' => array('text',$this->getDescription()),
					 'description_language' => array('text',$this->getDescriptionLanguageCode()));
	}

	function read()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguageItem.php';


		if($this->getMetaId())
		{

			$query = "SELECT * FROM il_meta_rights ".
				"WHERE meta_rights_id = ".$ilDB->quote($this->getMetaId() ,'integer');

		
			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setDescription($row->description);
				$this->setDescriptionLanguage(new ilMDLanguageItem($row->description_language));
				$this->setCosts($row->costs);
				$this->setCopyrightAndOtherRestrictions($row->cpr_and_or);
			}
			return true;
		}
		return false;
	}
				
	/*
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
		$writer->xmlStartTag('Rights',array('Cost' => $this->getCosts()
											? $this->getCosts()
											: 'No',
											'CopyrightAndOtherRestrictions' => $this->getCopyrightAndOtherRestrictions()
											? $this->getCopyrightAndOtherRestrictions()
											: 'No'));
		include_once './Services/Migration/DBUpdate_5295/classes/class.ilMDCopyrightSelectionEntry.php';
		$writer->xmlElement(
			'Description',array('Language' => $this->getDescriptionLanguageCode()
												? $this->getDescriptionLanguageCode()
												: 'en'),
			ilMDCopyrightSelectionEntry::lookupCopyyrightTitle($this->getDescription())
		);
		$writer->xmlEndTag('Rights');
	}
	
	/**
	 * Lookup description (copyright)
	 *
	 * @access public
	 * @param int rbac_id
	 * @param int obj_id
	 * 
	 */
	public static function _lookupDescription($a_rbac_id,$a_obj_id)
	{
	 	global $DIC;

	 	$ilDB = $DIC['ilDB'];
	 	
	 	$query = "SELECT description FROM il_meta_rights ".
	 		"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
	 		"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
	 	$res = $ilDB->query($query);
	 	$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
	 	return $row->description ? $row->description : '';
	}

	// STATIC
	static function _getId($a_rbac_id,$a_obj_id)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$query = "SELECT meta_rights_id FROM il_meta_rights ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			return $row->meta_rights_id;
		}
		return false;
	}
}
?>