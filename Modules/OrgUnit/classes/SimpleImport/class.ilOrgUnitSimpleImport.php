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
 ********************************************************************
 */
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitSimpleImport
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitSimpleImport extends ilOrgUnitImporter
{
    public function simpleImport(string $file_path)
    {
        $this->stats = array("created" => 0, "updated" => 0, "deleted" => 0);
        $a = file_get_contents($file_path, "r");
        $xml = new SimpleXMLElement($a);

        if (!count($xml->OrgUnit)) {
            $this->addError("no_orgunit", $xml->external_id, null);
            return;
        }

        foreach ($xml->OrgUnit as $o) {
            $this->simpleImportElement($o);
        }
    }

    public function simpleImportElement(SimpleXMLElement $o)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $title = (string) $o->title;
        $description = (string) $o->description;
        $external_id = (string) $o->external_id;
        $create_mode = true;
        $attributes = $o->attributes();
        $action = (string) $attributes->action;
        $ou_id = (int) $attributes->ou_id;
        $ou_id_type = (string) $attributes->ou_id_type;
        $ou_parent_id = (int) $attributes->ou_parent_id;
        $ou_parent_id_type = (string) $attributes->ou_parent_id_type;

        if ($ou_id == ilObjOrgUnit::getRootOrgRefId()) {
            $this->addWarning("cannot_change_root_node", $ou_id ? $ou_id : $external_id, $action);

            return;
        }

        if ($ou_parent_id == "__ILIAS") {
            $ou_parent_id = ilObjOrgUnit::getRootOrgRefId();
            $ou_parent_id_type = "reference_id";
        }

        //see mantis 0024601
        if ($ou_id_type == 'external_id') {
            if (strlen($external_id) == 0) {
                $external_id = $ou_id;
            }

            if ($this->hasMoreThanOneMatch($external_id)) {
                $this->addError("ou_more_than_one_match_found", $external_id, $action);

                return;
            }
        }

        $ref_id = $this->buildRef($ou_id, $ou_id_type);
        $parent_ref_id = $this->buildRef($ou_parent_id, $ou_parent_id_type);

        if ($action == "delete") {
            if (!$parent_ref_id) {
                $this->addError("ou_parent_id_not_valid", $ou_id ? $ou_id : $external_id, $action);

                return;
            }
            if (!$ref_id) {
                $this->addError("ou_id_not_valid", $ou_id ? $ou_id : $external_id, $action);

                return;
            }
            $ru = new ilRepUtil($this);
            try {
                $ru->deleteObjects($parent_ref_id, array($ref_id)) !== false;
                $this->stats["deleted"]++;
            } catch (Exception $e) {
                $this->addWarning("orgu_already_deleted", $ou_id ? $ou_id : $external_id, $action);
            }

            return;
        } elseif ($action == "update") {
            if (!$parent_ref_id) {
                $this->addError("ou_parent_id_not_valid", $ou_id ? $ou_id : $external_id, $action);

                return;
            }
            if (!$ref_id) {
                $this->addError("ou_id_not_valid", $ou_id ? $ou_id : $external_id, $action);

                return;
            }
            $object = new ilObjOrgUnit($ref_id);
            $object->setTitle($title);

            $object->updateTranslation($title, $description, $ilUser->getLanguage(), "");

            $object->setDescription($description);
            $object->update();
            $object->setImportId($external_id);
            $this->moveObject($ref_id, $parent_ref_id, $ou_id, $external_id);

            $this->stats["updated"]++;
        } elseif ($action == "create") {
            if (!$parent_ref_id) {
                $this->addError("ou_parent_id_not_valid", $ou_id ? $ou_id : $external_id, $action);

                return;
            }
            if ($external_id) {
                $obj_id = ilObject::_lookupObjIdByImportId($external_id);
                if (ilObject::_hasUntrashedReference($obj_id) && ilObject::_lookupType($obj_id) == 'orgu') {
                    $this->addError("ou_external_id_exists", $ou_id ? $ou_id : $external_id, $action);

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
     * @param int $ou_id this is only needed for displaying the warning.
     * @param int $external_id this is only needed for displaying the warning.
     */
    protected function moveObject(int $ref_id, int $parent_ref_id, int $ou_id, int $external_id)
    {
        global $DIC;
        $tree = $DIC['tree'];
        if ($parent_ref_id != $tree->getParentId($ref_id)) {
            try {
                $path = $tree->getPathId($parent_ref_id);
                if (in_array($ref_id, $path)) {
                    $this->addWarning("not_movable_to_subtree", $ou_id ? $ou_id : $external_id, "update");
                } else {
                    $tree->moveTree($ref_id, $parent_ref_id);
                }
            } catch (Exception $e) {
                global $DIC;
                $ilLog = $DIC['ilLog'];
                $this->addWarning("not_movable", $ou_id ? $ou_id : $external_id, "update");
                $ilLog->write($e->getMessage() . "\\n" . $e->getTraceAsString());
                error_log($e->getMessage() . "\\n" . $e->getTraceAsString());
            }
        }
    }
}
