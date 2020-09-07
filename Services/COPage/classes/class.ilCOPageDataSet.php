<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

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
 * @version $Id$
 * @ingroup ServicesCOPage
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
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("4.2.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Services/COPage/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
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
     *
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
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
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        return false;
    }
    
    ////
    //// Needs abstraction (interface?) and version handling
    ////
    
    
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case "pgtp":
                include_once("./Services/COPage/Layout/classes/class.ilPageLayout.php");
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
