<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Export
 * @author    Alex Killing <alex.killing@gmx.de>
 */
class ilExport
{
    /**
     * @todo currently used in ilLeanringModuleExporter
     */
    public string $export_run_dir = '';

    protected ilLogger $log;

    private static array $new_file_structure = array('cat',
                                               'exc',
                                               'crs',
                                               'sess',
                                               'file',
                                               'grp',
                                               'frm',
                                               'usr',
                                               'catr',
                                               'crsr',
                                               'grpr'
    );
    // @todo this should be part of module.xml and be parsed in the future
    private static array $export_implementer = array("tst", "lm", "glo", "sahs");
    private array $configs = [];

    private array $cnt = [];
    private ?ilXmlWriter $manifest_writer = null;

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC->logger()->exp();
    }

    /**
     * Get configuration (note that configurations are optional, null may be returned!)
     * @param string $a_comp component (e.g. "Modules/Glossary")
     * @return ilExportConfig $a_comp configuration object
     * @throws ilExportException thronw if no config exists
     */
    public function getConfig(string $a_comp) : ilExportConfig
    {
        // if created, return existing config object
        if (isset($this->configs[$a_comp])) {
            return $this->configs[$a_comp];
        }

        // create instance of export config object
        $comp_arr = explode("/", $a_comp);
        $a_class = "il" . $comp_arr[1] . "ExportConfig";
        $exp_config = new $a_class();
        $this->configs[$a_comp] = $exp_config;
        return $exp_config;
    }

    /**
     * Get a list of subitems of a repository resource, that implement
     * the export. Includes also information on last export file.
     * @return array<int, array<string, int|string>>
     */
    public static function _getValidExportSubItems(int $a_ref_id) : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $valid_items = array();
        $sub_items = $tree->getSubTree($tree->getNodeData($a_ref_id));
        foreach ($sub_items as $sub_item) {
            if (in_array($sub_item["type"], self::$export_implementer)) {
                $valid_items[] = [
                    "type" => (string) $sub_item["type"],
                    "title" => (string) $sub_item["title"],
                    "ref_id" => (int) $sub_item["child"],
                    "obj_id" => (int) $sub_item["obj_id"],
                    "timestamp" => ilExport::_getLastExportFileDate($sub_item["obj_id"], "xml", $sub_item["type"])
                ];
            }
        }
        return $valid_items;
    }

    /**
     * Get date of last export file
     * @param int    $a_obj_id   object id
     * @param string $a_type     export type ("xml", "html", ...), default "xml"
     * @param string $a_obj_type object type (optional, if not given, type is looked up)
     */
    public static function _getLastExportFileDate(int $a_obj_id, string $a_type = "", string $a_obj_type = "") : int
    {
        $files = ilExport::_getExportFiles($a_obj_id, $a_type, $a_obj_type);
        if (is_array($files)) {
            $files = ilArrayUtil::sortArray($files, "timestamp", "desc");
            return (int) $files[0]["timestamp"];
        }
        return 0;
    }

    /**
     * Get last export file information
     * @param int    $a_obj_id   object id
     * @param string $a_type     export type ("xml", "html", ...), default "xml"
     * @param string $a_obj_type object type (optional, if not given, type is looked up)
     * @return ?array
     */
    public static function _getLastExportFileInformation(
        int $a_obj_id,
        string $a_type = "",
        string $a_obj_type = ""
    ) : ?array {
        $files = ilExport::_getExportFiles($a_obj_id, $a_type, $a_obj_type);
        if (is_array($files)) {
            $files = ilArrayUtil::sortArray($files, "timestamp", "desc");
            return $files[0];
        }
        return null;
    }

    /**
     * Get export directory for an repository object
     * @param int    $a_obj_id   object id
     * @param string $a_type     export type ("xml", "html", ...), default "xml"
     * @param string $a_obj_type object type (optional, if not given, type is looked up)
     * @param string $a_entity
     * @return string export directory
     */
    public static function _getExportDirectory(
        int $a_obj_id,
        string $a_type = "xml",
        string $a_obj_type = "",
        string $a_entity = ""
    ) : string {
        global $DIC;

        $logger = $DIC->logger()->exp();
        $objDefinition = $DIC['objDefinition'];

        $ent = ($a_entity == "")
            ? ""
            : "_" . $a_entity;

        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        if (in_array($a_obj_type, self::$new_file_structure)) {
            $dir = ilFileUtils::getDataDir() . DIRECTORY_SEPARATOR;
            $dir .= 'il' . $objDefinition->getClassName($a_obj_type) . $ent . DIRECTORY_SEPARATOR;
            $dir .= self::createPathFromId($a_obj_id, $a_obj_type) . DIRECTORY_SEPARATOR;
            $dir .= ($a_type == 'xml' ? 'export' : 'export_' . $a_type);
            return $dir;
        }

        $exporter_class = ilImportExportFactory::getExporterClass($a_obj_type);
        $export_dir = call_user_func(
            array($exporter_class, 'lookupExportDirectory'),
            $a_obj_type,
            $a_obj_id,
            $a_type,
            $a_entity
        );

        $logger->debug('Export dir is ' . $export_dir);
        return $export_dir;
    }

    /**
     * @param int          $a_obj_id
     * @param string|array $a_export_types
     * @param string       $a_obj_type
     * @return array
     */
    public static function _getExportFiles(int $a_obj_id, $a_export_types = "", string $a_obj_type = "") : array
    {
        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        if ($a_export_types == "") {
            $a_export_types = array("xml");
        }
        if (!is_array($a_export_types)) {
            $a_export_types = array($a_export_types);
        }

        // initialize array
        $file = array();

        $types = $a_export_types;

        foreach ($types as $type) {
            $dir = ilExport::_getExportDirectory($a_obj_id, $type, $a_obj_type);

            // quit if import dir not available
            if (!is_dir($dir) || !is_writable($dir)) {
                continue;
            }

            // open directory
            $h_dir = dir($dir);

            // get files and save the in the array
            while ($entry = $h_dir->read()) {
                if ($entry !== "." &&
                    $entry !== ".." &&
                    substr($entry, -4) === ".zip" &&
                    preg_match("/^[0-9]{10}_{2}[0-9]+_{2}(" . $a_obj_type . "_)*[0-9]+\.zip\$/", $entry)) {
                    $ts = substr($entry, 0, strpos($entry, "__"));
                    $file[$entry . $type] = [
                        "type" => (string) $type,
                        "file" => (string) $entry,
                        "size" => (int) filesize($dir . "/" . $entry),
                        "timestamp" => (int) $ts
                    ];
                }
            }

            // close import directory
            $h_dir->close();
        }

        // sort files
        ksort($file);
        reset($file);
        return $file;
    }

    public static function _createExportDirectory(
        int $a_obj_id,
        string $a_export_type = "xml",
        string $a_obj_type = ""
    ) : bool {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        $edir = ilExport::_getExportDirectory($a_obj_id, $a_export_type, $a_obj_type);
        ilFileUtils::makeDirParents($edir);
        return true;
    }

    /**
     * Generates an index.html file including links to all xml files included
     * (for container exports)
     */
    public static function _generateIndexFile(
        string $a_filename,
        int $a_obj_id,
        array $a_files,
        string $a_type = ""
    ) : void {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("export");

        if ($a_type == "") {
            $a_type = ilObject::_lookupType($a_obj_id);
        }
        $a_tpl = new ilGlobalTemplate("tpl.main.html", true, true);
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $a_tpl->setVariable("LOCATION_STYLESHEET", $location_stylesheet);
        $a_tpl->loadStandardTemplate();
        $a_tpl->setTitle(ilObject::_lookupTitle($a_obj_id));
        $a_tpl->setDescription($lng->txt("export_export_date") . ": " .
            date('Y-m-d H:i:s', time()) . " (" . date_default_timezone_get() . ")");
        $f_tpl = new ilTemplate("tpl.export_list.html", true, true, "Services/Export");
        foreach ($a_files as $file) {
            $f_tpl->setCurrentBlock("file_row");
            $f_tpl->setVariable("TITLE", $file["title"]);
            $f_tpl->setVariable("TYPE", $lng->txt("obj_" . $file["type"]));
            $f_tpl->setVariable("FILE", $file["file"]);
            $f_tpl->parseCurrentBlock();
        }
        $a_tpl->setContent($f_tpl->get());
        $index_content = $a_tpl->getSpecial("DEFAULT", false, false, false, true, false, false);

        $f = fopen($a_filename, "w");
        fwrite($f, $index_content);
        fclose($f);
    }

    /***
     * - Walk through sequence
     * - Each step in sequence creates one xml file,
     *   e.g. Services/Mediapool/set_1.xml
     * - manifest.xml lists all files
     * <manifest>
     * <xmlfile path="Services/Mediapool/set_1.xml"/>
     * ...
     * </manifest
     */

    /**
     * Export an ILIAS object (the object type must be known by objDefinition)
     * @param string $a_type           repository object type
     * @param int    $a_id             id of object or entity that shoudl be exported
     * @param string $a_target_release target release
     * @return array success and info array
     */
    public function exportObject(
        string $a_type,
        int $a_id,
        string $a_target_release = ""
    ) : array {
        $this->log->debug("export type: $a_type, id: $a_id, target_release: " . $a_target_release);

        // if no target release specified, use latest major release number
        if ($a_target_release == "") {
            $v = explode(".", ILIAS_VERSION_NUMERIC);
            $a_target_release = $v[0] . "." . $v[1] . ".0";
            $this->log->debug("target_release set to: " . $a_target_release);
        }

        // manifest writer
        $this->manifest_writer = new ilXmlWriter();
        $this->manifest_writer->xmlHeader();
        $this->manifest_writer->xmlStartTag(
            'Manifest',
            array(
                "MainEntity" => $a_type,
                "Title" => ilObject::_lookupTitle($a_id),
                "TargetRelease" => $a_target_release,
                "InstallationId" => IL_INST_ID,
                "InstallationUrl" => ILIAS_HTTP_PATH
            )
        );

        // get export class
        ilExport::_createExportDirectory($a_id, "xml", $a_type);
        $export_dir = ilExport::_getExportDirectory($a_id, "xml", $a_type);
        $ts = time();

        // Workaround for test assessment
        $sub_dir = $ts . '__' . IL_INST_ID . '__' . $a_type . '_' . $a_id;
        $new_file = $sub_dir . '.zip';

        $this->export_run_dir = $export_dir . "/" . $sub_dir;
        ilFileUtils::makeDirParents($this->export_run_dir);
        $this->log->debug("export dir: " . $this->export_run_dir);

        $this->cnt = array();

        $class = ilImportExportFactory::getExporterClass($a_type);
        $comp = ilImportExportFactory::getComponentForExport($a_type);

        $success = $this->processExporter($comp, $class, $a_type, $a_target_release, [$a_id]);

        $this->manifest_writer->xmlEndTag('Manifest');
        $this->manifest_writer->xmlDumpFile($this->export_run_dir . "/manifest.xml", false);

        // zip the file
        $this->log->debug("zip: " . $export_dir . "/" . $new_file);
        ilFileUtils::zip($this->export_run_dir, $export_dir . "/" . $new_file);
        ilFileUtils::delDir($this->export_run_dir);

        // Store info about export
        if ($success) {
            $exp = new ilExportFileInfo($a_id);
            $exp->setVersion($a_target_release);
            $exp->setCreationDate(new ilDateTime($ts, IL_CAL_UNIX));
            $exp->setExportType('xml');
            $exp->setFilename($new_file);
            $exp->create();
        }

        return array(
            "success" => $success,
            "file" => $new_file,
            "directory" => $export_dir
        );
    }

    /**
     * Export an ILIAS entity
     * @param string $a_entity         entity type, e.g. "sty"
     * @param mixed  $a_id             entity id
     * @param string $a_target_release target release
     * @param string $a_component      component that exports (e.g. "Services/Style")
     * @return array success and info array
     */
    public function exportEntity(
        string $a_entity,
        string $a_id,
        string $a_target_release,
        string $a_component,
        string $a_title,
        string $a_export_dir,
        string $a_type_for_file = ""
    ) : array {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $tpl = $DIC['tpl'];

        // if no target release specified, use latest major release number
        if ($a_target_release == "") {
            $v = explode(".", ILIAS_VERSION_NUMERIC);
            $a_target_release = $v[0] . "." . $v[1] . ".0";
        }

        if ($a_type_for_file == "") {
            $a_type_for_file = $a_entity;
        }

        $comp = $a_component;
        $c = explode("/", $comp);
        $class = "il" . $c[1] . "Exporter";

        // manifest writer
        $this->manifest_writer = new ilXmlWriter();
        $this->manifest_writer->xmlHeader();
        $this->manifest_writer->xmlStartTag(
            'Manifest',
            array(
                "MainEntity" => $a_entity,
                "Title" => $a_title,
                "TargetRelease" => $a_target_release,
                "InstallationId" => IL_INST_ID,
                "InstallationUrl" => ILIAS_HTTP_PATH
            )
        );

        $export_dir = $a_export_dir;
        $ts = time();

        // determine file name and subdirectory
        $sub_dir = $ts . '__' . IL_INST_ID . '__' . $a_type_for_file . '_' . $a_id;
        $new_file = $sub_dir . '.zip';

        $this->export_run_dir = $export_dir . "/" . $sub_dir;
        ilFileUtils::makeDirParents($this->export_run_dir);

        $this->cnt = array();
        $success = $this->processExporter($comp, $class, $a_entity, $a_target_release, [$a_id]);
        $this->manifest_writer->xmlEndTag('Manifest');
        $this->manifest_writer->xmlDumpFile($this->export_run_dir . "/manifest.xml", false);

        // zip the file
        ilFileUtils::zip($this->export_run_dir, $export_dir . "/" . $new_file);
        ilFileUtils::delDir($this->export_run_dir);

        return array(
            "success" => $success,
            "file" => $new_file,
            "directory" => $export_dir
        );
    }

    /**
     * Process exporter
     * @param string $a_comp           e.g. "Modules/Forum"
     * @param string $a_class
     * @param string $a_entity         e.g. "frm"
     * @param string $a_target_release e.g. "5.1.0"
     * @param string $a_id             id of entity (e.g. object id)
     * @return bool success true/false
     * @throws ilExportException
     */
    public function processExporter(
        string $a_comp,
        string $a_class,
        string $a_entity,
        string $a_target_release,
        array $a_id = null
    ) : bool {
        $success = true;
        $this->log->debug("process exporter, comp: " . $a_comp . ", class: " . $a_class . ", entity: " . $a_entity .
            ", target release " . $a_target_release . ", id: " . print_r($a_id, true));

        if (!is_array($a_id)) {
            if ($a_id == "") {
                return true;
            }
            $a_id = array($a_id);
        }

        // get exporter object
        if (!class_exists($a_class)) {
            $export_class_file = "./" . $a_comp . "/classes/class." . $a_class . ".php";
            if (!is_file($export_class_file)) {
                throw new ilExportException('Export class file "' . $export_class_file . '" not found.');
            }
        }

        $exp = new $a_class();
        $exp->setExport($this);
        if (!isset($this->cnt[$a_comp])) {
            $this->cnt[$a_comp] = 1;
        } else {
            $this->cnt[$a_comp]++;
        }
        $set_dir_relative = $a_comp . "/set_" . $this->cnt[$a_comp];
        $set_dir_absolute = $this->export_run_dir . "/" . $set_dir_relative;
        ilFileUtils::makeDirParents($set_dir_absolute);
        $this->log->debug("dir: " . $set_dir_absolute);

        $this->log->debug("init exporter");
        $exp->init();

        // process head dependencies
        $this->log->debug("process head dependencies for " . $a_entity);
        $sequence = $exp->getXmlExportHeadDependencies($a_entity, $a_target_release, $a_id);
        foreach ($sequence as $s) {
            $comp = explode("/", $s["component"]);
            $exp_class = "il" . $comp[1] . "Exporter";
            $s = $this->processExporter(
                $s["component"],
                $exp_class,
                $s["entity"],
                $a_target_release,
                $s["ids"]
            );
            if (!$s) {
                $success = false;
            }
        }

        // write export.xml file
        $export_writer = new ilXmlWriter();
        $export_writer->xmlHeader();

        $sv = $exp->determineSchemaVersion($a_entity, $a_target_release);
        $sv["uses_dataset"] ??= false;
        $this->log->debug("schema version for entity: $a_entity, target release: $a_target_release");
        $this->log->debug("...is: " . $sv["schema_version"] . ", namespace: " . $sv["namespace"] .
            ", xsd file: " . $sv["xsd_file"] . ", uses_dataset: " . ((int) $sv["uses_dataset"]));

        $attribs = array("InstallationId" => IL_INST_ID,
                         "InstallationUrl" => ILIAS_HTTP_PATH,
                         "Entity" => $a_entity,
                         "SchemaVersion" => $sv["schema_version"],
                         "TargetRelease" => $a_target_release,
                         "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
                         "xmlns:exp" => "http://www.ilias.de/Services/Export/exp/4_1",
                         "xsi:schemaLocation" => "http://www.ilias.de/Services/Export/exp/4_1 " . ILIAS_HTTP_PATH . "/xml/ilias_export_4_1.xsd"
        );
        if ($sv["namespace"] != "" && $sv["xsd_file"] != "") {
            $attribs["xsi:schemaLocation"] .= " " . $sv["namespace"] . " " .
                ILIAS_HTTP_PATH . "/xml/" . $sv["xsd_file"];
            $attribs["xmlns"] = $sv["namespace"];
        }
        if ($sv["uses_dataset"]) {
            $attribs["xsi:schemaLocation"] .= " " .
                "http://www.ilias.de/Services/DataSet/ds/4_3 " . ILIAS_HTTP_PATH . "/xml/ilias_ds_4_3.xsd";
            $attribs["xmlns:ds"] = "http://www.ilias.de/Services/DataSet/ds/4_3";
        }
        $export_writer->xmlStartTag('exp:Export', $attribs);

        $dir_cnt = 1;
        foreach ($a_id as $id) {
            $exp->setExportDirectories(
                $set_dir_relative . "/expDir_" . $dir_cnt,
                $set_dir_absolute . "/expDir_" . $dir_cnt
            );
            $export_writer->xmlStartTag('exp:ExportItem', array("Id" => $id));
            //$xml = $exp->getXmlRepresentation($a_entity, $a_target_release, $id);
            $xml = $exp->getXmlRepresentation($a_entity, $sv["schema_version"], (string) $id);
            $export_writer->appendXML($xml);
            $export_writer->xmlEndTag('exp:ExportItem');
            $dir_cnt++;
        }

        $export_writer->xmlEndTag('exp:Export');
        $export_writer->xmlDumpFile($set_dir_absolute . "/export.xml", false);

        $this->manifest_writer->xmlElement(
            "ExportFile",
            array("Component" => $a_comp, "Path" => $set_dir_relative . "/export.xml")
        );

        // process tail dependencies
        $this->log->debug("process tail dependencies of " . $a_entity);
        $sequence = $exp->getXmlExportTailDependencies($a_entity, $a_target_release, $a_id);
        foreach ($sequence as $s) {
            $comp = explode("/", $s["component"]);
            $exp_class = "il" . $comp[1] . "Exporter";
            $s = $this->processExporter(
                $s["component"],
                $exp_class,
                $s["entity"],
                $a_target_release,
                (array) $s["ids"]
            );
            if (!$s) {
                $success = false;
            }
        }

        $this->log->debug("returning " . ((int) $success) . " for " . $a_entity);
        return $success;
    }

    protected static function createPathFromId(int $a_container_id, string $a_name) : string
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
}
