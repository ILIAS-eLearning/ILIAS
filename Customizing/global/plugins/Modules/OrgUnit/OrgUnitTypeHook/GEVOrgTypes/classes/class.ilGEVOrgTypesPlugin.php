<?php
require_once('./Modules/OrgUnit/classes/Types/class.ilOrgUnitTypeHookPlugin.php');

/**
 * Org-Unit-Types for the generali.
 * @author Richard Klees Wanzenried <sw@studer-raimann.ch>
 */
class ilGevOrgTypesPlugin extends ilOrgUnitTypeHookPlugin {
    static $allow = false;

    function getPluginName()
    {
        return 'GEVOrgTypes';
    }

    public function allowDelete($a_type_id) {
        return self::$allow;
    }

    public function allowSetDefaultLanguage($a_type_id, $a_lang_code) {
        return self::$allow;
    }

    public function allowSetTitle($a_type_id, $a_lang_code, $a_title) {
        return self::$allow;
    }

    public function allowUpdate($a_type_id) {
        return self::$allow;
    }

    public function allowAssignAdvancedMDRecord($a_type_id, $a_record_id) {
        return self::$allow;
    }

    public function allowDeAssignAdvancedMDRecord($a_type_id, $a_record_id) {
        return self::$allow;
    }



}