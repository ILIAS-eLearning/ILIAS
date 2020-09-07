<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjLanguageFolder
* contains all function to manage language support for ILIAS3
* install, uninstall, checkfiles ....
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @extends	ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjLanguageFolder extends ilObject
{
    /**
    * indicator for the system language
    * this language must not be deleted
    * @var		string
    * @access	private
    */
    public $lang_default;

    /**
    * language that is in use
    * by current user
    * this language must not be deleted
    *
    * @var		string
    * @access	private
    */
    public $lang_user;

    /**
    * path to language files
    * relative path is taken from ini file
    * and added to absolute path of ilias
    *
    * @var		string
    * @access	private
    */
    public $lang_path;

    /**
    * separator value between module,identivier & value
    * @var		string
    * @access	private
    */
    public $separator;

    /**
    * contians all informations about languages
    *
    * @var		array
    * @access	public
    */
    public $languages;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        global $DIC;
        $lng = $DIC->language();

        $this->type = "lngf";
        parent::__construct($a_id, $a_call_by_reference);

        $this->lang_path = $lng->lang_path;
        $this->lang_default = $lng->lang_default;
        $this->lang_user = $lng->lang_user;
        $this->separator = $lng->separator;
    }

    /**
    * gather all information about available languages
    *
    * This function builds an array with the following structure:
    * $languages[lang_key][long][installed][update][info]
    *
    * lang_key:		string		international language key (2 digits, i.e. de,en,dk...)
    * long:			string		full language name in the chosen user language
    * installed:	boolean		is the language installed (true) or not (false)?
    * update:		int			contains the timestamp of last db-modification
    * info:			string		optional information. valid is: 'notfound','new'
    *
    * @return	array	$languages	status information about available languages
    */
    public function getLanguages()
    {
        global $DIC;
        $lng = $DIC->language();
        
        $lng->loadLanguageModule("meta");

        // set path to directory where lang-files reside
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        // get available lang-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang$)~", $entry))) {
                $lang_key = substr($entry, 6, 2);
                $languages[$lang_key] = ""; // long names will be set in class Out
            }
        }

        // ensure that arrays are initiated when no lang file was found
        if (!array($languages)) {
            $language = array();
            $tmp_array = array();
        }

        $tmp_array = array_keys($languages);
        $lang_keys = array();

        // now get languages from database
        if ($lang_db = ilObject::_getObjectsByType("lng")) {
            foreach ($lang_db as $lang) {
                // set values
                $lang_key = $lang["title"];
                $languages[$lang_key] = $lang;
                $lang_keys[] = $lang_key;

                // determine default language and language of current user
                if ($lang_key == $this->lang_user) {
                    $languages[$lang_key]["status"] = "in_use";
                }

                if ($lang_key == $this->lang_default) {
                    $languages[$lang_key]["status"] = "system_language";
                }

                // check if files are missing
                if ((count($tmp_array) > 0) && (!in_array($lang_key, $tmp_array))) {
                    $languages[$lang_key]["info"] = "file_not_found";
                }
            }
        }

        // compute new languages
        foreach ($languages as $lang_key => $lang_data) {
            if (!in_array($lang_key, $lang_keys)) {
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
            $languages[$lang_key]["name"] = $lng->txt("meta_l_" . $lang_key);
        }

        $this->languages = $languages;

        return $this->languages;
    }

    /**
    * add new languages
    *
    * This functions checks in $languages for languages with the attribute 'new'
    * and insert these languages in db-table 'languages'
    *
    * @param	array	$languages		expect $languages
    *
    * @return	boolean					true: language array is not empty, otherwise false
    */
    public function addNewLanguages($a_languages)
    {
        if (count($a_languages) > 0) {
            foreach ($a_languages as $lang_key => $lang_data) {
                if ($lang_data["info"] == "new_language") {
                    include_once("./Services/Language/classes/class.ilObjLanguage.php");
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
                    unset($lngObj);			// better: the objects should be resident in an member array of this class
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
    * @access	public
    * @param	array	$languages
    * @return	array	$languages	updated status information about available languages
    */
    public function removeLanguages($a_languages)
    {
        global $DIC;
        $ilDB = $DIC->database();

        foreach ($a_languages as $lang_key => $lang_data) {
            if ($lang_data["desc"] == "not_installed" && $lang_data["info"] == "file_not_found") {
                // update languages array
                unset($a_languages[$lang_key]);

                // update object_data table
                $query = "DELETE FROM object_data " .
                         "WHERE type = " . $ilDB->quote("lng", "text") . " " .
                         "AND title = " . $ilDB->quote($lang_key, "text");
                $ilDB->manipulate($query);
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
    * @return	string	system message
    */
    public function checkAllLanguages()
    {
        global $DIC;
        // TODO: lng object should not be used in this class
        $lng = $DIC->language();

        // set path to directory where lang-files reside
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        // for giving a message when no lang-file was found
        $found = false;

        // get available lang-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang$)~", $entry))) {
                // textmeldung, wenn langfile gefunden wurde
                $output .= "<br/><br/>" . $lng->txt("langfile_found") . ": " . $entry;
                $content = file($entry);

                $found = true;
                $error = false;

                if ($content = ilObjLanguage::cut_header($content)) {
                    foreach ($content as $key => $val) {
                        $separated = explode($this->separator, trim($val));
                        $num = count($separated);

                        if ($num != 3) {
                            $error = true;
                            $line = $key + 37;

                            $output .= "<br/><b/>" . $lng->txt("err_in_line") . " " . $line . " !</b>&nbsp;&nbsp;";
                            $output .= $lng->txt("module") . ": " . $separated[0];
                            $output .= ", " . $lng->txt("identifier") . ": " . $separated[1];
                            $output .= ", " . $lng->txt("value") . ": " . $separated[2];

                            switch ($num) {
                                case 1:
                                    if (empty($separated[0])) {
                                        $output .= "<br/>" . $lng->txt("err_no_param") . " " . $lng->txt("check_langfile");
                                    } else {
                                        $output .= "<br/>" . $lng->txt("err_1_param") . " " . $lng->txt("check_langfile");
                                    }
                                break;

                                case 2:
                                    $output .= "<br/>" . $lng->txt("err_2_param") . " " . $lng->txt("check_langfile");
                                break;

                                default:
                                    $output .= "<br/>" . $lng->txt("err_over_3_param") . " " . $lng->txt("check_langfile");
                                break;
                            }
                        }
                    }

                    if ($error) {
                        $output .= "<br/>" . $lng->txt("file_not_valid") . " " . $lng->txt("err_count_param");
                    } else {
                        $output .= "<br/>" . $lng->txt("file_valid");
                    }
                } else {
                    $output .= "<br/>" . $lng->txt("file_not_valid") . " " . $lng->txt("err_wrong_header");
                }
            }
        }

        $d->close();

        if (!$found) {
            $output .= "<br/>" . $lng->txt("err_no_langfile_found");
        }

        chdir($tmpPath);
        return $output;
    }
} // END class.LanguageFolderObject
