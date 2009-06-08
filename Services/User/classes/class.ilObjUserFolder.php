<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjUserFolder
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilObject
*/

require_once "./classes/class.ilObject.php";

define('USER_FOLDER_ID',7);

class ilObjUserFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjUserFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "usrf";
		$this->ilObject($a_id,$a_call_by_reference);
	}


	/**
	* delete userfolder and all related data
	* DISABLED
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// DISABLED
		return false;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		// put here userfolder specific stuff

		// always call parent delete function at the end!!
		return true;
	}


	function getExportFilename($a_mode = "userfolder_export_excel_x86")
	{
		$filename = "";
		//$settings = $this->ilias->getAllSettings();
		//$this->inst_id = $settings["inst_id"];
		$inst_id = IL_INST_ID;

		$date = time();

		switch($a_mode)
		{
			case "userfolder_export_excel_x86":
				$filename = $date."__".$inst_id."__xls_usrf.xls";
				break;
			case "userfolder_export_csv":
				$filename = $date."__".$inst_id."__csv_usrf.csv";
				break;
			case "userfolder_export_xml":
				$filename = $date."__".$inst_id."__xml_usrf.xml";
				break;
		}
		return $filename;
	}


/**
* Get the location of the export directory for the user accounts
*
* Get the location of the export directory for the user accounts
*
* @access	public
*/
	function getExportDirectory()
	{
		$export_dir = ilUtil::getDataDir()."/usrf_data/export";

		return $export_dir;
	}

