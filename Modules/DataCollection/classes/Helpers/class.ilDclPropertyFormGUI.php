<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilDclPropertyFormGUI
 * @author       Michael Herren <mh@studer-raimann.ch>
 * @version      1.0.0
 * @ilCtrl_Calls ilDclPropertyFormGUI: ilFormPropertyDispatchGUI
 */
class ilDclPropertyFormGUI extends ilPropertyFormGUI
{
    /**
     * Expose method for save confirmation
     */
    public function keepTempFileUpload(
        string $a_hash,
        string $a_field,
        string $a_tmp_name,
        string $a_name,
        string $a_type,
        ?string $a_index = null,
        ?string $a_sub_index = null
    ): void {
        $this->keepFileUpload($a_hash, $a_field, $a_tmp_name, $a_name, $a_type, $a_index, $a_sub_index);
    }

    public static function getTempFilename(
        string $a_hash,
        string $a_field,
        string $a_name,
        string $a_type,
        ?string $a_index = null,
        ?string $a_sub_index = null
    ): string {
        $a_name = ilFileUtils::getAsciiFileName($a_name);

        $tmp_file_name = implode(
            "~~",
            array(
                session_id(),
                $a_hash,
                $a_field,
                $a_index,
                $a_sub_index,
                str_replace("/", "~~", $a_type),
                str_replace("~~", "_", $a_name),
            )
        );

        // make sure temp directory exists
        $temp_path = ilFileUtils::getDataDir() . "/temp/";

        return $temp_path . $tmp_file_name;
    }

    /**
     * @throws ilDclException
     */
    public static function rebuildTempFileByHash(string $hash): void
    {
        $temp_path = ilFileUtils::getDataDir() . "/temp";
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
     */
    public function cleanupTempFiles(string $hash): void
    {
        $files = glob(ilFileUtils::getDataDir() . "/temp/" . session_id() . "~~" . $hash . "~~*");

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
