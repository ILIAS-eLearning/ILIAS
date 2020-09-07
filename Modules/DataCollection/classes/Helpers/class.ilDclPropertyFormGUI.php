<?php

/**
 * Class ilDclPropertyFormGUI
 *
 * @author       Michael Herren <mh@studer-raimann.ch>
 * @version      1.0.0
 * @ilCtrl_Calls ilDclPropertyFormGUI: ilFormPropertyDispatchGUI
 */
class ilDclPropertyFormGUI extends ilPropertyFormGUI
{

    /**
     * Expose method for save confirmation
     *
     * @param      $a_hash
     * @param      $a_field
     * @param      $a_tmp_name
     * @param      $a_name
     * @param      $a_type
     * @param null $a_index
     * @param null $a_sub_index
     */
    public function keepTempFileUpload($a_hash, $a_field, $a_tmp_name, $a_name, $a_type, $a_index = null, $a_sub_index = null)
    {
        $this->keepFileUpload($a_hash, $a_field, $a_tmp_name, $a_name, $a_type, $a_index, $a_sub_index);
    }


    /**
     * return temp-filename
     *
     * @param      $a_hash
     * @param      $a_field
     * @param      $a_name
     * @param      $a_type
     * @param null $a_index
     * @param null $a_sub_index
     *
     * @return string
     */
    public static function getTempFilename($a_hash, $a_field, $a_name, $a_type, $a_index = null, $a_sub_index = null)
    {
        $a_name = ilUtil::getAsciiFileName($a_name);

        $tmp_file_name = implode(
            "~~",
            array(session_id(),
                        $a_hash,
                        $a_field,
                        $a_index,
                        $a_sub_index,
                        str_replace("/", "~~", $a_type),
                        str_replace("~~", "_", $a_name))
        );

        // make sure temp directory exists
        $temp_path = ilUtil::getDataDir() . "/temp/";

        return $temp_path . $tmp_file_name;
    }


    /**
     * Return temp files
     *
     * @param $hash
     *
     * @throws ilDclException
     */
    public static function rebuildTempFileByHash($hash)
    {
        $temp_path = ilUtil::getDataDir() . "/temp";
        if (is_dir($temp_path)) {
            $temp_files = glob($temp_path . "/" . session_id() . "~~" . $hash . "~~*");
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
                            $_FILES[$field]["tmp_name"][$idx][$idx2] = $full_file;
                            $_FILES[$field]["name"][$idx][$idx2] = $name;
                            $_FILES[$field]["type"][$idx][$idx2] = $type;
                            $_FILES[$field]["error"][$idx][$idx2] = 0;
                            $_FILES[$field]["size"][$idx][$idx2] = filesize($full_file);
                        }
                    } else {
                        if ($idx != "") {
                            if (!$_FILES[$field]["tmp_name"][$idx]) {
                                $_FILES[$field]["tmp_name"][$idx] = $full_file;
                                $_FILES[$field]["name"][$idx] = $name;
                                $_FILES[$field]["type"][$idx] = $type;
                                $_FILES[$field]["error"][$idx] = 0;
                                $_FILES[$field]["size"][$idx] = filesize($full_file);
                            }
                        } else {
                            if (!$_FILES[$field]["tmp_name"]) {
                                $_FILES[$field]["tmp_name"] = $full_file;
                                $_FILES[$field]["name"] = $name;
                                $_FILES[$field]["type"] = $type;
                                $_FILES[$field]["error"] = 0;
                                $_FILES[$field]["size"] = filesize($full_file);
                            }
                        }
                    }
                }
            }
        } else {
            throw new ilDclException('temp dir path "' . $temp_path . '" is not a directory');
        }
    }


    /**
     * Cleanup temp-files
     *
     * @param $hash
     */
    public function cleanupTempFiles($hash)
    {
        $files = glob(ilUtil::getDataDir() . "/temp/" . session_id() . "~~" . $hash . "~~*");

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
