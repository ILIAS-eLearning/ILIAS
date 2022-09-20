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
 * Class ilOrgUnitImporter
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitImporter extends ilXmlImporter
{
    /* @var array $lang_var => language variable, import_id => the reference or import id, depending on the ou_id_type */
    public array $errors = [];
    /* @var array lang_var => language variable, import_id => the reference or import id, depending on the ou_id_type */
    public array $warnings = [];
    /* @var array keys in {updated, edited, deleted} */
    public array $stats;
    private ilDBInterface $database;

    public function __construct()
    {
        global $DIC;
        $this->database = $DIC->database();
    }

    /** @return bool|int */
    protected function buildRef(int $id, string $type) /*: bool|int*/
    {
        if ($type === 'reference_id') {
            if (!ilObjOrgUnit::_exists($id, true)) {
                return false;
            }

            return $id;
        } elseif ($type === 'external_id') {
            $obj_id = ilObject::_lookupObjIdByImportId($id);

            if (ilObject::_lookupType($obj_id) !== 'orgu') {
                return false;
            }

            if (!ilObject::_hasUntrashedReference($obj_id)) {
                return false;
            }

            $ref_ids = ilObject::_getAllReferences($obj_id);

            if (!count($ref_ids)) {
                return false;
            }

            foreach ($ref_ids as $ref_id) {
                if (!ilObject::_isInTrash($ref_id)) {
                    return $ref_id;
                }
            }

            return false;
        } else {
            return false;
        }
    }

    public function hasMoreThanOneMatch(string $external_id): bool
    {
        $query = "SELECT * FROM object_data " .
            "INNER JOIN object_reference as ref on ref.obj_id = object_data.obj_id and ref.deleted is null " .
            'WHERE object_data.type = "orgu" and import_id = ' . $this->database->quote($external_id, "text") . " " .
            "ORDER BY create_date DESC";

        $res = $this->database->query($query);

        if ($this->database->numRows($res) > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function hasErrors(): bool
    {
        return count($this->errors) != 0;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) != 0;
    }

    public function addWarning(string $lang_var, string $import_id, ?string $action = null): void
    {
        $this->warnings[] = array('lang_var' => $lang_var, 'import_id' => $import_id, 'action' => $action);
    }

    public function addError(string $lang_var, string $import_id, ?string $action = null): void
    {
        $this->errors[] = array('lang_var' => $lang_var, 'import_id' => $import_id, 'action' => $action);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    /** @deprecated */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $container_mappings = $a_mapping->getMappingsOfEntity("Services/Container", "objs");
        foreach ($container_mappings as $old => $new) {
            if (ilObject2::_lookupType($new) === 'orgu') {
                $a_mapping->addMapping('Modules/OrgUnit', 'orgu', $old, $new);
            }
        }
    }
}
