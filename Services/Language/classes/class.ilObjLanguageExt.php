<?php
/* Copyright (c) 1998-20014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Language/classes/class.ilObjLanguage.php";

/**
* Class ilObjLanguageExt
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilObjLanguageExt.php $
*
* @ingroup ServicesLanguage
*/
class ilObjLanguageExt extends ilObjLanguage
{
    
    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_call_by_reference = false)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    /**
    * Read and get the global language file as an object
    * @return   object  	global language file
    */
    public function getGlobalLanguageFile()
    {
        require_once "./Services/Language/classes/class.ilLanguageFile.php";
        return ilLanguageFile::_getGlobalLanguageFile($this->key);
    }

    /**
    * Set the local status of the language
    *
    * @param   boolean       local status (true/false)
    */
    public function setLocal($a_local = true)
    {
        if ($this->isInstalled()) {
            if ($a_local == true) {
                $this->setDescription("installed_local");
            } else {
                $this->setDescription("installed");
            }
            $this->update();
        }
    }

    
    /**
    * Get the full language description
    *
    * @return   string       description
    */
    public function getLongDescription()
    {
        return $this->lng->txt($this->desc);
    }


    /**
     * Get the path for language data written by ILIAS
     * @return string
     */
    public function getDataPath()
    {
        if (!is_dir(CLIENT_DATA_DIR . '/lang_data')) {
            ilUtil::makeDir(CLIENT_DATA_DIR . '/lang_data');
        }
        return CLIENT_DATA_DIR . '/lang_data';
    }
    
    /**
    * Get the language files path
    *
    * @return   string       path of language files folder
    */
    public function getLangPath()
    {
        return $this->lang_path;
    }

    /**
    * Get the customized language files path
    *
    * @return   string       path of customized language files folder
    */
    public function getCustLangPath()
    {
        return $this->cust_lang_path;
    }

    /**
    * Get all remarks from the database
    *
    * @return   array       module.separator.topic => remark
    */
    public function getAllRemarks()
    {
        return self::_getRemarks($this->key);
    }

    /**
    * Get all values from the database
    *
    * @param    array       list of modules
    * @param    string      search pattern
    * @param    array       list of topics
    * @return   array       module.separator.topic => value
    */
    public function getAllValues($a_modules = array(), $a_pattern = '', $a_topics = array())
    {
        return self::_getValues($this->key, $a_modules, $a_topics, $a_pattern);
    }
    
    
    /**
    * Get only the changed values from the database
    * which differ from the original language file.
    *
    * @param    array       list of modules
    * @param    string      search pattern
    * @param    array       list of topics
    * @return   array       module.separator.topic => value
    */
    public function getChangedValues($a_modules = array(), $a_pattern = '', $a_topics = array())
    {
        return self::_getValues($this->key, $a_modules, $a_topics, $a_pattern, 'changed');
    }


    /**
    * Get only the unchanged values from the database
    * which are equal to the original language file.
    *
    * @param    array       list of modules
    * @param    array       search pattern
    * @param    array       list of topics
    * @return   array       module.separator.topic => value
    */
    public function getUnchangedValues($a_modules = array(), $a_pattern = '', $a_topics = array())
    {
        return self::_getValues($this->key, $a_modules, $a_topics, $a_pattern, 'unchanged');
    }

    /**
    * Get only the entries which don't exist in the global language file
    *
    * @param    array       list of modules
    * @param    array       search pattern
    * @param    array       list of topics
    * @return   array       module.separator.topic => value
    */
    public function getAddedValues($a_modules = array(), $a_pattern = '', $a_topics = array())
    {
        $global_file_obj = $this->getGlobalLanguageFile();
        $global_values = $global_file_obj->getAllValues();
        $local_values = self::_getValues($this->key, $a_modules, $a_topics, $a_pattern);

        return array_diff_key($local_values, $global_values);
    }


    /**
    * Get all values from the database for wich the global language file has a comment.
    *
    * Note: This function checks the comments in the globel lang file,
    *       not the remarks in the database!
    *
    * @param    array       list of modules
    * @param    array       search pattern
    * @param    array       list of topics
    * @return   array       module.separator.topic => value
    */
    public function getCommentedValues($a_modules = array(), $a_pattern = '', $a_topics = array())
    {
        $global_file_obj = $this->getGlobalLanguageFile();
        $global_comments = $global_file_obj->getAllComments();
        $local_values = self::_getValues($this->key, $a_modules, $a_topics, $a_pattern);

        return array_intersect_key($local_values, $global_comments);
    }


    /**
    * Get the local values merged into the values of the global language file
    *
    * The returned array contains:
    * 1. all entries that exist globally, with their local values,
    *	 ordered like in the global language file
    * 2. all additional local entries,
    *	 ordered by module and identifier
    *
    * @return   array       module.separator.topic => value
    */
    public function getMergedValues()
    {
        $global_file_obj = $this->getGlobalLanguageFile();
        $global_values = $global_file_obj->getAllValues();
        $local_values = self::_getValues($this->key);

        return array_merge($global_values, $local_values);
    }

    /**
    * Get the local remarks merged into the remarks of the global language file
    *
    * The returned array contains:
    * 1. all remarks that exist globally, with their local values,
    *	 ordered like in the global language file
    * 2. all additional local remarks,
    *	 ordered by module and identifier
    *
    * @return   array       module.separator.topic => value
    */
    public function getMergedRemarks()
    {
        $global_file_obj = $this->getGlobalLanguageFile();
        $global_comments = $global_file_obj->getAllComments();

        // get remarks including empty remarks for local changes
        $local_remarks = self::_getRemarks($this->key, true);

        return array_merge($global_comments, $local_remarks);
    }



    /**
    * Import a language file into the ilias database
    *
    * @param    string  	handling of existing values
    *						('keepall','keepnew','replace','delete')
    */
    public function importLanguageFile($a_file, $a_mode_existing = 'keepnew')
    {
        global $DIC;
        $ilDB = $DIC->database();
        /** @var ilErrorHandling $ilErr */
        $ilErr = $DIC['ilErr'];

        // read the new language file
        require_once "./Services/Language/classes/class.ilLanguageFile.php";
        $import_file_obj = new ilLanguageFile($a_file);
        if (!$import_file_obj->read()) {
            $ilErr->raiseError($import_file_obj->getErrorMessage(), $ilErr->MESSAGE);
        }

        switch ($a_mode_existing) {
            // keep all existing entries
            case 'keepall':
                $to_keep = $this->getAllValues();
                break;

            // keep existing online changes
            case 'keepnew':
                $to_keep = $this->getChangedValues();
                break;

            // replace all existing definitions
            case 'replace':
                $to_keep = array();
                break;

           // delete all existing entries
            case 'delete':
                ilObjLanguage::_deleteLangData($this->key, false);
                $ilDB->manipulate("DELETE FROM lng_modules WHERE lang_key = " .
                    $ilDB->quote($this->key, "text"));
                $to_keep = array();
                break;
                
            default:
                return;
        }
        
        // process the values of the import file
        $to_save = array();
        foreach ($import_file_obj->getAllValues() as $key => $value) {
            if (!isset($to_keep[$key])) {
                $to_save[$key] = $value;
            }
        }
        self::_saveValues($this->key, $to_save, $import_file_obj->getAllComments());
    }

    /**
    * Get all modules of a language
    *
    * @access   static
    * @param    string      language key
    * @return   array       list of modules
    */
    public static function _getModules($a_lang_key)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT DISTINCT module FROM lng_data WHERE " .
            " lang_key = " . $ilDB->quote($a_lang_key, "text") . " order by module";
        $set = $ilDB->query($q);

        $modules = array();
        while ($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $modules[] = $rec["module"];
        }
        return $modules;
    }


    /**
    * Get all remarks of a language
    *
    * @access   static
    * @param    string      language key
    * @param    boolean     include empty remarks for local changes
    * @return   array       module.separator.topic => remarks
    */
    public static function _getRemarks($a_lang_key, $a_all_changed = false)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $q = "SELECT module, identifier, remarks"
        . " FROM lng_data"
        . " WHERE lang_key = " . $ilDB->quote($a_lang_key, "text");

        if ($a_all_changed) {
            $q .= " AND (remarks IS NOT NULL OR local_change IS NOT NULL)";
        } else {
            $q .= " AND remarks IS NOT NULL";
        }

        $result = $ilDB->query($q);

        $remarks = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $remarks[$row["module"] . $lng->separator . $row["identifier"]] = $row["remarks"];
        }
        return $remarks;
    }


    /**
    * Get the translations of specified topics
    *
    * @access   static
    * @param    string      language key
    * @param    array       list of modules
    * @param    array       list of topics
    * @param    array       search pattern
    * @param    string      local change state ('changed', 'unchanged', '')
    * @return   array       module.separator.topic => value
    */
    public static function _getValues(
        $a_lang_key,
        $a_modules = array(),
        $a_topics = array(),
        $a_pattern = '',
        $a_state = ''
    ) {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $q = "SELECT * FROM lng_data WHERE" .
            " lang_key = " . $ilDB->quote($a_lang_key, "text") . " ";
        
        if (is_array($a_modules) && count($a_modules) > 0) {
            $q .= " AND " . $ilDB->in("module", $a_modules, false, "text");
        }
        if (is_array($a_topics) && count($a_topics) > 0) {
            $q .= " AND " . $ilDB->in("identifier", $a_topics, false, "text");
        }
        if ($a_pattern) {
            $q .= " AND " . $ilDB->like("value", "text", "%" . $a_pattern . "%");
        }
        if ($a_state == "changed") {
            $q .= " AND NOT local_change IS NULL ";
        }
        if ($a_state == "unchanged") {
            $q .= " AND local_change IS NULL ";
        }
        $q .= " ORDER BY module, identifier";

        $set = $ilDB->query($q);

        $values = array();
        while ($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $values[$rec["module"] . $lng->separator . $rec["identifier"]] = $rec["value"];
        }
        return $values;
    }




    /**
    * Save a set of translation in the database
    *
    * @access   static
    * @param    string      language key
    * @param    array       module.separator.topic => value
    * @param    array       module.separator.topic => remarks
    */
    public static function _saveValues($a_lang_key, $a_values = array(), $a_remarks = array())
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        if (!is_array($a_values)) {
            return;
        }
        $save_array = array();
        $save_date = date("Y-m-d H:i:s", time());
        
        // read and get the global values
        require_once "./Services/Language/classes/class.ilLanguageFile.php";
        $global_file_obj = ilLanguageFile::_getGlobalLanguageFile($a_lang_key);
        $global_values = $global_file_obj->getAllValues();
        $global_comments = $global_file_obj->getAllComments();

        // save the single translations in lng_data
        foreach ($a_values as $key => $value) {
            $keys = explode($lng->separator, $key);
            if (count($keys) == 2) {
                $module = $keys[0];
                $topic = $keys[1];
                $save_array[$module][$topic] = $value;

                if ($global_values[$key] != $value
                or $global_comments[$key] != $a_remarks[$key]) {
                    $local_change = $save_date;
                } else {
                    $local_change = null;
                }

                ilObjLanguage::replaceLangEntry(
                    $module,
                    $topic,
                    $a_lang_key,
                    $value,
                    $local_change,
                    $a_remarks[$key]
                );
            }
        }

        // save the serialized module entries in lng_modules
        foreach ($save_array as $module => $entries) {
            $set = $ilDB->query(sprintf(
                "SELECT * FROM lng_modules " .
                "WHERE lang_key = %s AND module = %s",
                $ilDB->quote($a_lang_key, "text"),
                $ilDB->quote($module, "text")
            ));
            $row = $ilDB->fetchAssoc($set);
            
            $arr = unserialize($row["lang_array"]);
            if (is_array($arr)) {
                $entries = array_merge($arr, $entries);
            }
            ilObjLanguage::replaceLangModule($a_lang_key, $module, $entries);
        }


        require_once('class.ilCachedLanguage.php');
        ilCachedLanguage::getInstance($a_lang_key)->flush();
    }


    /**
    * Delete a set of translation in the database
    *
    * @access   static
    * @param    string      language key
    * @param    array       module.separator.topic => value
    */
    public static function _deleteValues($a_lang_key, $a_values = array())
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        if (!is_array($a_values)) {
            return;
        }
        $delete_array = array();

        // save the single translations in lng_data
        foreach ($a_values as $key => $value) {
            $keys = explode($lng->separator, $key);
            if (count($keys) == 2) {
                $module = $keys[0];
                $topic = $keys[1];
                $delete_array[$module][$topic] = $value;

                ilObjLanguage::deleteLangEntry($module, $topic, $a_lang_key);
            }
        }

        // save the serialized module entries in lng_modules
        foreach ($delete_array as $module => $entries) {
            $set = $ilDB->query(sprintf(
                "SELECT * FROM lng_modules " .
                "WHERE lang_key = %s AND module = %s",
                $ilDB->quote($a_lang_key, "text"),
                $ilDB->quote($module, "text")
            ));
            $row = $ilDB->fetchAssoc($set);

            $arr = unserialize($row["lang_array"]);
            if (is_array($arr)) {
                $entries = array_diff_key($arr, $entries);
            }
            ilObjLanguage::replaceLangModule($a_lang_key, $module, $entries);
        }
    }
} // END class.ilObjLanguageExt
