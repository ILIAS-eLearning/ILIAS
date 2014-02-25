<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Exercise data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesExercise
 */
class ilUserDataSet extends ilDataSet
{	
	protected $temp_picture_dirs = array();
	
	public $multi = array();
	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.3.0", "4.5.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Services/User/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		// user profile type
		if ($a_entity == "usr_profile")
		{
			switch ($a_version)
			{
				case "4.3.0":
				case "4.5.0":
					return array(
						"Id" => "integer",
						"Username" => "text",
						"Firstname" => "text",
						"Lastname" => "text",
						"Title" => "text",
						"Birthday" => "text",
						"Gender" => "text",
						"Institution" => "text",
						"Department" => "text",
						"Street" => "text",
						"Zipcode" => "text",
						"City" => "text",
						"Country" => "text",
						"SelCountry" => "text",
						"PhoneOffice" => "text",
						"PhoneHome" => "text",
						"PhoneMobile" => "text",
						"Fax" => "text",
						"Email" => "text",
						"Hobby" => "text",
						"ReferralComment" => "text",
						"Matriculation" => "text",
						"Delicious" => "text",
						"Latitude" => "text",
						"Longitude" => "text",
						"Picture" => "directory"
						);
			}
		}

		if ($a_entity == "usr_setting")
		{
			switch ($a_version)
			{
				case "4.3.0":
				case "4.5.0":
					return array(
						"UserId" => "integer",
						"Keyword" => "text",
						"Value" => "text"
					);
			}
		}

		if ($a_entity == "personal_data")
		{
			switch ($a_version)
			{
				case "4.3.0":
				case "4.5.0":
					return array(
						"Id" => "integer"
					);
			}
		}