/**
* Get a list of the already exported files in the export directory
*
* Get a list of the already exported files in the export directory
*
* @return array A list of file names
* @access	public
*/
	function getExportFiles()
	{
		$dir = $this->getExportDirectory();

		// quit if export dir not available
		if (!@is_dir($dir) or
			!is_writeable($dir))
		{
			return array();
		}

		// open directory
		$dir = dir($dir);

		// initialize array
		$file = array();

		// get files and save the in the array
		while ($entry = $dir->read())
		{
			if ($entry != "." and
				$entry != ".." and
				preg_match("/^[0-9]{10}_{2}[0-9]+_{2}([a-z0-9]{3})_usrf\.[a-z]{1,3}\$/", $entry, $matches))
			{
				$filearray["filename"] = $entry;
				$filearray["filesize"] = filesize($this->getExportDirectory()."/".$entry);
				array_push($file, $filearray);
			}
		}

		// close import directory
		$dir->close();

		// sort files
		sort ($file);
		reset ($file);

		return $file;
	}

	function escapeXML($value)
	{
		$value = str_replace("&", "&amp;", $value);
		$value = str_replace("<", "&lt;", $value);
		$value = str_replace(">", "&gt;", $value);
		return $value;
	}

	function createXMLExport(&$settings, &$data, $filename)
	{
		include_once './Services/User/classes/class.ilUserDefinedData.php';
		include_once './Services/User/classes/class.ilObjUser.php';

		global $rbacreview;
		global $ilDB;
		global $log;

		$file = fopen($filename, "w");

		if (is_array($data))
		{
			  	include_once './Services/User/classes/class.ilUserXMLWriter.php';

			  	$xmlWriter = new ilUserXMLWriter();
			  	$xmlWriter->setObjects($data);
			  	$xmlWriter->setSettings($settings);
				$xmlWriter->setAttachRoles (true);

				if($xmlWriter->start())
				{
					fwrite($file, $xmlWriter->getXML());
				}
		}

/*
		fwrite($file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
		fwrite($file, "<!DOCTYPE Users SYSTEM \"".ILIAS_HTTP_PATH."/xml/ilias_user_3_8.dtd\">\n");
		fwrite($file, "<Users>");
		foreach ($data as $row)
		{
			//$log->write(date("[y-m-d H:i:s] ")."User data export: processing user " . $row["login"]);
			foreach ($row as $key => $value)
			{
				$row[$key] = $this->escapeXML($value);
			}
			$userline = "";
			// TODO: Define combobox for "Action" ???
			if (strlen($row["language"]) == 0) $row["language"] = "en";
			$userline .= "<User Id=\"il_".IL_INST_ID."_usr_".$row["usr_id"]."\" Language=\"".$row["language"]."\" Action=\"Insert\">";
			if (array_search("login", $settings) !== FALSE)
			{
				$userline .= "<Login>".$row["login"]."</Login>";
			}
			// Alternative way to get the roles of a user?
			$query = sprintf("SELECT object_data.title, rbac_fa.* FROM object_data, rbac_ua, rbac_fa WHERE rbac_ua.usr_id = %s AND rbac_ua.rol_id = rbac_fa.rol_id AND object_data.obj_id = rbac_fa.rol_id",
				$ilDB->quote($row["usr_id"])
			);
			$rbacresult = $ilDB->query($query);
			while ($rbacrow = $rbacresult->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$type = "";
				if ($rbacrow["assign"] == "y")
				{
					if ($rbacrow["parent"] == ROLE_FOLDER_ID)
					{
						$type = "Global";
					}
					else
					{
						$type = "Local";
					}
					if (strlen($type))
					{
						$userline .= "<Role Id=\"il_".IL_INST_ID."_role_".$rbacrow["rol_id"]."\" Type=\"".$type."\">".$rbacrow["title"]."</Role>";
					}
				}
			}
			//$log->write(date("[y-m-d H:i:s] ")."User data export: get all roles");
			/* the export of roles is to expensive. on a system with 6000 users the following
			   section needs 37 seconds

			$roles = $rbacreview->getRolesByFilter(1, $row["usr_id"]);
			$ass_roles = $rbacreview->assignedRoles($row["usr_id"]);
			foreach ($roles as $role)
			{
				if (array_search($role["obj_id"], $ass_roles) !== FALSE)
				{
					$type = "";
					switch ($role["role_type"])
					{
						case "global":
							$type = "Global";
							break;
						case "local":
							$type = "Local";
							break;
					}
					if (strlen($type))
					{
						$userline .= "<Role Id=\"".$role["obj_id"]."\" Type=\"".$type."\">".$role["title"]."</Role>";
					}
				}
			}
			*/
/*			//$log->write(date("[y-m-d H:i:s] ")."User data export: got all roles");
			$i2passwd = FALSE;
			if (array_search("i2passwd", $settings) !== FALSE)
			{
				if (strlen($row["i2passwd"])) $i2passwd = TRUE;
				if ($i2passwd) $userline .= "<Password Type=\"ILIAS2\">".$row["i2passwd"]."</Password>";
			}
			if ((!$i2passwd) && (array_search("passwd", $settings) !== FALSE))
			{
				if (strlen($row["passwd"])) $userline .= "<Password Type=\"ILIAS3\">".$row["passwd"]."</Password>";
			}
			if (array_search("firstname", $settings) !== FALSE)
			{
				if (strlen($row["firstname"])) $userline .= "<Firstname>".$row["firstname"]."</Firstname>";
			}
			if (array_search("lastname", $settings) !== FALSE)
			{
				if (strlen($row["lastname"])) $userline .= "<Lastname>".$row["lastname"]."</Lastname>";
			}
			if (array_search("title", $settings) !== FALSE)
			{
				if (strlen($row["title"])) $userline .= "<Title>".$row["title"]."</Title>";
			}
			if (array_search("upload", $settings) !== FALSE)
			{
				// personal picture
				$q = sprintf("SELECT value FROM usr_pref WHERE usr_id=%s AND keyword='profile_image'", $ilDB->quote($row["usr_id"] . ""));
				$r = $ilDB->query($q);
				if ($r->numRows() == 1)
				{
					$personal_picture_data = $r->fetchRow(DB_FETCHMODE_ASSOC);
					$personal_picture = $personal_picture_data["value"];
					$webspace_dir = ilUtil::getWebspaceDir();
					$image_file = $webspace_dir."/usr_images/".$personal_picture;
					if (@is_file($image_file))
					{
						$fh = fopen($image_file, "rb");
						if ($fh)
						{
							$image_data = fread($fh, filesize($image_file));
							fclose($fh);
							$base64 = base64_encode($image_data);
							$imagetype = "image/jpeg";
							if (preg_match("/.*\.(png|gif)$/", $personal_picture, $matches))
							{
								$imagetype = "image/".$matches[1];
							}
							$userline .= "<PersonalPicture imagetype=\"$imagetype\" encoding=\"Base64\">$base64</PersonalPicture>";
						}
					}
				}
			}
			if (array_search("gender", $settings) !== FALSE)
			{
				if (strlen($row["gender"])) $userline .= "<Gender>".$row["gender"]."</Gender>";
			}
			if (array_search("email", $settings) !== FALSE)
			{
				if (strlen($row["email"])) $userline .= "<Email>".$row["email"]."</Email>";
			}
			if (array_search("institution", $settings) !== FALSE)
			{
				if (strlen($row["institution"])) $userline .= "<Institution>".$row["institution"]."</Institution>";
			}
			if (array_search("street", $settings) !== FALSE)
			{
				if (strlen($row["street"])) $userline .= "<Street>".$row["street"]."</Street>";
			}
			if (array_search("city", $settings) !== FALSE)
			{
				if (strlen($row["city"])) $userline .= "<City>".$row["city"]."</City>";
			}
			if (array_search("zipcode", $settings) !== FALSE)
			{
				if (strlen($row["zipcode"])) $userline .= "<PostalCode>".$row["zipcode"]."</PostalCode>";
			}
			if (array_search("country", $settings) !== FALSE)
			{
				if (strlen($row["country"])) $userline .= "<Country>".$row["country"]."</Country>";
			}
			if (array_search("phone_office", $settings) !== FALSE)
			{
				if (strlen($row["phone_office"])) $userline .= "<PhoneOffice>".$row["phone_office"]."</PhoneOffice>";
			}
			if (array_search("phone_home", $settings) !== FALSE)
			{
				if (strlen($row["phone_home"])) $userline .= "<PhoneHome>".$row["phone_home"]."</PhoneHome>";
			}
			if (array_search("phone_mobile", $settings) !== FALSE)
			{
				if (strlen($row["phone_mobile"])) $userline .= "<PhoneMobile>".$row["phone_mobile"]."</PhoneMobile>";
			}
			if (array_search("fax", $settings) !== FALSE)
			{
				if (strlen($row["fax"])) $userline .= "<Fax>".$row["fax"]."</Fax>";
			}
			if (strlen($row["hobby"])) if (array_search("hobby", $settings) !== FALSE)
			{
				$userline .= "<Hobby>".$row["hobby"]."</Hobby>";
			}
			if (array_search("department", $settings) !== FALSE)
			{
				if (strlen($row["department"])) $userline .= "<Department>".$row["department"]."</Department>";
			}
			if (array_search("referral_comment", $settings) !== FALSE)
			{
				if (strlen($row["referral_comment"])) $userline .= "<Comment>".$row["referral_comment"]."</Comment>";
			}
			if (array_search("matriculation", $settings) !== FALSE)
			{
				if (strlen($row["matriculation"])) $userline .= "<Matriculation>".$row["matriculation"]."</Matriculation>";
			}
			if (array_search("active", $settings) !== FALSE)
			{
				if ($row["active"])
				{
					$userline .= "<Active>true</Active>";
				}
				else
				{
					$userline .= "<Active>false</Active>";
				}
			}
			if (array_search("client_ip", $settings) !== FALSE)
			{
				if (strlen($row["client_ip"])) $userline .= "<ClientIP>".$row["client_ip"]."</ClientIP>";
			}
			if (array_search("time_limit_owner", $settings) !== FALSE)
			{
				if (strlen($row["time_limit_owner"])) $userline .= "<TimeLimitOwner>".$row["time_limit_owner"]."</TimeLimitOwner>";
			}
			if (array_search("time_limit_unlimited", $settings) !== FALSE)
			{
				if (strlen($row["time_limit_unlimited"])) $userline .= "<TimeLimitUnlimited>".$row["time_limit_unlimited"]."</TimeLimitUnlimited>";
			}
			if (array_search("time_limit_from", $settings) !== FALSE)
			{
				if (strlen($row["time_limit_from"])) $userline .= "<TimeLimitFrom>".$row["time_limit_from"]."</TimeLimitFrom>";
			}
			if (array_search("time_limit_until", $settings) !== FALSE)
			{
				if (strlen($row["time_limit_until"])) $userline .= "<TimeLimitUntil>".$row["time_limit_until"]."</TimeLimitUntil>";
			}
			if (array_search("time_limit_message", $settings) !== FALSE)
			{
				if (strlen($row["time_limit_message"])) $userline .= "<TimeLimitMessage>".$row["time_limit_message"]."</TimeLimitMessage>";
			}
			if (array_search("approve_date", $settings) !== FALSE)
			{
				if (strlen($row["approve_date"])) $userline .= "<ApproveDate>".$row["approve_date"]."</ApproveDate>";
			}
			if (array_search("agree_date", $settings) !== FALSE)
			{
				if (strlen($row["agree_date"])) $userline .= "<AgreeDate>".$row["agree_date"]."</AgreeDate>";
			}
			if ((int) $row["ilinc_id"] !=0) {
					if (array_search("ilinc_id", $settings) !== FALSE)
					{
						if (strlen($row["ilinc_id"])) $userline .= "<iLincID>".$row["ilinc_id"]."</iLincID>";
					}
					if (array_search("ilinc_login", $settings) !== FALSE)
					{
						if (strlen($row["ilinc_login"])) $userline .= "<iLincLogin>".$row["ilinc_login"]."</iLincLogin>";
					}
					if (array_search("ilinc_passwd", $settings) !== FALSE)
					{
						if (strlen($row["ilinc_passwd"])) $userline .= "<iLincPasswd>".$row["ilinc_passwd"]."</iLincPasswd>";
					}
			}
			if (array_search("auth_mode", $settings) !== FALSE)
			{
				if (strlen($row["auth_mode"])) $userline .= "<AuthMode type=\"".$row["auth_mode"]."\"></AuthMode>";
			}
			if (array_search("skin_style", $settings) !== FALSE)
			{
				$userline .=
					"<Look Skin=\"" . ilObjUser::_lookupPref($row["usr_id"], "skin") . "\" " .
					"Style=\"" . ilObjUser::_lookupPref($row["usr_id"], "style") . "\"></Look>";
			}

			if (array_search("last_update", $settings) !== FALSE)
			{
				if (strlen($row["last_update"])) $userline .= "<LastUpdate>".$row["last_update"]."</LastUpdate>";
			}

			if (array_search("last_login", $settings) !== FALSE)
			{
				if (strlen($row["last_login"])) $userline .= "<LastLogin>".$row["last_login"]."</LastLogin>";
			}

			// Append User defined field data
			$udf_data = new ilUserDefinedData($row['usr_id']);
			$userline .= $udf_data->toXML();

			$userline .= "</User>";
			fwrite($file, $userline);
		}
		fwrite($file, "</Users>");
		fclose($file);*/
	}

	function createCSVExport(&$settings, &$data, $filename)
	{
		$headerrow = array();
		foreach ($settings as $value)
		{
			array_push($headerrow, $this->lng->txt($value));
		}
		$separator = ";";
		$file = fopen($filename, "w");
		$formattedrow =& ilUtil::processCSVRow($headerrow, TRUE, $separator);
		fwrite($file, join ($separator, $formattedrow) ."");
		foreach ($data as $row)
		{
			$csvrow = array();
			foreach ($settings as $header)
			{
				array_push($csvrow, $row[$header]);
			}
			$formattedrow =& ilUtil::processCSVRow($csvrow, TRUE, $separator);
			fwrite($file, join ($separator, $formattedrow) ."\n");
		}
		fclose($file);
	}

	function createExcelExport(&$settings, &$data, $filename, $a_mode)
	{
		include_once "./classes/class.ilExcelUtils.php";
		include_once "./classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($filename, FALSE);
		$workbook = $adapter->getWorkbook();
		// Creating a worksheet
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		$worksheet =& $workbook->addWorksheet();
		$row = 0;
		$col = 0;

		foreach ($settings as $value)
		{
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt($value), $a_mode), $format_title);
			$col++;
		}


		foreach ($data as $index => $rowdata)
		{
			$row++;
			$col = 0;
			foreach ($settings as $fieldname)
			{
//			foreach ($rowdata as $rowindex => $value)
//			{
				$value = $rowdata[$fieldname];
				switch ($fieldname)
				{
					case "language":
						$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("lang_".$value), $a_mode));
						break;
					case "time_limit_from":
					case "time_limit_until":
						$date = strftime("%Y-%m-%d %H:%M:%S", $value);
						if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $date, $matches))
						{
							$worksheet->write($row, $col, ilUtil::excelTime($matches[1],$matches[2],$matches[3],$matches[4],$matches[5],$matches[6]), $format_datetime);
						}
						break;
					case "last_login":
					case "last_update":
					case "create_date":
					case "approve_date":
					case "agree_date":
						if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $value, $matches))
						{
							$worksheet->write($row, $col, ilUtil::excelTime($matches[1],$matches[2],$matches[3],$matches[4],$matches[5],$matches[6]), $format_datetime);
						}
						break;
					default:
						$worksheet->write($row, $col, ilExcelUtils::_convert_text($value, $a_mode));
						break;
				}
				$col++;
			}
		}
		$workbook->close();
	}

	/**
	 * getExport Settings
	 *
	 * @return array of exportable fields
	 */
	static function getExportSettings()
	{
		global $ilDB;

		$db_settings = array();
		$profile_fields =& ilObjUserFolder::getProfileFields();
		$profile_fields[] = "preferences";

		$query = "SELECT * FROM `settings` WHERE keyword LIKE '%usr_settings_export_%' AND value = '1'";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (preg_match("/usr_settings_export_(.*)/", $row["keyword"], $setting))
			{
				array_push($db_settings, $setting[1]);
			}
		}
		$export_settings = array();
		foreach ($profile_fields as $key => $value)
		{
			if (in_array($value, $db_settings))
			{
				if (strcmp($value, "password") == 0)
				{
					array_push($export_settings, "passwd");
				}
				else
				{
					array_push($export_settings, $value);
				}
			}
		}
		array_push($export_settings, "login");
		array_push($export_settings, "last_login");
		array_push($export_settings, "last_update");
		array_push($export_settings, "create_date");
		array_push($export_settings, "i2passwd");
		array_push($export_settings, "time_limit_owner");
		array_push($export_settings, "time_limit_unlimited");
		array_push($export_settings, "time_limit_from");
		array_push($export_settings, "time_limit_until");
		array_push($export_settings, "time_limit_message");
		array_push($export_settings, "active");
		array_push($export_settings, "approve_date");
		array_push($export_settings, "agree_date");
		array_push($export_settings, "ilinc_id");
		array_push($export_settings, "ilinc_login");
		array_push($export_settings, "ilinc_passwd");
		array_push($export_settings, "client_ip");
		array_push($export_settings, "auth_mode");
		array_push($export_settings, "ext_account");
		array_push($export_settings, "feedhash");
		
		return $export_settings;
	}

	/**
	* build xml export file
	*/
	function buildExportFile($a_mode = "userfolder_export_excel_x86", $user_data_filter = FALSE)
	{
		global $ilBench;
		global $log;
		global $ilDB;
		global $ilias;

		//get Log File
		$expDir = $this->getExportDirectory();
		//$expLog = &$log;
		//$expLog->delete();
		//$expLog->setLogFormat("");
		//$expLog->write(date("[y-m-d H:i:s] ")."Start export of user data");

		// create export directory if needed
		$this->createExportDirectory();

		//get data
		//$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");
		$settings =& $this->getExportSettings();
		$data = array();
		$query = "SELECT usr_data.*, usr_pref.value AS language FROM usr_data, usr_pref WHERE usr_pref.usr_id = usr_data.usr_id ".
			"AND usr_pref.keyword = ".$ilDB->quote("language", "text")." ORDER BY usr_data.lastname, usr_data.firstname";
		$result = $ilDB->query($query);
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (is_array($user_data_filter))
			{
				if (in_array($row["usr_id"], $user_data_filter)) array_push($data, $row);
			}
			else
			{
				array_push($data, $row);
			}
		}
		//$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");

		$fullname = $expDir."/".$this->getExportFilename($a_mode);
		switch ($a_mode)
		{
			case "userfolder_export_excel_x86":
				$this->createExcelExport($settings, $data, $fullname, "latin1");
				break;
			case "userfolder_export_csv":
				$this->createCSVExport($settings, $data, $fullname);
				break;
			case "userfolder_export_xml":
				$this->createXMLExport($settings, $data, $fullname);
				break;
		}
		//$expLog->write(date("[y-m-d H:i:s] ")."Finished export of user data");

		return $fullname;
	}


	/**
	* creates data directory for export files
	* (data_dir/usrf_data/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		if (!@is_dir($this->getExportDirectory()))
		{
			$usrf_data_dir = ilUtil::getDataDir()."/usrf_data";
			ilUtil::makeDir($usrf_data_dir);
			if(!is_writable($usrf_data_dir))
			{
				$this->ilias->raiseError("Userfolder data directory (".$usrf_data_dir
					.") not writeable.",$this->ilias->error_obj->MESSAGE);
			}

			// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
			$export_dir = $usrf_data_dir."/export";
			ilUtil::makeDir($export_dir);
			if(!@is_dir($export_dir))
			{
				$this->ilias->raiseError("Creation of Userfolder Export Directory failed.",$this->ilias->error_obj->MESSAGE);
			}
		}
	}

	/**
	 * get Profile fields
	 *
	 * @return array of fieldnames
	 */
	static function &getProfileFields()
	{
		$profile_fields = array(
			"gender",
			"firstname",
			"lastname",
			"title",			
			"password",
			"institution",
			"department",
			"street",
			"zipcode",
			"city",
			"country",
			"phone_office",
			"phone_home",
			"phone_mobile",
			"fax",
			"email",
			"hobby",
			"referral_comment",
			"matriculation",
			"upload",
			"language",
			"skin_style",
			"hits_per_page",
			"show_users_online",
			"instant_messengers",
			"hide_own_online_status" 
		);
		return $profile_fields;
	}

	function _writeNewAccountMail($a_lang, $a_subject, $a_sal_g, $a_sal_f, $a_sal_m, $a_body)
	{
		global $ilDB;
		
		if(self::_lookupNewAccountMail($a_lang))
		{
			$values = array(
				'subject'		=> array('text',$a_subject),
				'body'			=> array('clob',$a_body),
				'sal_g'			=> array('text',$a_sal_g),
				'sal_f'			=> array('text',$a_sal_f),
				'sal_m'			=> array('text',$a_sal_m)
				);
			$ilDB->update('usr_new_account_mail',$values,array('lang' => array('text',$a_lang)));
		}
		else
		{
			$values = array(
				'subject'		=> array('text',$a_subject),
				'body'			=> array('clob',$a_body),
				'sal_g'			=> array('text',$a_sal_g),
				'sal_f'			=> array('text',$a_sal_f),
				'sal_m'			=> array('text',$a_sal_m),
				'lang'			=> array('text',$a_lang)
				);
			$ilDB->insert('usr_new_account_mail',$values);
		}
	}

	function _lookupNewAccountMail($a_lang)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM usr_new_account_mail ".
			" WHERE lang = ".$ilDB->quote($a_lang,'text'));

		if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $rec;
		}
		return array();
	}
	
	/**
	 * Update user folder assignment
	 * Typically called after deleting a category with local user accounts.
	 * These users will be assigned to the global user folder. 
	 *
	 * @access public
	 * @static
	 *
	 * @param int old_id
	 * @param int new id
	 */
	public static function _updateUserFolderAssignment($a_old_id,$a_new_id)
	{
		global $ilDB;
		
		$query = "UPDATE usr_data SET time_limit_owner = ".$ilDB->quote($a_new_id, "integer")." ".
			"WHERE time_limit_owner = ".$ilDB->quote($a_old_id, "integer")." ";
		$ilDB->manipulate($query);
		
		return true;
	}
	

} // END class.ilObjUserFolder
?>
