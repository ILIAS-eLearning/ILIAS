<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Rating Data set class
 *
 * This class implements the following entities:
 * - rating_category: data from il_rating_cat
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ingroup ServicesRating
 */
class ilRatingDataSet extends ilDataSet
{
    /**
     * Get supported versions
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("4.3.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Services/Rating/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "rating_category") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "ParentId" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Pos" => "integer");
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
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "rating_category") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT id, parent_id, title," .
                        " description, pos" .
                        " FROM il_rating_cat" .
                        " WHERE " . $ilDB->in("parent_id", $a_ids, false, "integer"));
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
        
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        //echo $a_entity;
        //var_dump($a_rec);
    
        switch ($a_entity) {
            case "rating_category":
                if ($parent_id = $a_mapping->getMapping('Services/Rating', 'rating_category_parent_id', $a_rec['ParentId'])) {
                    include_once("./Services/Rating/classes/class.ilRatingCategory.php");
                    $newObj = new ilRatingCategory();
                    $newObj->setParentId($parent_id);
                    $newObj->save();
                    
                    $newObj->setTitle($a_rec["Title"]);
                    $newObj->setDescription($a_rec["Description"]);
                    $newObj->setPosition($a_rec["Pos"]);
                    $newObj->update();
                }
                break;
        }
    }
}
