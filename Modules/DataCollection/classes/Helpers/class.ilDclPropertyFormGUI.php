<?php
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Class ilDclPropertyFormGUI
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclPropertyFormGUI extends ilPropertyFormGUI {

	public function keepTempFileUpload($a_hash, $a_field, $a_tmp_name, $a_name, $a_type, $a_index = null, $a_sub_index = null)
	{
		parent::keepFileUpload($a_hash, $a_field, $a_tmp_name, $a_name, $a_type, $a_index, $a_sub_index);
	}

	public static function getTempFilename($a_hash, $a_field, $a_name, $a_type, $a_index = null, $a_sub_index = null) {
		global $ilUser;

		$user_id = $ilUser->getId();
		if(!$user_id || $user_id == ANONYMOUS_USER_ID)
		{
			return;
		}

		$a_name = ilUtil::getAsciiFileName($a_name);

		$tmp_file_name = implode("~~", array($user_id,
			$a_hash,
			$a_field,
			$a_index,
			$a_sub_index,
			str_replace("/", "~~", $a_type),
			str_replace("~~", "_", $a_name)));

		// make sure temp directory exists
		$temp_path = ilUtil::getDataDir() . "/temp/";
		return $temp_path.$tmp_file_name;
	}

	public static function getTempFileByHash($hash, $user_id) {
		$temp_path = ilUtil::getDataDir() . "/temp";
		if(is_dir($temp_path) && $user_id && $user_id != ANONYMOUS_USER_ID) {
			$reload = array();

			$temp_files = glob($temp_path . "/" . $user_id . "~~" . $hash . "~~*");
			if (is_array($temp_files)) {
				foreach ($temp_files as $full_file) {
					$file = explode("~~", basename($full_file));
					$field = $file[2];
					$idx = $file[3];
					$idx2 = $file[4];
					$type = $file[5] . "/" . $file[6];
					$name = $file[7];

					if ($idx2 != "") {
						if (!$_FILES[$field]["tmp_name"][$idx][$idx2]) {
							$reload[$field]["tmp_name"][$idx][$idx2] = $full_file;
							$reload[$field]["name"][$idx][$idx2] = $name;
							$reload[$field]["type"][$idx][$idx2] = $type;
							$reload[$field]["error"][$idx][$idx2] = 0;
							$reload[$field]["size"][$idx][$idx2] = filesize($full_file);
						}
					} else {
						if ($idx != "") {
							if (!$_FILES[$field]["tmp_name"][$idx]) {
								$reload[$field]["tmp_name"][$idx] = $full_file;
								$reload[$field]["name"][$idx] = $name;
								$reload[$field]["type"][$idx] = $type;
								$reload[$field]["error"][$idx] = 0;
								$reload[$field]["size"][$idx] = filesize($full_file);
							}
						} else {
							if (!$_FILES[$field]["tmp_name"]) {
								$reload[$field]["tmp_name"] = $full_file;
								$reload[$field]["name"] = $name;
								$reload[$field]["type"] = $type;
								$reload[$field]["error"] = 0;
								$reload[$field]["size"] = filesize($full_file);
							}
						}
					}
				}
			}
		}
		return $reload;
	}

	public function getReloadedFiles() {
		return $this->reloaded_files;
	}
}