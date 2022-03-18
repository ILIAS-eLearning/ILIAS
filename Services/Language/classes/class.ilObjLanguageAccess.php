<?php
/* Copyright (c) 1998-20014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjLanguageAccess
*
* Languages are not under RBAC control in ILIAS
*
* This class provides access checks for language maintenance
* based on the RBAC settings of the global language folder
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilObjLanguageAccess.php $
*
* @package AccessControl
*/
class ilObjLanguageAccess
{
    /**
     * Cached result of permission check for page translation
     * @var bool
     */
    protected static $cached_check_translate;


    /**
    * Permission check for translations
    *
    * This check is used for displaying the translation link on each page
    * - The page translation of the current language must be turned on
    * - The user must have read and write permissions to the language folder
    *
    * @access   static
    * @return   boolean     translation possible (true/false)
    */
    public static function _checkTranslate()
    {
        global $DIC;
        $lng = $DIC->language();
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        $rbacsystem = $DIC->rbac()->system();

        if (isset(self::$cached_check_translate)) {
            return self::$cached_check_translate;
        }

        if (!$ilSetting->get("lang_translate_" . $lng->getLangKey())) {
            self::$cached_check_translate = false;
            return self::$cached_check_translate;
        }

        if ($ilUser->getId()) {
            $ref_id = self::_lookupLangFolderRefId();
            self::$cached_check_translate =  $rbacsystem->checkAccess("read,write", (int) $ref_id);
        }
        else {
            self::$cached_check_translate = false;
        }

        return self::$cached_check_translate;
    }


    /**
    * Permission check for language maintenance (import/export)
    * - The user must have read and write permissions to the language folder
    *
    * @access   static
    * @return   boolean     maintenance possible (true/false)
    */
    public static function _checkMaintenance()
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        $rbacsystem = $DIC->rbac()->system();

        if ($ilUser->getId()) {
            $ref_id = self::_lookupLangFolderRefId();
            return $rbacsystem->checkAccess("read,write", (int) $ref_id);
        }
        return false;
    }


    /**
    * Lookup the ref_id of the global language folder
    *
    * @return   int     	language folder ref_id
    */
    public static function _lookupLangFolderRefId()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT ref_id FROM object_reference r, object_data d" .
        " WHERE r.obj_id = d.obj_id AND d.type = " . $ilDB->quote("lngf", "text");
        $set = $ilDB->query($q);
        $row = $ilDB->fetchAssoc($set);
        return $row['ref_id'];
    }
    

    /**
    * Lookup the object ID for a language key
    *
    * @param    string      language key
    * @param    integer     language object id
    */
    public static function _lookupId($a_key)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = "SELECT obj_id FROM object_data " .
        " WHERE type = " . $ilDB->quote("lng", "text") .
        " AND title = " . $ilDB->quote($a_key, "text");
        $set = $ilDB->query($q);
        $row = $ilDB->fetchAssoc($set);
        return $row['obj_id'];
    }


    /**
     * Get the link to translate the current page
     *
     * @return  string  translation link
     */
    public static function _getTranslationLink()
    {
        // ref id must be given to prevent params being deleted by ilAdministrtionGUI
        return "ilias.php"
        . "?ref_id=" . self::_lookupLangFolderRefId()
        . "&baseClass=ilAdministrationGUI"
        . "&cmdClass=ilobjlanguageextgui"
        . "&view_mode=translate"
        . "&reset_offset=true";
    }

    /**
     * Check if the current request is a page translation
     *
     * The page translation mode is used when the translation
     * of a single page is called by the translation link on a page footer.
     * On this screen only the topics stored from the calling page are shown for translation
     * and only a save function for these topics is offered.
     *
     * @return   bool      page translation (true or false)
     */
    public static function _isPageTranslation()
    {
        return (strtolower($_GET['cmdClass'] == 'ilobjlanguageextgui') and $_GET['view_mode'] == "translate");
    }


    /**
     * Store the collected language variable usages in the user session
     * This should be called as late as possible in a request
     */
    public static function _saveUsages()
    {
        global $DIC;
        $lng = $DIC->language();

        if (self::_checkTranslate() and !self::_isPageTranslation()) {
            $_SESSION['lang_ext_maintenance']['used_modules'] = array_keys($lng->getUsedModules());
            $_SESSION['lang_ext_maintenance']['used_topics'] = array_keys($lng->getUsedTopics());
        }
    }

    /**
     * Get the stored modules from the user session
     *
     * @return array    list of module names
     */
    public static function _getSavedModules()
    {
        $saved = $_SESSION['lang_ext_maintenance']['used_modules'];
        return is_array($saved) ? $saved : array();
    }

    /**
     * Get the stored topics from the user session
     *
     * @return array    list of module names
     */
    public static function _getSavedTopics()
    {
        $saved = $_SESSION['lang_ext_maintenance']['used_topics'];
        return is_array($saved) ? $saved : array();
    }
}
