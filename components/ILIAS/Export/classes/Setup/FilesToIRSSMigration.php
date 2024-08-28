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

declare(strict_types=1);

namespace ILIAS\Export\Setup;

use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBConstants;
use ilDBInterface;
use ilFileUtils;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Export\ExportHandler\Repository\ResourceStakeholder;
use ilIniFilesLoadedObjective;
use ilObject;
use ilResourceStorageMigrationHelper;
use InvalidArgumentException;

class FilesToIRSSMigration implements Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "ilExportFilesToIRSSMigration";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 5;
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            ilResourceStorageMigrationHelper::getPreconditions(),
            [
                new ilIniFilesLoadedObjective(),
                new ilDatabaseInitializedObjective(),
                new ilDatabaseUpdatedObjective()
            ]
        );
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $res_export_file_info = $this->db->query(
            "SELECT obj_id, export_type, filename, create_date FROM export_file_info WHERE migrated = 0 LIMIT 1"
        );
        $row_export_file_info = $res_export_file_info->fetchAssoc();
        if (is_null($row_export_file_info)) {
            return;
        }
        $res_object_data = $this->db->query(
            "SELECT type FROM object_data WHERE obj_id = " . $this->db->quote((int) $row_export_file_info['obj_id'], ilDBConstants::T_INTEGER)
        );
        $row_object_data = $res_object_data->fetchAssoc();
        if (is_null($row_object_data)) {
            return;
        }
        $res_il_object_def = $this->db->query(
            "SELECT class_name, component, location, id FROM il_object_def where id = " . $this->db->quote($row_object_data['type'], ilDBConstants::T_TEXT)
        );
        $row_il_object_def = $this->db->fetchAssoc($res_il_object_def);
        if (is_null($row_il_object_def)) {
            return;
        }
        $res_il_plugin = $this->db->query("SELECT plugin_id FROM il_plugin");
        $plugin_ids = [];
        while ($row_il_plugin = $res_il_plugin->fetchAssoc()) {
            $plugin_ids[] = $row_il_plugin['plugin_id'];
        }
        $classname = $row_il_object_def['class_name'];
        $obj_id = (int) $row_export_file_info['obj_id'];
        $export_type = $row_export_file_info['export_type'];
        $filename = $row_export_file_info['filename'];
        $create_date = $row_export_file_info['create_date'];
        $type = $row_object_data['type'];
        $component_for_type = $row_il_object_def['component'];
        $is_plugin = in_array($row_il_object_def["id"], $plugin_ids);
        $location = $row_il_object_def['location'];
        $export_dir = $this->getExportDirectory(
            $classname,
            $is_plugin,
            $location,
            $component_for_type,
            $obj_id,
            $export_type,
            $type
        );
        $file_path = $export_dir . DIRECTORY_SEPARATOR . $filename;
        $irss_helper = new ilResourceStorageMigrationHelper(new ResourceStakeholder(), $environment);
        $rid = $irss_helper->movePathToStorage($file_path, 6, null, null, false);
        if (is_null($rid)) {
            throw new \Exception('Could not store:' . $file_path);
        }
        $this->db->manipulate(
            "INSERT INTO export_files (object_id, rid, owner_id, timestamp) VALUES ("
            . $this->db->quote($obj_id, ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($rid->serialize(), ilDBConstants::T_TEXT) . ", "
            . $this->db->quote(6, ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($create_date, ilDBConstants::T_DATE) . ")"
        );
        $this->db->manipulate(
            "UPDATE export_file_info SET migrated = 1 WHERE"
            . " obj_id = " . $this->db->quote($obj_id, ilDBConstants::T_INTEGER)
            . " AND export_type = " . $this->db->quote($export_type, ilDBConstants::T_TEXT)
            . " AND filename = " . $this->db->quote($filename, ilDBConstants::T_TEXT)
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = "SELECT COUNT(*) as count FROM export_file_info where migrated = 0";
        $res = $this->db->query($query);
        $row = $res->fetchAssoc();
        if (is_null($row)) {
            return 0;
        }
        return (int) $row['count'];
    }

    protected function getExportDirectory(
        string $class_name,
        bool $is_plugin,
        string $location,
        string $component_for_type,
        int $a_obj_id,
        string $a_type = "xml",
        string $a_obj_type = "",
        string $a_entity = ""
    ): string {
        $ent = ($a_entity == "")
            ? ""
            : "_" . $a_entity;
        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }
        $new_file_structure = [
            'cat', 'exc', 'crs', 'sess',
            'file', 'grp', 'frm', 'usr',
            'catr', 'crsr', 'grpr'
        ];
        if (in_array($a_obj_type, $new_file_structure)) {
            $dir = ilFileUtils::getDataDir() . DIRECTORY_SEPARATOR;
            $dir .= 'il' . $class_name . $ent . DIRECTORY_SEPARATOR;
            $dir .= $this->createPathFromId($a_obj_id, $a_obj_type) . DIRECTORY_SEPARATOR;
            $dir .= ($a_type == 'xml' ? 'export' : 'export_' . $a_type);
            return $dir;
        }
        $exporter_class = $this->getExporterClass(
            $a_obj_type,
            $is_plugin,
            $class_name,
            $location,
            $component_for_type
        );
        $export_dir = call_user_func(
            array($exporter_class, 'lookupExportDirectory'),
            $a_obj_type,
            $a_obj_id,
            $a_type,
            $a_entity
        );
        return $export_dir;
    }

    protected function createPathFromId(int $a_container_id, string $a_name): string
    {
        $max_exponent = 3;
        $factor = 100;
        $path = [];
        $found = false;
        $num = $a_container_id;
        $path_string = '';
        for ($i = $max_exponent; $i > 0; $i--) {
            $factor = pow($factor, $i);
            if (($tmp = (int) ($num / $factor)) or $found) {
                $path[] = $tmp;
                $num = $num % $factor;
                $found = true;
            }
        }
        if (count($path)) {
            $path_string = (implode('/', $path) . '/');
        }
        return $path_string . $a_name . '_' . $a_container_id;
    }

    protected function getExporterClass(
        string $a_type,
        bool $is_plugin,
        string $class_name,
        string $location,
        string $component_for_type
    ): string {
        if ($is_plugin) {
            $classname = 'il' . $class_name . 'Exporter';
            if (include_once $location . '/class.' . $classname . '.php') {
                return $classname;
            }
        } else {
            $comp = $component_for_type;
            $componentParts = explode("/", $comp);
            $class = array_pop($componentParts);
            $class = "il" . $class . "Exporter";
            // page component plugin exporter classes are already included
            // the component is not registered by ilObjDefinition
            if (class_exists($class)) {
                return $class;
            }
            // the next line had a "@" in front of the include_once
            // I removed this because it tages ages to track down errors
            // if the include class contains parse errors.
            // Alex, 20 Jul 2012
            if (include_once "./" . $comp . "/classes/class." . $class . ".php") {
                return $class;
            }
        }
        throw new InvalidArgumentException('Invalid exporter type given');
    }
}
