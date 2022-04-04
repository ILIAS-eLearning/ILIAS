<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Rating Data set class
 *
 * This class implements the following entities:
 * - rating_category: data from il_rating_cat
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRatingDataSet extends ilDataSet
{
    public function getSupportedVersions() : array
    {
        return array("4.3.0");
    }
    
    /**
     * @inheritDoc
     */
    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        // PHP8-Review: Link with unencrypted protocol
        return "http://www.ilias.de/xml/Services/Rating/" . $a_entity;
    }
    
    /**
     * @inheritDoc
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "rating_category") {
            // PHP8-Review: 'switch' with single 'case'
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
        return [];
    }

    /**
     * @inheritDoc
     */
    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "rating_category") {
            // PHP8-Review: 'switch' with single 'case'
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

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }
        
    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        // PHP8-Review: 'switch' with single 'case'
        switch ($a_entity) {
            case "rating_category":
                if ($parent_id = $a_mapping->getMapping('Services/Rating', 'rating_category_parent_id', $a_rec['ParentId'])) {
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
