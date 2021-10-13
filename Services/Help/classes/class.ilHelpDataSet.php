<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Help system data set class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilHelpDataSet extends ilDataSet
{

    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("4.3.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Services/Help/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "help_map") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Chap" => "integer",
                        "Component" => "text",
                        "ScreenId" => "text",
                        "ScreenSubId" => "text",
                        "Perm" => "text"
                    );
            }
        }

        if ($a_entity == "help_tooltip") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "TtText" => "text",
                        "TtId" => "text",
                        "Comp" => "text",
                        "Lang" => "text"
                    );
            }
        }
    }

    /**
     * Read data
     * @param
     * @return void
     */
    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "help_map") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT chap, component, screen_id, screen_sub_id, perm " .
                        " FROM help_map " .
                        "WHERE " .
                        $ilDB->in("chap", $a_ids, false, "integer"));
                    break;
            }
        }
        
        if ($a_entity == "help_tooltip") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT id, tt_text, tt_id, comp, lang FROM help_tooltip");
                    break;
            }
        }
    }
    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }
    
    
    /**
     * Import record
     * @param
     * @return void
     */
    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        switch ($a_entity) {
            case "help_map":
                
                // without module ID we do nothing
                $module_id = $a_mapping->getMapping('Services/Help', 'help_module', 0);
                $t = $a_mapping->getAllMappings();
                if ($module_id) {
                    $new_chap = $a_mapping->getMapping(
                        'Services/Help',
                        'help_chap',
                        $a_rec["Chap"]
                    );

                    // new import (5.1): get chapter from learning module import mapping
                    if ($new_chap == 0) {
                        $new_chap = $a_mapping->getMapping(
                            'Modules/LearningModule',
                            'lm_tree',
                            $a_rec["Chap"]
                        );
                    }

                    if ($new_chap > 0) {
                        ilHelpMapping::saveMappingEntry(
                            $new_chap,
                            $a_rec["Component"],
                            $a_rec["ScreenId"],
                            $a_rec["ScreenSubId"],
                            $a_rec["Perm"],
                            $module_id
                        );
                    }
                }
                break;
                
            case "help_tooltip":
                
                // without module ID we do nothing
                $module_id = $a_mapping->getMapping('Services/Help', 'help_module', 0);
                if ($module_id) {
                    ilHelp::addTooltip($a_rec["TtId"], $a_rec["TtText"], $module_id);
                }
                break;
        }
    }
}
