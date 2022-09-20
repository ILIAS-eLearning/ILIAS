<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Media Pool Data set class
 *
 * This class implements the following entities:
 * - mob: object data
 * - mob_media_item: data from table media_item
 * - mob_mi_map_area: data from a table map_area
 * - mob_mi_parameter: data from a table mob_parameter
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaObjectDataSet extends ilDataSet
{
    protected ilMediaItem $current_media_item;
    protected ilObjMediaObject $current_mob;
    protected ilLogger $mob_log;
    protected bool $use_previous_import_ids = false;

    public function __construct()
    {
        parent::__construct();
        $this->mob_log = ilLoggerFactory::getLogger('mob');
    }

    /**
     * Set use previous import ids
     *
     * @param bool $a_val use previous import ids
     */
    public function setUsePreviousImportIds(bool $a_val): void
    {
        $this->use_previous_import_ids = $a_val;
    }

    public function getUsePreviousImportIds(): bool
    {
        return $this->use_previous_import_ids;
    }

    /**
     * @return string[]
     */
    public function getSupportedVersions(): array
    {
        return array("5.1.0", "4.3.0", "4.1.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Services/MediaObject/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        // mob
        if ($a_entity == "mob") {
            switch ($a_version) {
                case "4.1.0":
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Dir" => "directory"
                    );

                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Dir" => "directory",
                        "ImportId" => "text"
                    );
            }
        }

        // media item
        if ($a_entity == "mob_media_item") {
            switch ($a_version) {
                case "5.1.0":
                case "4.3.0":
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "MobId" => "integer",
                        "Width" => "integer",
                        "Height" => "integer",
                        "Halign" => "text",
                        "Caption" => "text",
                        "Nr" => "integer",
                        "Purpose" => "text",
                        "Location" => "text",
                        "LocationType" => "text",
                        "Format" => "text",
                        "TextRepresentation" => "text"
                    );
            }
        }

        // map areas
        if ($a_entity == "mob_mi_map_area") {
            switch ($a_version) {
                case "4.1.0":
                case "4.3.0":
                case "5.1.0":
                    return array(
                        "MiId" => "integer",
                        "Nr" => "integer",
                        "Shape" => "text",
                        "Coords" => "text",
                        "LinkType" => "text",
                        "Title" => "text",
                        "Href" => "text",
                        "Target" => "text",
                        "Type" => "text",
                        "TargetFrame" => "text",
                        "HighlightMode" => "text",
                        "HighlightText" => "text"
                    );
            }
        }

        // media item parameter
        if ($a_entity == "mob_mi_parameter") {
            switch ($a_version) {
                case "4.1.0":
                case "4.3.0":
                case "5.1.0":
                    return array(
                        "MiId" => "integer",
                        "Name" => "text",
                        "Value" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(
        string $a_entity,
        string $a_version,
        array $a_ids
    ): void {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // mob
        if ($a_entity == "mob") {
            $this->data = array();

            switch ($a_version) {
                case "4.1.0":
                case "4.3.0":
                    foreach ($a_ids as $mob_id) {
                        if (ilObject::_lookupType($mob_id) == "mob") {
                            $this->data[] = array("Id" => $mob_id,
                                "Title" => ilObject::_lookupTitle($mob_id),
                                "Description" => ilObject::_lookupDescription($mob_id)
                            );
                        }
                    }
                    break;

                case "5.1.0":
                    foreach ($a_ids as $mob_id) {
                        if (ilObject::_lookupType($mob_id) == "mob") {
                            $this->data[] = array("Id" => $mob_id,
                                "Title" => ilObject::_lookupTitle($mob_id),
                                "Description" => ilObject::_lookupDescription($mob_id),
                                "ImportId" => ilObject::_lookupImportId($mob_id)
                            );
                        }
                    }
                    break;
            }
        }

        // media item
        if ($a_entity == "mob_media_item") {
            switch ($a_version) {
                case "5.1.0":
                case "4.3.0":
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, mob_id, width, height, halign," .
                        "caption, nr, purpose, location, location_type, format, text_representation" .
                        " FROM media_item WHERE " .
                        $ilDB->in("mob_id", $a_ids, false, "integer"));
                    break;
            }
        }


        // media item map area
        if ($a_entity == "mob_mi_map_area") {
            switch ($a_version) {
                case "4.1.0":
                case "4.3.0":
                case "5.1.0":
                    foreach ($this->getDirectDataFromQuery("SELECT item_id mi_id, nr" .
                        " ,shape, coords, link_type, title, href, target, type, target_frame, " .
                        " highlight_mode, highlight_class" .
                        " FROM map_area " .
                        " WHERE " .
                        $ilDB->in("item_id", $a_ids, false, "integer") .
                        " ORDER BY nr", true, false) as $r) {
                        $r["Target"] = ilUtil::insertInstIntoID($r["Target"]);

                        // see ilPageObject::insertInstIntoIDs
                        if ($r["Type"] == "RepositoryItem") {
                            $id_arr = explode("_", $r["Target"]);
                            $ref_id = $id_arr[3];
                            $obj_id = ilObject::_lookupObjId($id_arr[3]);

                            $otype = ilObject::_lookupType($obj_id);
                            if ($obj_id > 0) {
                                $id = $otype . "_" . $obj_id . "_" . $ref_id;
                                $r["Target"] = "il_" . $id_arr[1] . "_" . $id;
                            }
                        }

                        $this->data[] = $r;
                    }
                    break;
            }
        }

        // media item parameter
        if ($a_entity == "mob_mi_parameter") {
            switch ($a_version) {
                case "4.1.0":
                case "4.3.0":
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT med_item_id mi_id, name, value" .
                        " FROM mob_parameter " .
                        " WHERE " .
                        $ilDB->in("med_item_id", $a_ids, false, "integer"));
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
    ): array {
        switch ($a_entity) {
            case "mob":
                return array(
                    "mob_media_item" => array("ids" => $a_rec["Id"] ?? null)
                );

            case "mob_media_item":
                return array(
                    "mob_mi_map_area" => array("ids" => $a_rec["Id"] ?? null),
                    "mob_mi_parameter" => array("ids" => $a_rec["Id"] ?? null)
                );
        }
        return [];
    }

    public function getXmlRecord(
        string $a_entity,
        string $a_version,
        array $a_set
    ): array {
        if ($a_entity == "mob") {
            $dir = ilObjMediaObject::_getDirectory($a_set["Id"]);
            $a_set["Dir"] = $dir;
        }

        return $a_set;
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        switch ($a_entity) {
            case "mob":

                $newObj = new ilObjMediaObject();
                $newObj->setType("mob");
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                // on translation re-import we are interested in the "previous" import id
                if ($this->getUsePreviousImportIds()) {
                    $newObj->setImportId($a_rec["ImportId"]);
                } else {
                    $newObj->setImportId("il_" . $this->getCurrentInstallationId() . "_mob_" . $a_rec["Id"]);
                }
                $newObj->create();
                $newObj->createDirectory();
                ilObjMediaObject::_createThumbnailDirectory($newObj->getId());
                $this->current_mob = $newObj;

                $dir = str_replace("..", "", $a_rec["Dir"]);
                if ($dir != "" && $this->getImportDirectory() != "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $target_dir = $dir = ilObjMediaObject::_getDirectory($newObj->getId());
                    $this->mob_log->debug("s:-$source_dir-,t:-$target_dir-");
                    ilFileUtils::rCopy($source_dir, $target_dir);
                    ilObjMediaObject::renameExecutables($target_dir);
                    ilMediaSvgSanitizer::sanitizeDir($target_dir);	// see #20339
                }

                $a_mapping->addMapping("Services/MediaObjects", "mob", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "Services/MetaData",
                    "md",
                    "0:" . $a_rec["Id"] . ":mob",
                    "0:" . $newObj->getId() . ":mob"
                );
                break;

            case "mob_media_item":

                // determine parent mob
                $mob_id = (int) $a_mapping->getMapping("Services/MediaObjects", "mob", $a_rec["MobId"]);
                if (is_object($this->current_mob) && $this->current_mob->getId() == $mob_id) {
                    $mob = $this->current_mob;
                } else {
                    $mob = new ilObjMediaObject($mob_id);
                }

                $newObj = new ilMediaItem();
                $newObj->setMobId($mob_id);
                $newObj->setWidth($a_rec["Width"]);
                $newObj->setHeight($a_rec["Height"]);
                $newObj->setCaption($a_rec["Caption"]);
                $newObj->setNr($a_rec["Nr"]);
                $newObj->setPurpose($a_rec["Purpose"]);
                $newObj->setLocation($a_rec["Location"]);
                $newObj->setLocationType($a_rec["LocationType"]);
                $newObj->setFormat($a_rec["Format"]);
                $newObj->setTextRepresentation($a_rec["TextRepresentation"]);
                $newObj->create();
                $this->current_media_item = $newObj;

                $a_mapping->addMapping("Services/MediaObjects", "mob_media_item", $a_rec["Id"], $newObj->getId());

                break;

            case "mob_mi_parameter":

                // get media item
                $med_id = (int) $a_mapping->getMapping("Services/MediaObjects", "mob_media_item", $a_rec["MiId"]);
                if (is_object($this->current_media_item) && $this->current_media_item->getId() == $med_id) {
                    $med = $this->current_media_item;
                } else {
                    $med = new ilMediaItem($med_id);
                }
                $med->writeParameter($a_rec["Name"], $a_rec["Value"]);

                break;

            case "mob_mi_map_area":
                // get media item
                $med_id = (int) $a_mapping->getMapping("Services/MediaObjects", "mob_media_item", $a_rec["MiId"]);
                if (is_object($this->current_media_item) && $this->current_media_item->getId() == $med_id) {
                    $med = $this->current_media_item;
                } else {
                    $med = new ilMediaItem($med_id);
                }

                $map_area = new ilMapArea();
                $map_area->setItemId($med_id);
                $map_area->setNr($a_rec["Nr"]);
                $map_area->setShape($a_rec["Shape"]);
                $map_area->setCoords($a_rec["Coords"]);
                $map_area->setLinkType($a_rec["LinkType"]);
                $map_area->setTitle($a_rec["Title"]);
                $map_area->setHref($a_rec["Href"]);
                $map_area->setTarget($a_rec["Target"]);
                $map_area->setType($a_rec["Type"]);
                $map_area->setTargetFrame($a_rec["TargetFrame"]);
                $map_area->setHighlightMode($a_rec["HighlightMode"]);
                $map_area->setHighlightClass($a_rec["HighlightClass"]);
                $map_area->create();
                break;
        }
    }
}
