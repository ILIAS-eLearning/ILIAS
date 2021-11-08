<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * COPage Data set class
 *
 * This class implements the following entities:
 * - pgtp: page layout template
 *
 * Please note that the usual page xml export DOES NOT use the dataset.
 * The page export uses pre-existing methods to create the xml.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilCOPageDataSet extends ilDataSet
{
    protected $master_lang_only = false;

    /**
     * Set master language only
     *
     * @param bool $a_val export only master language
     */
    public function setMasterLanguageOnly($a_val)
    {
        $this->master_lang_only = $a_val;
    }

    /**
     * Get master language only
     *
     * @return bool export only master language
     */
    public function getMasterLanguageOnly()
    {
        return $this->master_lang_only;
    }

    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("4.2.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Services/COPage/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        // pgtp: page layout template
        if ($a_entity == "pgtp") {
            switch ($a_version) {
                case "4.2.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "SpecialPage" => "integer",
                        "StyleId" => "integer");
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
        $db = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        // mep_data
        if ($a_entity == "pgtp") {
            switch ($a_version) {
                case "4.2.0":
                    $this->getDirectDataFromQuery("SELECT layout_id id, title, description, " .
                        " style_id, special_page " .
                        " FROM page_layout " .
                        "WHERE " .
                        $db->in("layout_id", $a_ids, false, "integer"));
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
    
    ////
    //// Needs abstraction (interface?) and version handling
    ////
    
    
    /**
     * Import record
     * @param
     * @return void
     */
    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        switch ($a_entity) {
            case "pgtp":
                $pt = new ilPageLayout();
                $pt->setTitle($a_rec["Title"]);
                $pt->setDescription($a_rec["Description"]);
                $pt->setSpecialPage($a_rec["SpecialPage"]);
                $pt->update();
                
                $this->current_obj = $pt;
                $a_mapping->addMapping(
                    "Services/COPage",
                    "pgtp",
                    $a_rec["Id"],
                    $pt->getId()
                );
                $a_mapping->addMapping(
                    "Services/COPage",
                    "pg",
                    "stys:" . $a_rec["Id"],
                    "stys:" . $pt->getId()
                );
                break;
        }
    }
}
