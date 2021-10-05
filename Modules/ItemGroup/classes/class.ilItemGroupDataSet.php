<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Item group data set class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilItemGroupDataSet extends ilDataSet
{
    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("4.3.0", "5.3.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/ItemGroup/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "itgr") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text");
                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "HideTitle" => "integer",
                        "Behaviour" => "integer",
                        "Title" => "text",
                        "Description" => "text");
            }
        }

        if ($a_entity == "itgr_item") {
            switch ($a_version) {
                case "4.3.0":
                case "5.3.0":
                    return array(
                        "ItemGroupId" => "integer",
                        "ItemId" => "text"
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
                
        if ($a_entity == "itgr") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT obj_id id, title, description " .
                        " FROM object_data " .
                        "WHERE " .
                        $ilDB->in("obj_id", $a_ids, false, "integer"));
                    break;
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT obj_id id, title, description, hide_title, behaviour " .
                        " FROM object_data JOIN itgr_data ON (object_data.obj_id = itgr_data.id)" .
                        "WHERE " .
                        $ilDB->in("obj_id", $a_ids, false, "integer"));
                    break;

            }
        }

        if ($a_entity == "itgr_item") {
            switch ($a_version) {
                case "4.3.0":
                case "5.3.0":
                    $this->getDirectDataFromQuery($q = "SELECT item_group_id itgr_id, item_ref_id item_id" .
                        " FROM item_group_item " .
                        "WHERE " .
                        $ilDB->in("item_group_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }
    
    /**
     * Get xml record (export)
     * @param	array	abstract data record
     * @return	array	xml record
     */
    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
    {
        if ($a_entity == "itgr_item") {
            // make ref id an object id
            $a_set["ItemId"] = ilObject::_lookupObjId($a_set["ItemId"]);
        }
        return $a_set;
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
        switch ($a_entity) {
            case "itgr":
                return array(
                    "itgr_item" => array("ids" => $a_rec["Id"])
                );
        }

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
            case "itgr":
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjItemGroup();
                    $newObj->setType("itgr");
                    $newObj->create(true);
                }
                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setBehaviour($a_rec["Behaviour"]);
                $newObj->setHideTitle($a_rec["HideTitle"]);
                $newObj->update(true);
                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/ItemGroup", "itgr", $a_rec["Id"], $newObj->getId());
                
                break;
                
            case "itgr_item":
                if ($obj_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['ItemId'])) {
                    $ref_id = current(ilObject::_getAllReferences($obj_id));
                    $itgri = new ilItemGroupItems();
                    $itgri->setItemGroupId($this->current_obj->getId());
                    $itgri->read();
                    $itgri->addItem($ref_id);
                    $itgri->update();
                }
                break;

        }
    }
}
