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
 *********************************************************************/

declare(strict_types=1);

/**
* Class ilObjLanguageFolder
* contains all function to manage language support for ILIAS3
* install, uninstall, checkfiles ....
*
* @author   Sascha Hofmann <shofmann@databay.de>
* @version  $Id$
*
* @extends  ilObject
*/
class ilObjLanguageFolder extends ilObject
{
    /**
    * indicator for the system language
    * this language must not be deleted
    */
    public string $lang_default;

    /**
    * language that is in use
    * by current user
    * this language must not be deleted
    */
    public string $lang_user;

    /**
    * path to language files
    * relative path is taken from ini file
    * and added to absolute path of ilias
    */
    public string $lang_path;

    /**
    * separator value between module,identivier & value
    */
    public string $separator;

    /**
    * contians all informations about languages
    */
    public array $languages;

    /**
    * Constructor
    * $a_id                     reference_id or object_id
    * $a_call_by_reference      treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id, bool $a_call_by_reference = true)
    {
        $this->type = "lngf";
        parent::__construct($a_id, $a_call_by_reference);

        $this->lang_path = $this->lng->lang_path;
        $this->lang_default = $this->lng->lang_default;
        $this->lang_user = $this->lng->lang_user;
        $this->separator = $this->lng->separator;
    }

    /**
    * gather all information about available languages
    *
    * This function builds an array with the following structure:
    * $languages[lang_key][long][installed][update][info]
    *
    * lang_key:     string      international language key (2 digits, i.e. de,en,dk...)
    * long:         string      full language name in the chosen user language
    * installed:    boolean     is the language installed (true) or not (false)?
    * update:       int         contains the timestamp of last db-modification
    * info:         string      optional information. valid is: 'notfound','new'
    *
    * Returns   $languages    status information about available languages
    */
    public function getLanguages(): array
    {
        $this->lng->loadLanguageModule("meta");

        // set path to directory where lang-files reside
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        $languages = [];

        // get available lang-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang$)~", $entry))) {
                $lang_key = substr($entry, 6, 2);
                $languages[$lang_key] = ""; // long names will be set in class Out
            }
        }

        $language = array();
        $tmp_array = array_keys($languages);
        $lang_keys = array();

        // now get languages from database
        if ($lang_db = ilObject::_getObjectsByType("lng")) {
            foreach ($lang_db as $lang) {
                // set values
                $lang_key = $lang["title"];
                $languages[$lang_key] = $lang;
                $lang_keys[] = $lang_key;

                $languages[$lang_key]["info"] = "";
                $languages[$lang_key]["status"] = "";

                // determine default language and language of current user
                if ($lang_key === $this->lang_user) {
                    $languages[$lang_key]["status"] = "in_use";
                }

                if ($lang_key === $this->lang_default) {
                    $languages[$lang_key]["status"] = "system_language";
                }

                // check if files are missing
                if (count($tmp_array) > 0 && !in_array($lang_key, $tmp_array, true)) {
                    $languages[$lang_key]["info"] = "file_not_found";
                }
            }
        }

        // compute new languages
        foreach ($languages as $lang_key => $lang_data) {
            if (!in_array($lang_key, $lang_keys, true)) {
                $languages[$lang_key] = array();
                $languages[$lang_key]["info"] = "new_language";
                //$languages[$lang_key]["desc"] = "not_installed";
            }
        }

        chdir($tmpPath);

        // Insert languages with files new found into table language
        $languages = $this->addNewLanguages($languages);

        // Remove from array & db languages which are not installed and no lang-files
        $languages = $this->removeLanguages($languages);

        // setting language's full names
        foreach ($languages as $lang_key => $lang_data) {
            $languages[$lang_key]["name"] = $this->lng->txt("meta_l_" . $lang_key);
        }

        $this->languages = $languages;

        return $this->languages;
    }

    /**
    * add new languages
    *
    * This functions checks in $languages for languages with the attribute 'new'
    * and insert these languages in db-table 'languages'
    */
    public function addNewLanguages(array $a_languages): array
    {
        if (count($a_languages) > 0) {
            foreach ($a_languages as $lang_key => $lang_data) {
                if (isset($lang_data["info"]) && $lang_data["info"] === "new_language") {
                    include_once "./Services/Language/classes/class.ilObjLanguage.php";
                    $lngObj = new ilObjLanguage();
                    $lngObj->setTitle($lang_key);
                    $lngObj->setDescription("not_installed");
                    $lngObj->create();

                    // must get OOP through the whole class some time
                    // (no arrays with db fields! this class doesn't know anything about table object!)
                    $a_languages[$lang_key] = array("obj_id" => $lngObj->getId(),
                                                    "type" => $lngObj->getType(),
                                                    "description" => $lngObj->getDescription(),
                                                    "desc" => $lngObj->getDescription(),
                                                    "owner" => $lngObj->getOwner(),
                                                    "create_date" => $lngObj->getCreateDate(),
                                                    "last_update" => $lngObj->getLastUpdateDate());

                    $a_languages[$lang_key]["info"] = "new_language";
                    unset($lngObj);      // better: the objects should be resident in an member array of this class
                }
            }
        }

        return $a_languages;
    }

    /**
    * remove languages which are not installed AND has no lang-file
    *
    * This function removes only the entry in db-table 'languages' and
    * in the array $languages. Does not uninstall a language (see: function flushLanguage())
    *
    * Return   $languages    updated status information about available languages
    */
    public function removeLanguages(array $a_languages): array
    {
        foreach ($a_languages as $lang_key => $lang_data) {
            if ($lang_data["desc"] === "not_installed" && $lang_data["info"] === "file_not_found") {
                // update languages array
                unset($a_languages[$lang_key]);

                // update object_data table
                $query = "DELETE FROM object_data " .
                         "WHERE type = " . $this->db->quote("lng", "text") . " " .
                         "AND title = " . $this->db->quote($lang_key, "text");
                $this->db->manipulate($query);
            }
        }

        return $a_languages;
    }

    /**
    * validate the logical structure of a lang-file
    *
    * This function is similar to function checkLanguage() (see below) but checks for all
    * lang-files and outputs more helpful information.
    *
    * Return system message
    */
    public function checkAllLanguages(): string
    {
        // set path to directory where lang-files reside
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        // for giving a message when no lang-file was found
        $found = false;

        $output = '';
        // get available lang-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang$)~", $entry))) {
                // textmeldung, wenn langfile gefunden wurde
                $output .= "<br/><br/>" . $this->lng->txt("langfile_found") . ": " . $entry;
                $content = file($entry);
                $lines_full = count($content);
                $found = true;
                $error_param = false;
                $error_double = false;
                $double_checker = [];

                if ($content = ilObjLanguage::cut_header($content)) {
                    $lines_cut = count($content);
                    foreach ($content as $key => $val) {
                        $separated = explode($this->separator, trim($val));
                        $num = count($separated);
                        $line = $key + $lines_full - $lines_cut + 1;

                        if ($num !== 3) {
                            $error_param = true;

                            $output .= "<br/><b/>" . $this->lng->txt("err_in_line") . " " . $line . " !</b>&nbsp;&nbsp;";

                            switch ($num) {
                                case 1:
                                    if (empty($separated[0])) {
                                        $output .= "<br/>" . $this->lng->txt("err_no_param") . " " . $this->lng->txt("check_langfile");
                                    } else {
                                        $output .= $this->lng->txt("module") . ": " . $separated[0];
                                        $output .= "<br/>" . $this->lng->txt("err_1_param") . " " . $this->lng->txt("check_langfile");
                                    }
                                    break;

                                case 2:
                                    $output .= $this->lng->txt("module") . ": " . $separated[0];
                                    $output .= ", " . $this->lng->txt("identifier") . ": " . $separated[1];
                                    $output .= "<br/>" . $this->lng->txt("err_2_param") . " " . $this->lng->txt("check_langfile");
                                    break;

                                default:
                                    $output .= $this->lng->txt("module") . ": " . $separated[0];
                                    $output .= ", " . $this->lng->txt("identifier") . ": " . $separated[1];
                                    $output .= ", " . $this->lng->txt("value") . ": " . $separated[2];
                                    $output .= "<br/>" . $this->lng->txt("err_over_3_param") . " " . $this->lng->txt("check_langfile");
                                    break;
                            }
                            continue;
                        }
                        if ($double_checker[strtolower($separated[0])][strtolower($separated[1])] ?? false) {
                            $error_double = true;
                            
                            $output .= "<br/><b/>" . $this->lng->txt("err_in_line") . " " . $double_checker[strtolower($separated[0])][strtolower($separated[1])] . " " . $this->lng->txt("and") . " " . $line . " !</b>&nbsp;&nbsp;";
                            $output .= $this->lng->txt("module") . ": " . $separated[0];
                            $output .= ", " . $this->lng->txt("identifier") . ": " . $separated[1];
                            $output .= ", " . $this->lng->txt("value") . ": " . $separated[2];
                        }
                        $double_checker[strtolower($separated[0])][strtolower($separated[1])] = $line;
                    }

                    if ($error_param || $error_double) {
                        $reason = "";
                        if ($error_param) {
                            $reason .= " " . $this->lng->txt("err_count_param");
                        }
                        if ($error_double) {
                            $reason .= " " . $this->lng->txt("err_double_entries");
                        }
                        $output .= "<br/>" . $this->lng->txt("file_not_valid") . $reason;
                    } else {
                        $output .= "<br/>" . $this->lng->txt("file_valid");
                    }
                } else {
                    $output .= "<br/>" . $this->lng->txt("file_not_valid") . " " . $this->lng->txt("err_wrong_header");
                }
            }
        }

        $d->close();

        if (!$found) {
            $output .= "<br/>" . $this->lng->txt("err_no_langfile_found");
        }

        chdir($tmpPath);
        return $output;
    }
} // END class.LanguageFolderObject