		if ($a_entity == "usr_multi")
		{
			switch ($a_version)
			{
				case "4.5.0":
					return array(
						"UserId" => "integer",
						"FieldId" => "text",
						"Value" => "text"
					);
			}
		}
	}

	
	/**
	 * Get xml record
	 *
	 * @param
	 * @return
	 */
	function getXmlRecord($a_entity, $a_version, $a_set)
	{
		global $ilLog;
		
		if ($a_entity == "usr_profile")
		{
			$tmp_dir = ilUtil::ilTempnam();
			ilUtil::makeDir($tmp_dir);
			include_once("./Services/User/classes/class.ilObjUser.php");
			ilObjUser::copyProfilePicturesToDirectory($a_set["Id"], $tmp_dir);
			
			$this->temp_picture_dirs[$a_set["Id"]] = $tmp_dir;
			
			$a_set["Picture"] = $tmp_dir;
		}

		return $a_set;
	}

	/**
	 * After xml record writing hook record
	 *
	 * @param
	 * @return
	 */
	function afterXmlRecordWriting($a_entity, $a_version, $a_set)
	{
		if ($a_entity == "usr_profile")
		{
			// cleanup temp dirs for pictures
			$tmp_dir = $this->temp_picture_dirs[$a_set["Id"]];
			if ($tmp_dir != "" && is_dir($tmp_dir))
			{
				ilUtil::delDir($tmp_dir);
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
				
		if ($a_entity == "personal_data")
		{
			switch ($a_version)
			{
				case "4.3.0":
				case "4.5.0":
					$this->data = array();
					foreach ($a_ids as $id)
					{
						$this->data[] = array("Id" => $id);
					}
					break;
			}
		}
		
		if ($a_entity == "usr_profile")
		{
			switch ($a_version)
			{
				case "4.3.0":
				case "4.5.0":
					$this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, ".
						" title, birthday, gender, institution, department, street, city, zipcode, country, sel_country, ".
						" phone_office, phone_home, phone_mobile, fax, email, hobby, referral_comment, matriculation, ".
						" delicious, latitude, longitude".
						" FROM usr_data u ".
						"WHERE ".
						$ilDB->in("u.usr_id", $a_ids, false, "integer"));
					break;
			}
		}
		
		if ($a_entity == "usr_setting")
		{
			switch ($a_version)
			{
				case "4.3.0":
				case "4.5.0":
					// for all user ids get data from usr_pref and mail options, create records user_id/name/value
					$prefs = array("date_format", "day_end", "day_start", "hide_own_online_status", "hits_per_page", "language",
						"public_birthday", "puplic_city", "public_country", "public_delicious", "public_department", "public_email",
						"public_fax", "public_gender", "public_hobby", "public_im_aim", "public_im_icq", "public_im_jabber",
						"public_im_msn", "public_im_skype", "public_im_voip", "public_im_yahoo", "public_institution", "public_location",
						"public_matriculation", "public_phone_home", "public_phone_mobile", "public_phone_office", "public_profile",
						"public_sel_country", "public_street", "public_title", "public_upload", "public_zipcode",
						"screen_reader_optimization", "show_users_online",
						"store_last_visited", "time_format", "user_tz", "weekstart");
					$this->data = array();
					$set = $ilDB->query("SELECT * FROM usr_pref ".
						" WHERE ".$ilDB->in("keyword", $prefs, false, "text").
						" AND ".$ilDB->in("usr_id", $a_ids, false, "integer"));
					while ($rec  = $ilDB->fetchAssoc($set))
					{
						$this->data[] = array("UserId" => $rec["usr_id"], "Keyword" => $rec["keyword"], "Value" => $rec["value"]);
					}
					
					/*
					require_once 'Services/Mail/classes/class.ilMailOptions.php';
					$mailOptions = new ilMailOptions($ilUser->getId());

					/*$this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, ".
						" title, birthday, gender, institution, department, street, city, zipcode, country, sel_country, ".
						" phone_office, phone_home, phone_mobile, fax, email, hobby, referral_comment, matriculation, ".
						" delicious, latitude, longitude".
						" FROM usr_data u ".
						"WHERE ".
						$ilDB->in("u.usr_id", $a_ids, false, "integer"));*/
					break;
			}
		}

		if ($a_entity == "usr_multi")
		{			
			switch ($a_version)
			{
				case "4.5.0":					
					$this->data = array();
					$set = $ilDB->query("SELECT * FROM usr_data_multi".
						" WHERE ".$ilDB->in("usr_id", $a_ids, false, "integer"));
					while ($rec  = $ilDB->fetchAssoc($set))
					{
						$this->data[] = array("UserId" => $rec["usr_id"], "FieldId" => $rec["field_id"], "Value" => $rec["value"]);
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
		return false;
		switch ($a_entity)
		{
			case "personal_data":
				return array (
					"usr_profile" => array("ids" => $a_rec["Id"]),
					"usr_setting" => array("ids" => $a_rec["Id"]),
					"usr_multi" => array("ids" => $a_rec["Id"])
				);							
		}
		return false;
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		global $ilSetting, $ilUser;
//echo $a_entity;
//var_dump($a_rec);

		switch ($a_entity)
		{
			case "personal_data":
				// only users themselves import their profiles!
				// thus we can map the import id of the dataset to the current user
				$a_mapping->addMapping("Services/User", "usr", $a_rec["Id"], $ilUser->getId());
				break;
				
			case "usr_profile":
				$usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["Id"]);
				if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr")
				{
					if (!isset($this->users[$usr_id]))
					{
						$this->users[$usr_id] = new ilObjUser($usr_id);
					}
					$user = $this->users[$usr_id];
					include_once("./Services/User/classes/class.ilUserProfile.php");
					$prof = new ilUserProfile();
					$prof->skipField("username");
					$prof->skipField("password");
					$prof->skipField("roles");
					$prof->skipGroup("settings");
					$fields = $prof->getStandardFields();
					foreach ($fields as $k => $f)
					{
						$up_k = $this->convertToLeadingUpper($k);
						// only change fields, when it is possible in profile
						if (ilUserProfile::userSettingVisible($k) &&
							!$ilSetting->get("usr_settings_disable_".$k) &&
							$f["method"] != "" && isset($a_rec[$up_k]))
						{
							$set_method = "set".substr($f["method"], 3);
							$user->{$set_method}($a_rec[$up_k]);
//	echo "<br>-setting-".$set_method."-".$a_rec[$up_k]."-";
						}
					}
					$user->update();
					
					// personal picture
					$pic_dir = $this->getImportDirectory()."/".str_replace("..", "", $a_rec["Picture"]);
					if ($pic_dir != "" && is_dir($pic_dir))
					{
						$upload_file = $pic_dir."/upload_".$a_rec["Id"]."pic";
						if (is_file($upload_file))
						{
							ilObjUser::_uploadPersonalPicture($upload_file, $user->getId());
						}
					}
				}
				break;

			case "usr_setting":
				$usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["UserId"]);
				if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr")
				{
					if (!isset($this->users[$usr_id]))
					{
						$this->users[$usr_id] = new ilObjUser($usr_id);
					}
					$user = $this->users[$usr_id];
					$user->writePref($a_rec["Keyword"], $a_rec["Value"]);
				}
				break;
				
			case "usr_multi":
				$usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["UserId"]);
				if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr")
				{					
					$this->multi[$usr_id][$a_rec["FieldId"]][] = $a_rec["Value"];
				}
				break;
		}
	}
}
?>