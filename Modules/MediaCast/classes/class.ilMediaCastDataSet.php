<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Media cast data set class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaCastDataSet extends ilDataSet
{
    protected $order = array(); // [array]
    
    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("5.0.0", "4.1.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/MediaCast/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "mcst") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "PublicFiles" => "integer",
                        "Downloadable" => "integer",
                        "DefaultAccess" => "integer");

                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "PublicFiles" => "integer",
                        "Downloadable" => "integer",
                        "DefaultAccess" => "integer",
                        "Sortmode" => "integer",
                        "Viewmode" => "text",
                        "PublicFeed" => "integer",
                        "KeepRssMin" => "integer",
                        "Order" => "text"
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
                
        if ($a_entity == "mcst") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description, " .
                        " public_files, downloadable, def_access default_access" .
                        " FROM il_media_cast_data JOIN object_data ON (il_media_cast_data.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    break;

                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description, " .
                        " public_files, downloadable, def_access default_access, sortmode, viewmode" .
                        " FROM il_media_cast_data JOIN object_data ON (il_media_cast_data.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    
                    // #17174 - manual order?
                    $order = array();
                    $set = $ilDB->query("SELECT * FROM il_media_cast_data_ord" .
                        " WHERE " . $ilDB->in("obj_id", $a_ids, false, "integer") .
                        " ORDER BY pos");
                    while ($row = $ilDB->fetchAssoc($set)) {
                        $order[$row["obj_id"]][] = $row["item_id"];
                    }

                    foreach ($this->data as $k => $v) {
                        $this->data[$k]["PublicFeed"] = ilBlockSetting::_lookup("news", "public_feed", 0, $v["Id"]);
                        $this->data[$k]["KeepRssMin"] = (int) ilBlockSetting::_lookup("news", "keep_rss_min", 0, $v["Id"]);
                        
                        // manual order?
                        if ($this->data[$k]["Sortmode"] == 4 &&
                            array_key_exists($v["Id"], $order)) {
                            $this->data[$k]["Order"] = implode(";", $order[$v["Id"]]);
                        }
                    }
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
            case "mcst":
                
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjMediaCast();
                    $newObj->setType("mcst");
                    $newObj->create(true);
                }
                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setDefaultAccess($a_rec["DefaultAccess"]);
                $newObj->setDownloadable($a_rec["Downloadable"]);
                $newObj->setPublicFiles($a_rec["PublicFiles"]);

                if ($a_schema_version == "5.0.0") {
                    $newObj->setOrder($a_rec["Sortmode"]);
                    $newObj->setViewMode($a_rec["Viewmode"]);
                    
                    if ($a_rec["Order"]) {
                        $this->order[$newObj->getId()] = explode(";", $a_rec["Order"]);
                    }

                    ilBlockSetting::_write(
                        "news",
                        "public_feed",
                        $a_rec["PublicFeed"],
                        0,
                        $newObj->getId()
                    );

                    ilBlockSetting::_write(
                        "news",
                        "keep_rss_min",
                        $a_rec["KeepRssMin"],
                        0,
                        $newObj->getId()
                    );
                }

                $newObj->update(true);
                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/MediaCast", "mcst", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "Services/News",
                    "news_context",
                    $a_rec["Id"] . ":mcst:0:",
                    $newObj->getId() . ":mcst:0:"
                );
//var_dump($a_mapping->mappings["Services/News"]["news_context"]);
                break;
        }
    }
    
    public function getOrder()
    {
        return $this->order;
    }
}
