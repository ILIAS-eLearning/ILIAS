<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Forum data set class
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ingroup ModulesForum
 */
class ilForumDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions($a_entity)
	{
		switch ($a_entity)
		{
			case "frm":
				return array("4.1.0");
		}
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_target_release)
	{
		return "http://www.ilias.de/xml/Modules/Forum/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "frm")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						'Description' => 'text',
						"DefaultView" => "integer",
						"Pseudonyms" => "integer",
						"Statistics" => "integer",
						"PostingActivation" => "integer",
						"PresetSubject" => "integer",
						"PresetRe" => "integer",
						"NotificationType" => "text",
						"ForceNotification" => "integer",
						"ToggleNotification" => "integer"
						
						);
			}
		}
	}

	/**
	 * Read data
	 *
	 * @param
	 * @return
	 */
	function readData($a_entity, $a_version, $a_ids, $a_field = "")
	{
		global $ilDB;

		if (!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}
				
		if ($a_entity == "frm")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$query = 'SELECT * FROM frm_settings fs '.
					'JOIN object_data od ON fs.obj_id = od.obj_id '.
					'WHERE '.$ilDB->in('fs.obj_id', $a_ids, false, 'integer');
					
					$res = $ilDB->query($query);

					$set = array();
					while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
					{
						$set['Id'] = $row->obj_id;
						$set['Title'] = $row->title;
						$set['Description'] = $row->description;
						$set['DefaultView'] = (int) $row->default_view;
						$set['Pseudonyms'] = (int) $row->anonymized;
						$set['Statistics'] = (int) $row->statistics_enabled;
						$set['PostingActivation'] = (int) $row->post_activation;
						$set['PresetSubject'] = (int) $row->preset_subject;
						$set['PresetRe'] = (int) $row->add_re_subject;
						$set['NotificationType'] = $row->notification_type;
						$set['ForceNotification'] = (int) $row->admin_force_noti;
						$set['ToggleNotification'] = (int) $row->user_toggle_noti;
						
						$this->data[] = $set;
					}
					break;
			}
		}
	}




	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		switch ($a_entity)
		{
			case "frm":
				include_once("./Modules/Forum/classes/class.ilObjForum.php");

				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjForum();
					$newObj->setType("frm");
					$newObj->create(true);
				}
				
				include_once './Modules/Forum/classes/class.ilForumProperties.php';
				$newObjProp = ilForumProperties::getInstance($newObj->getId());
				
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				
				$newObjProp->setDefaultView((int) $a_rec['DefaultView']);
				$newObjProp->setAnonymisation((int) $a_rec['Pseudonyms']);
				$newObjProp->setStatisticsStatus((int) $a_rec['Statistics']);
				$newObjProp->setPostActivation((int) $a_rec['PostingActivation']);
				$newObjProp->setPresetSubject((int) $a_rec['PresetSubject']);
				$newObjProp->setAddReSubject((int) $a_rec['PresetRe']);
				$newObjProp->setNotificationType($a_rec['NotificationType']);
				$newObjProp->setAdminForceNoti((int) $a_rec['ForceNotification']);
				$newObjProp->setUserToggleNoti((int) $a_rec['ToggleNotification']);
				$newObjProp->insert();
				
				$newObj->update();
				
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/Forum", "frm", $a_rec["Id"], $newObj->getId());
				break;
		}
	}
}
?>