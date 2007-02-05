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
* Meta Data class (element annotation)
*
* @author Stefan Meyer <smeyer@databay.de>
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDLifecycle extends ilMDBase
{
	function ilMDLifecycle($a_rbac_id = 0,$a_obj_id = 0,$a_obj_type = '')
	{
		parent::ilMDBase($a_rbac_id,
						 $a_obj_id,
						 $a_obj_type);
	}

	function getPossibleSubelements()
	{
		$subs['Contribute'] = 'meta_contribute';

		return $subs;
	}		

	// Get subelemsts 'Contribute'
	function &getContributeIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDContribute.php';

		return ilMDContribute::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),'meta_lifecycle');
	}
	function &getContribute($a_contribute_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDContribute.php';
		
		if(!$a_contribute_id)
		{
			return false;
		}
		$con =& new ilMDContribute();
		$con->setMetaId($a_contribute_id);

		return $con;
	}
	function &addContribute()
	{
		include_once 'Services/MetaData/classes/class.ilMDContribute.php';

		$con =& new ilMDContribute($this->getRBACId(),$this->getObjId(),$this->getObjType());
		$con->setParentId($this->getMetaId());
		$con->setParentType('meta_lifecycle');

		return $con;
	}


	// SET/GET
	function setStatus($a_status)
	{
		switch($a_status)
		{
			case 'Draft':
			case 'Final':
			case 'Revised':
			case 'Unavailable':
				$this->status = $a_status;

			default:
				return false;
		}
	}
	function getStatus()
	{
		return $this->status;
	}
	function setVersion($a_version)
	{
		$this->version = $a_version;
	}
	function getVersion()
	{
		return $this->version;
	}
	function setVersionLanguage($lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->version_language =& $lng_obj;
		}
	}
	function &getVersionLanguage()
	{
		return $this->version_language;
	}
	function getVersionLanguageCode()
	{
		if(is_object($this->version_language))
		{
			return $this->version_language->getLanguageCode();
		}
		return false;
	}

	function save()
	{
		if($this->db->autoExecute('il_meta_lifecycle',
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
		global $ilDB;
		
		if($this->getMetaId())
		{
			if($this->db->autoExecute('il_meta_lifecycle',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_lifecycle_id = ".$ilDB->quote($this->getMetaId())))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		global $ilDB;
		
		// Delete 'contribute'
		foreach($this->getContributeIds() as $id)
		{
			$con = $this->getContribute($id);
			$con->delete();
		}


		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_lifecycle ".
				"WHERE meta_lifecycle_id = ".$ilDB->quote($this->getMetaId());
			
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
					 'lifecycle_status'	=> $this->getStatus(),
					 'meta_version'		=> $this->getVersion(),
					 'version_language' => $this->getVersionLanguageCode());
	}

	function read()
	{
		global $ilDB;
		
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_lifecycle ".
				"WHERE meta_lifecycle_id = ".$ilDB->quote($this->getMetaId());

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setStatus($row->lifecycle_status);
				$this->setVersion($row->meta_version);
				$this->setVersionLanguage(new ilMDLanguageItem($row->version_language));
			}
		}
		return true;
	}

	/*
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
		$writer->xmlStartTag('Lifecycle',array('Status' => $this->getStatus() 
											   ? $this->getStatus() 
											   : 'Draft'));
		$writer->xmlElement('Version',array('Language' => $this->getVersionLanguageCode() 
											? $this->getVersionLanguageCode()
											: 'en'),
							$this->getVersion());

		// contribute
		$contributes = $this->getContributeIds();
		foreach($contributes as $id)
		{
			$con =& $this->getContribute($id);
			$con->toXML($writer);
		}
		if(!count($contributes))
		{
			include_once 'Services/MetaData/classes/class.ilMDContribute.php';
			$con = new ilMDContribute($this->getRBACId(),$this->getObjId());
			$con->toXML($writer);
		}
		$writer->xmlEndTag('Lifecycle');
	}

				

	// STATIC
	function _getId($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_lifecycle_id FROM il_meta_lifecycle ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id)." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id);


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->meta_lifecycle_id;
		}
		return false;
	}
}
?>