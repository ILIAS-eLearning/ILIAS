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
* Meta Data class (element technical)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTechnical extends ilMDBase
{
	var $parent_obj = null;

	function ilMDTechnical(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_technical',
						 $a_id);

		if($a_id)
		{
			$this->read();
		}
	}

	// Methods for child objects (Format, Location, Requirement OrComposite)
	function &getFormatIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDFormat.php';

		return ilMDFormat::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getFormat($a_format_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDFormat.php';

		if(!$a_format_id)
		{
			return false;
		}
		return new ilMDFormat($this,$a_format_id);
	}
	function &addFormat()
	{
		include_once 'Services/MetaData/classes/class.ilMDFormat.php';

		return new ilMDFormat($this);
	}
	function &getLocationIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDLocation.php';

		return ilMDLocation::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getLocation($a_location_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDLocation.php';

		if(!$a_location_id)
		{
			return false;
		}
		return new ilMDLocation($this,$a_location_id);
	}
	function &addLocation()
	{
		include_once 'Services/MetaData/classes/class.ilMDLocation.php';

		return new ilMDLocation($this);
	}
	function &getRequirementIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDRequirement.php';

		return ilMDRequirement::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getRequirement($a_requirement_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDRequirement.php';

		if(!$a_requirement_id)
		{
			return false;
		}
		return new ilMDRequirement($this,$a_requirement_id);
	}
	function &addRequirement()
	{
		include_once 'Services/MetaData/classes/class.ilMDRequirement.php';

		return new ilMDRequirement($this);
	}
	function &getOrCompositeIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDOrComposite.php';

		return ilMDOrComposite::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getOrComposite($a_or_composite_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDOrComposite.php';

		if(!$a_or_composite_id)
		{
			return false;
		}
		return new ilMDOrComposite($this,$a_or_composite_id);
	}
	function &addOrComposite()
	{
		include_once 'Services/MetaData/classes/class.ilMDOrComposite.php';

		return new ilMDOrComposite($this);
	}

	// SET/GET
	function setSize($a_size)
	{
		$this->size = $a_size;
	}
	function getSize()
	{
		return $this->size;
	}
	function setInstallationRemarks($a_val)
	{
		$this->installation_remarks = $a_val;
	}
	function getInstallationRemarks()
	{
		return $this->installation_remarks;
	}
	function setInstallationRemarksLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->installation_remarks_language =& $lng_obj;
		}
	}
	function &getInstallationRemarksLanguage()
	{
		return is_object($this->installation_remarks_language) ? $this->installation_remarks_language : false;
	}
	function getInstallationRemarksLanguageCode()
	{
		return is_object($this->installation_remarks_language) ? $this->installation_remarks_language->getLanguageCode() : false;
	}
	function setOtherPlatformRequirements($a_val)
	{
		$this->other_platform_requirements = $a_val;
	}
	function getOtherPlatformRequirements()
	{
		return $this->other_platform_requirements;
	}
	function setOtherPlatformRequirementsLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->other_platform_requirements_language =& $lng_obj;
		}
	}
	function &getOtherPlatformRequirementsLanguage()
	{
		return is_object($this->other_platform_requirements_language) ? $this->other_platform_requirements_language : false;
	}
	function getOtherPlatformRequirementsLanguageCode()
	{
		return is_object($this->other_platform_requirements_language) 
			? $this->other_platform_requirements_language->getLanguageCode() 
			: false;
	}
	function setDuration($a_val)
	{
		$this->duration = $a_val;
	}
	function getDuration()
	{
		return $this->duration;
	}
	
	

	function save()
	{
		if($this->db->autoExecute('il_meta_technical',
								  $this->__getFields(),
								  DB_AUTOQUERY_INSERT))
		{
			$this->setMetaId($this->db->getLastInsertId());

			return $this->getMetaId();
		}

		return false;
	}

	function update()
	{
		if($this->getMetaId())
		{
			if($this->db->autoExecute('il_meta_technical',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_technical_id = '".$this->getMetaId()."'"))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_technical ".
				"WHERE meta_technical_id = '".$this->getMetaId()."'";
			
			$this->db->query($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> $this->getRBACId(),
					 'obj_id'	=> $this->getObjId(),
					 'obj_type'	=> ilUtil::prepareDBString($this->getObjType()),
					 'size'		=> ilUtil::prepareDBString($this->getSize()),
					 'installation_remarks' => ilUtil::prepareDBString($this->getInstallationRemarks()),
					 'installation_remarks_language' => ilUtil::prepareDBString($this->getInstallationRemarksLanguageCode()),
					 'other_platform_requirements' => ilUtil::prepareDBString($this->getOtherPlatformRequirements()),
					 'other_platform_requirements_language' => ilUtil::prepareDBString($this->getOtherPlatformRequirementsLanguageCode()),
					 'duration' => ilUtil::prepareDBString($this->getDuration()));
	}

	function read()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{

			$query = "SELECT * FROM il_meta_technical ".
				"WHERE meta_technical_id = '".$this->getMetaId()."'";

		
			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setSize($row->size);
				$this->setInstallationRemarks($row->installation_remarks);
				$this->setInstallationRemarksLanguage(new ilMDLanguageItem($row->installation_remarks_language));
				$this->setOtherPlatformRequirements($row->other_platform_requirements);
				$this->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($row->other_platform_requirements_language));
				$this->setDuration($row->duration);
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
		$writer->xmlStartTag('Technical');

		// Foramt
		foreach($this->getFormatIds() as $id)
		{
			$for =& $this->getFormat($id);
			$for->toXML($writer);
		}

		// Size
		if(strlen($this->getSize()))
		{
			$writer->xmlElement('Size',null,$this->getSize());
		}
		
		// Location
		foreach($this->getLocationIds() as $id)
		{
			$loc =& $this->getLocation($id);
			$loc->toXML($writer);
		}

		// Requirement
		foreach($this->getRequirementIds() as $id)
		{
			$req =& $this->getRequirement($id);
			$req->toXML($writer);
		}

		// OrComposite
		foreach($this->getOrCompositeIds() as $id)
		{
			$orc =& $this->getOrComposite($id);
			$orc->toXML($writer);
		}
		
		// InstallationRemarks
		if(strlen($this->getInstallationRemarks()))
		{
			$writer->xmlElement('InstallationRemarks',
								array('Language' => $this->getInstallationRemarksLanguageCode()),
								$this->getInstallationRemarks());
		}

		// OtherPlatformRequirements
		if(strlen($this->getOtherPlatformRequirements()))
		{
			$writer->xmlElement('OtherPlatformRequirements',
								array('Language' => $this->getOtherPlatformRequirementsLanguageCode()),
								$this->getOtherPlatformRequirements());
		}
		// Durtation
		if(strlen($this->getDuration()))
		{
			$writer->xmlElement('Duration',null,$this->getDuration());
		}
		
		$writer->xmlEndTag('Technical');

	}
	// STATIC
	function _getId($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_technical_id FROM il_meta_technical ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->meta_technical_id;
		}
		return false;
	}
}
?>