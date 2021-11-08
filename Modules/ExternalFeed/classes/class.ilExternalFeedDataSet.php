<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * External feed data set class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExternalFeedDataSet extends ilDataSet
{
    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("4.1.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/ExternalFeed/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "feed") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Url" => "text");
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
                
        if ($a_entity == "feed") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT obj_id id, title, description url " .
                        " FROM object_data " .
                        "WHERE " .
                        $ilDB->in("obj_id", $a_ids, false, "integer"));
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
        //echo $a_entity;
        //var_dump($a_rec);

        switch ($a_entity) {
            case "feed":

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjExternalFeed();
                    $newObj->setType("feed");
                    $newObj->create(true);
                }

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Url"]);
                $newObj->update();
                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/ExternalFeed", "feed", $a_rec["Id"], $newObj->getId());

                // create the feed block
                $fb = new ilExternalFeedBlock();
                $fb->setTitle($a_rec["Title"]);
                $fb->setFeedUrl($a_rec["Url"]);
                $fb->setContextObjId($newObj->getId());
                $fb->setContextObjType("feed");
                $fb->create();

                break;
        }
    }
}
