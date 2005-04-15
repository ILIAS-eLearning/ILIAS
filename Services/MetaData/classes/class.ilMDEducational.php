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
* Meta Data class (element educational)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDEducational extends ilMDBase
{
	var $parent_obj = null;

	function ilMDEducational(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_educational',
						 $a_id);

		if($a_id)
		{
			$this->read();
		}
	}

	// Methods for child objects (TypicalAgeRange, Description, Language)
	function &getTypicalAgeRangeIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDTypicalAgeRange.php';

		return ilMDTypicalAgeRange::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getTypicalAgeRange($a_typical_age_range_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDTypicalAgeRange.php';

		if(!$a_typical_age_range_id)
		{
			return false;
		}
		return new ilMDTypicalAgeRange($this,$a_typical_age_range_id);
	}
	function &addTypicalAgeRange()
	{
		include_once 'Services/MetaData/classes/class.ilMDTypicalAgeRange.php';

		return new ilMDTypicalAgeRange($this);
	}
	function &getDescriptionIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDDescription.php';

		return ilMDDescription::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getDescription($a_description_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDDescription.php';

		if(!$a_description_id)
		{
			return false;
		}
		return new ilMDDescription($this,$a_description_id);
	}
	function &addDescription()
	{
		include_once 'Services/MetaData/classes/class.ilMDDescription.php';

		return new ilMDDescription($this);
	}
	function &getLanguageIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

		return ilMDLanguage::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getLanguage($a_language_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

		if(!$a_language_id)
		{
			return false;
		}
		return new ilMDLanguage($this,$a_language_id);
	}
	function &addLanguage()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';
		
		return new ilMDLanguage($this);
	}

	// SET/GET
	function setInteractivityType($a_iat)
	{
		$this->interactivity_type = $a_iat;
	}
	function getInteractivityType()
	{
		return $this->interactivity_type;
	}
	function setLearningResourceType($a_lrt)
	{
		$this->learning_resource_type = $a_lrt;
	}
	function getLearningResourceType()
	{
		return $this->learning_resource_type;
	}
	function setInteractivityLevel($a_iat)
	{
		$this->interactivity_level = $a_iat;
	}
	function getInteractivityLevel()
	{
		return $this->interactivity_level;
	}
	function setSemanticDensity($a_sd)
	{
		$this->semantic_density = $a_sd;
	}
	function getSemanticDensity()
	{
		return $this->semantic_density;
	}
	function setIntendedEndUserRole($a_ieur)
	{
		$this->intended_end_user_role = $a_ieur;
	}
	function getIntendedEndUserRole()
	{
		return $this->intended_end_user_role;
	}
	function setContext($a_context)
	{
		$this->context = $a_context;
	}
	function getContext()
	{
		return $this->context;
	}
	function setDifficulty($a_difficulty)
	{
		$this->difficulty = $a_difficulty;
	}
	function getDifficulty()
	{
		return $this->difficulty;
	}
	function setTypicalLearningTime($a_tlt)
	{
		$this->typical_learning_time = $a_tlt;
	}
	function getTypicalLearningTime()
	{
		return $this->typical_learning_time;
	}


	function save()
	{
		if($this->db->autoExecute('il_meta_educational',
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
			if($this->db->autoExecute('il_meta_educational',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_educational_id = '".$this->getMetaId()."'"))
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
			$query = "DELETE FROM il_meta_educational ".
				"WHERE meta_educational_id = '".$this->getMetaId()."'";
			
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
					 'interactivity_type' => ilUtil::prepareDBString($this->getInteractivityType()),
					 'learning_resource_type' => ilUtil::prepareDBString($this->getLearningResourceType()),
					 'interactivity_level' => ilUtil::prepareDBString($this->getInteractivityLevel()),
					 'semantic_density' => ilUtil::prepareDBString($this->getSemanticDensity()),
					 'intended_end_user_role' => ilUtil::prepareDBString($this->getIntendedEndUserRole()),
					 'context' => ilUtil::prepareDBString($this->getContext()),
					 'difficulty' => ilUtil::prepareDBString($this->getDifficulty()),
					 'typical_learning_time' => ilUtil::prepareDBString($this->getTypicalLearningTime()));
	}

	function read()
	{
		if($this->getMetaId())
		{

			$query = "SELECT * FROM il_meta_educational ".
				"WHERE meta_educational_id = '".$this->getMetaId()."'";

		
			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setInteractivityType($row->interactivity_type);
				$this->setLearningResourceType($row->learning_resource_type);
				$this->setInteractivityLevel($row->interactivity_level);
				$this->setSemanticDensity($row->semantic_density);
				$this->setIntendedEndUserRole($row->intended_end_user_role);
				$this->setContext($row->context);
				$this->setDifficulty($row->difficulty);
				$this->setTypicalLearningTime($row->typical_learning_time);
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
		$writer->xmlStartTag('Educational',
							 array('InteractivityType' => $this->getInteractivityType(),
								   'RearningResourceType' => $this->getLearningResourceType(),
								   'InteractivityLevel' => $this->getInteractivityLevel(),
								   'SemanticDensity' => $this->getSemanticDensity(),
								   'IntendedEndUserRole' => $this->getIntendedEndUserRole(),
								   'Context' => $this->getContext(),
								   'Difficulty' => $this->getDifficulty()));
							 
		// TypicalAgeRange
		foreach($this->getTypicalAgeRangeIds() as $id)
		{
			$key =& $this->getTypicalAgeRange($id);
			$key->toXML($writer);
		}
		// TypicalLearningTime
		$writer->xmlElement('TypicalLearningTime',null,$this->getTypicalLearningTime());

		// Description
		foreach($this->getDescriptionIds() as $id)
		{
			$key =& $this->getDescription($id);
			$key->toXML($writer);
		}
		// Language
		foreach($this->getLanguageIds() as $id)
		{
			$lang =& $this->getLanguage($id);
			$lang->toXML($writer);
		}
		$writer->xmlEndTag('Educational');
	}
	// STATIC
	function _getId($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_educational_id FROM il_meta_educational ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->meta_educational_id;
		}
		return false;
	}
}
?>