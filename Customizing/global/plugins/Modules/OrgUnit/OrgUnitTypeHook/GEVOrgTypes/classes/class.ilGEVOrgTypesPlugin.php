<?php
require_once('./Modules/OrgUnit/classes/Types/class.ilOrgUnitTypeHookPlugin.php');

/**
 * Class ilExampleOrgUnitTypePlugin
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilGevOrgTypesPlugin extends ilOrgUnitTypeHookPlugin {

    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * Must be overwritten in plugin class of plugin
     * (and should be made final)
     *
     * @return    string    Plugin Name
     */
    function getPluginName()
    {
        return 'GEVOrgTypes';
    }

    public function allowDelete($a_type_id) {
        return false;
    }

    public function allowSetDefaultLanguage($a_type_id, $a_lang_code) {
        return false;
    }

    public function allowSetTitle($a_type_id, $a_lang_code, $a_title) {
        return false;
    }

    public function allowUpdate($a_type_id) {
        return false;
    }

    public function allowAssignAdvancedMDRecord($a_type_id, $a_record_id) {
        return false;
    }

    public function allowDeAssignAdvancedMDRecord($a_type_id, $a_record_id) {
        return false;
    }



}