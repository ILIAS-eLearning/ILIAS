<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilOrgUnitSimpleImport
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrgUnitSimpleImport extends ilOrgUnitImporter
{
    public function simpleImport($file_path)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->stats = array("created" => 0, "updated" => 0, "deleted" => 0);
        $a = file_get_contents($file_path, "r");
        $xml = new SimpleXMLElement($a);

        if (!count($xml->OrgUnit)) {
            $this->addError("no_orgunit", null, null);
            return;
        }

        foreach ($xml->OrgUnit as $o) {
            $this->simpleImportElement($o);
        }
    }

    public function simpleImportElement(SimpleXMLElement $o)
    {
        global $DIC;
        $tree = $DIC['tree'];
        $tpl = $DIC['tpl'];
        $ilUser = $DIC['ilUser'];
        $title = $o->title;
        $description = $o->description;
        $external_id = $o->external_id;
        $create_mode = true;
        $attributes = $o->attributes();
        $action = (string) $attributes->action;
        $ou_id = (string) $attributes->ou_id;
        $ou_id_type = (string) $attributes->ou_id_type;
        $ou_parent_id = (string) $attributes->ou_parent_id;
        $ou_parent_id_type = (string) $attributes->ou_parent_id_type;

        if ($ou_id == ilObjOrgUnit::getRootOrgRefId()) {
            $this->addWarning("cannot_change_root_node", $ou_id?$ou_id:$external_id, $action);
            return;
        }

        if ($ou_parent_id == "__ILIAS") {
            $ou_parent_id = ilObjOrgUnit::getRootOrgRefId();
            $ou_parent_id_type = "reference_id";
        }

        //see mantis 0024601
        if ($ou_id_type == 'external_id') {
            if ($this->hasMoreThanOneMatch($ou_id)) {
                $this->addError("ou_more_than_one_match_found", $ou_id?$ou_id:$external_id, $action);
                return;
            }
        }

        $ref_id = $this->buildRef($ou_id, $ou_id_type);
        $parent_ref_id = $this->buildRef($ou_parent_id, $ou_parent_id_type);

        if ($action == "delete") {
            if (!$parent_ref_id) {
                $this->addError("ou_parent_id_not_valid", $ou_id?$ou_id:$external_id, $action);
                return;
            }
            if (!$ref_id) {
                $this->addError("ou_id_not_valid", $ou_id?$ou_id:$external_id, $action);
                return;
            }
            $ru = new ilRepUtil($this);
            try {
                $ru->deleteObjects($parent_ref_id, array($ref_id)) !== false;
                $this->stats["deleted"]++;
            } catch (Exception $e) {
                $this->addWarning("orgu_already_deleted", $ou_id?$ou_id:$external_id, $action);
            }
            return;
        } elseif ($action == "update") {
            if (!$parent_ref_id) {
                $this->addError("ou_parent_id_not_valid", $ou_id?$ou_id:$external_id, $action);
                return;
            }
            if (!$ref_id) {
                $this->addError("ou_id_not_valid", $ou_id?$ou_id:$external_id, $action);
                return;
            }
            $object = new ilObjOrgUnit($ref_id);
            $object->setTitle($title);

            $arrTranslations = $object->getTranslations();
            $object->updateTranslation($title, $description, $ilUser->getLanguage(), "");

            $object->setDescription($description);
            $object->update();
            $object->setImportId($external_id);
            $this->moveObject($ref_id, $parent_ref_id, $ou_id, $external_id);

            $this->stats["updated"]++;
        } elseif ($action == "create") {
            if (!$parent_ref_id) {
                $this->addError("ou_parent_id_not_valid", $ou_id?$ou_id:$external_id, $action);
                return;
            }
            if ($external_id) {
                $obj_id = ilObject::_lookupObjIdByImportId($external_id);
                if (ilObject::_hasUntrashedReference($obj_id) && ilObject::_lookupType($obj_id) == 'orgu') {
                    $this->addError("ou_external_id_exists", $ou_id?$ou_id:$external_id, $action);
                    return;
                }
            }
            $object = new ilObjOrgUnit();
            $object->setTitle($title);
            $object->setDescription($description);
            $object->setImportId($external_id);
            $object->create();
            $object->createReference();
            $object->putInTree($parent_ref_id);
            $object->setPermissions($ou_parent_id);
            $this->stats["created"]++;
        } else {
            $this->addError("no_valid_action_given", $ou_id, $action);
        }
    }

    /**
     * @param $ref_id int
     * @param $parent_ref_id int
     * @param $ou_id mixed This is only needed for displaying the warning.
     * @param $external_id mixed This is only needed for displaying the warning.
     */
    protected function moveObject($ref_id, $parent_ref_id, $ou_id, $external_id)
    {
        global $DIC;
        $tree = $DIC['tree'];
        if ($parent_ref_id != $tree->getParentId($ref_id)) {
            try {
                $path = $tree->getPathId($parent_ref_id);
                if (in_array($ref_id, $path)) {
                    $this->addWarning("not_movable_to_subtree", $ou_id?$ou_id:$external_id, "update");
                } else {
                    $tree->moveTree($ref_id, $parent_ref_id);
                }
            } catch (Exception $e) {
                global $DIC;
                $ilLog = $DIC['ilLog'];
                $this->addWarning("not_movable", $ou_id?$ou_id:$external_id, "update");
                $ilLog->write($e->getMessage() . "\\n" . $e->getTraceAsString());
                error_log($e->getMessage() . "\\n" . $e->getTraceAsString());
            }
        }
    }
}
