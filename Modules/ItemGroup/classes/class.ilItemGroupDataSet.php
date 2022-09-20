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
 * Item group data set class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupDataSet extends ilDataSet
{
    protected ilObjItemGroup $current_obj;

    public function getSupportedVersions(): array
    {
        return array("4.3.0", "5.3.0");
    }

    public function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/ItemGroup/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
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
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

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

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set): array
    {
        if ($a_entity == "itgr_item") {
            // make ref id an object id
            $a_set["ItemId"] = ilObject::_lookupObjId($a_set["ItemId"]);
        }
        return $a_set;
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        switch ($a_entity) {
            case "itgr":
                return array(
                    "itgr_item" => array("ids" => $a_rec["Id"])
                );
        }

        return [];
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        switch ($a_entity) {
            case "itgr":
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    /** @var ilObjItemGroup $newObj */
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
                $newObj->update();
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
