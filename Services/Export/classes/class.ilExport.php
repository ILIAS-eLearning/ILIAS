<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Export
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @defgroup ServicesExport Services/Export
* @ingroup	ServicesExport
*/
class ilExport
{
    /**
     * @var ilLogger
     */
    protected $log;

    public static $new_file_structure = array('cat','exc','crs','sess','file','grp','frm', 'usr', 'catr', 'crsr', 'grpr');
    
    // this should be part of module.xml and be parsed in the future
    public static $export_implementer = array("tst", "lm", "glo", "sahs");

    protected $configs = array();
    
    /**
     * Default constructor
     * @return
     */
    public function __construct()
    {
        $this->log = ilLoggerFactory::getLogger('exp');
    }

    /**
     * Get configuration (note that configurations are optional, null may be returned!)
     *
     * @param string $a_comp component (e.g. "Modules/Glossary")
     * @return ilExportConfig $a_comp configuration object or null
     * @throws ilExportException
     */
    public function getConfig($a_comp)
    {
        // if created, return existing config object
        if (isset($this->configs[$a_comp])) {
            return $this->configs[$a_comp];
        }

        // create instance of export config object
        $comp_arr = explode("/", $a_comp);
        $a_class = "il" . $comp_arr[1] . "ExportConfig";
        $export_config_file = "./" . $a_comp . "/classes/class." . $a_class . ".php";
        if (!is_file($export_config_file)) {
            include_once("./Services/Export/exceptions/class.ilExportException.php");
            throw new ilExportException('Component "' . $a_comp . '" does not provide ExportConfig class.');
        }
        include_once($export_config_file);
        $exp_config = new $a_class();
        $this->configs[$a_comp] = $exp_config;

        return $exp_config;
    }


    /**
    * Get a list of subitems of a repository resource, that implement
    * the export. Includes also information on last export file.
    */
    public static function _getValidExportSubItems($a_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        $valid_items = array();
        $sub_items = $tree->getSubTree($tree->getNodeData($a_ref_id));
        foreach ($sub_items as $sub_item) {
            if (in_array($sub_item["type"], self::$export_implementer)) {
                $valid_items[] = array("type" => $sub_item["type"],
                    "title" => $sub_item["title"], "ref_id" => $sub_item["child"],
                    "obj_id" => $sub_item["obj_id"],
                    "timestamp" =>
                    ilExport::_getLastExportFileDate($sub_item["obj_id"], "xml", $sub_item["type"]));
            }
        }
        return $valid_items;
    }
    
    /**
     * Get date of last export file
     *
     * @param int $a_obj_id object id
     * @param string $a_type export type ("xml", "html", ...), default "xml"
     * @param string $a_obj_type object type (optional, if not given, type is looked up)
     */
    public static function _getLastExportFileDate($a_obj_id, $a_type = "", $a_obj_type = "")
    {
        $files = ilExport::_getExportFiles($a_obj_id, $a_type, $a_obj_type);
        if (is_array($files)) {
            $files = ilUtil::sortArray($files, "timestamp", "desc");
            return $files[0]["timestamp"];
        }
        return false;
    }
    
    /**
     * Get last export file information
     *
     * @param int $a_obj_id object id
     * @param string $a_type export type ("xml", "html", ...), default "xml"
     * @param string $a_obj_type object type (optional, if not given, type is looked up)
     */
    public static function _getLastExportFileInformation($a_obj_id, $a_type = "", $a_obj_type = "")
    {
        $files = ilExport::_getExportFiles($a_obj_id, $a_type, $a_obj_type);
        if (is_array($files)) {
            $files = ilUtil::sortArray($files, "timestamp", "desc");
            return $files[0];
        }
        return false;
    }

    /**
     * Get export directory for an repository object
     *
     * @param int $a_obj_id object id
     * @param string $a_type export type ("xml", "html", ...), default "xml"
     * @param string $a_obj_type object type (optional, if not given, type is looked up)
     *
     * @return string export directory
     */
    public static function _getExportDirectory($a_obj_id, $a_type = "xml", $a_obj_type = "", $a_entity = "")
    {
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
            include_once './Services/FileSystem/classes/class.ilFileSystemStorage.php';
            $dir = ilUtil::getDataDir() . DIRECTORY_SEPARATOR;
            $dir .= 'il' . $objDefinition->getClassName($a_obj_type) . $ent . DIRECTORY_SEPARATOR;
            $dir .= ilFileSystemStorage::_createPathFromId($a_obj_id, $a_obj_type) . DIRECTORY_SEPARATOR;
            $dir .= ($a_type == 'xml' ? 'export' : 'export_' . $a_type);
            return $dir;
        }

        include_once './Services/Export/classes/class.ilImportExportFactory.php';
        $exporter_class = ilImportExportFactory::getExporterClass($a_obj_type);
        $export_dir = call_user_func(array($exporter_class,'lookupExportDirectory'), $a_obj_type, $a_obj_id, $a_type, $a_entity);

        $logger->debug('Export dir is ' . $export_dir);
        return $export_dir;
    }

    /**
     * Get Export Files for a repository object
     */
    public static function _getExportFiles($a_obj_id, $a_export_types = "", $a_obj_type = "")
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
            if (!@is_dir($dir) or
                !is_writeable($dir)) {
                continue;
            }

            // open directory
            $h_dir = dir($dir);

            // get files and save the in the array
            while ($entry = $h_dir->read()) {
                if ($entry != "." and
                    $entry != ".." and
                    substr($entry, -4) == ".zip" and
                    preg_match("/^[0-9]{10}_{2}[0-9]+_{2}(" . $a_obj_type . "_)*[0-9]+\.zip\$/", $entry)) {
                    $ts = substr($entry, 0, strpos($entry, "__"));
                    $file[$entry . $type] = array("type" => $type, "file" => $entry,
                        "size" => filesize($dir . "/" . $entry),
                        "timestamp" => $ts);
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


    /**
     * @param $a_obj_id
     * @param string $a_export_type
     * @param string $a_obj_type
     * @return bool
     */
    public static function _createExportDirectory($a_obj_id, $a_export_type = "xml", $a_obj_type = "")
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        
        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        $edir = ilExport::_getExportDirectory($a_obj_id, $a_export_type, $a_obj_type);
        ilUtil::makeDirParents($edir);
        return true;
    }

    /**
    * Generates an index.html file including links to all xml files included
    * (for container exports)
    */
    public static function _generateIndexFile($a_filename, $a_obj_id, $a_files, $a_type = "")
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("export");
        
        if ($a_type == "") {
            $a_type = ilObject::_lookupType($a_obj_id);
        }
        $a_tpl = new ilTemplate("tpl.main.html", true, true);
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $a_tpl->setVariable("LOCATION_STYLESHEET", $location_stylesheet);
        $a_tpl->getStandardTemplate();
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
        $index_content = $a_tpl->get("DEFAULT", false, false, false, true, false, false);

        $f = fopen($a_filename, "w");
        fwrite($f, $index_content);
        fclose($f);
    }
    
    ////
    //// New functions coming with 4.1 export revision
    ////
    
    /***
     *
     * - Walk through sequence
     * - Each step in sequence creates one xml file,
     *   e.g. Services/Mediapool/set_1.xml
     * - manifest.xml lists all files
     *
     * <manifest>
     * <xmlfile path="Services/Mediapool/set_1.xml"/>
     * ...
     * </manifest
     *
     *
     */

    /**
     * Export an ILIAS object (the object type must be known by objDefinition)
     *
     * @param string $a_type repository object type
     * @param int $a_id id of object or entity that shoudl be exported
     * @param string $a_target_release target release
     *
     * @return array success and info array
     */
    public function exportObject($a_type, $a_id, $a_target_release = "")
    {
        $this->log->debug("export type: $a_type, id: $a_id, target_release: " . $a_target_release);

        // if no target release specified, use latest major release number
        if ($a_target_release == "") {
            $v = explode(".", ILIAS_VERSION_NUMERIC);
            $a_target_release = $v[0] . "." . $v[1] . ".0";
            $this->log->debug("target_release set to: " . $a_target_release);
        }
        
        // manifest writer
        include_once "./Services/Xml/classes/class.ilXmlWriter.php";
        $this->manifest_writer = new ilXmlWriter();
        $this->manifest_writer->xmlHeader();
        $this->manifest_writer->xmlStartTag(
            'Manifest',
            array(
                "MainEntity" => $a_type,
                "Title" => ilObject::_lookupTitle($a_id),
                "TargetRelease" => $a_target_release,
                "InstallationId" => IL_INST_ID,
                "InstallationUrl" => ILIAS_HTTP_PATH)
        );

        // get export class
        ilExport::_createExportDirectory($a_id, "xml", $a_type);
        $export_dir = ilExport::_getExportDirectory($a_id, "xml", $a_type);
        $ts = time();
        
        // Workaround for test assessment
        $sub_dir = $ts . '__' . IL_INST_ID . '__' . $a_type . '_' . $a_id;
        $new_file = $sub_dir . '.zip';
        
        $this->export_run_dir = $export_dir . "/" . $sub_dir;
        ilUtil::makeDirParents($this->export_run_dir);
        $this->log->debug("export dir: " . $this->export_run_dir);

        $this->cnt = array();
                
        include_once './Services/Export/classes/class.ilImportExportFactory.php';
        $class = ilImportExportFactory::getExporterClass($a_type);
        $comp = ilImportExportFactory::getComponentForExport($a_type);
        
        $success = $this->processExporter($comp, $class, $a_type, $a_target_release, $a_id);

        $this->manifest_writer->xmlEndTag('Manifest');

        $this->manifest_writer->xmlDumpFile($this->export_run_dir . "/manifest.xml", false);

        // zip the file
        $this->log->debug("zip: " . $export_dir . "/" . $new_file);
        ilUtil::zip($this->export_run_dir, $export_dir . "/" . $new_file);
        ilUtil::delDir($this->export_run_dir);

        // Store info about export
        if ($success) {
            include_once './Services/Export/classes/class.ilExportFileInfo.php';
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
     *
     * @param string $a_entity entity type, e.g. "sty"
     * @param mixed $a_id entity id
     * @param string $a_target_release target release
     * @param string $a_component component that exports (e.g. "Services/Style")
     *
     * @return array success and info array
     */
    public function exportEntity(
        $a_entity,
        $a_id,
        $a_target_release,
        $a_component,
        $a_title,
        $a_export_dir,
        $a_type_for_file = ""
    ) {
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
        include_once "./Services/Xml/classes/class.ilXmlWriter.php";
        $this->manifest_writer = new ilXmlWriter();
        $this->manifest_writer->xmlHeader();
        $this->manifest_writer->xmlStartTag(
            'Manifest',
            array(
                "MainEntity" => $a_entity,
                "Title" => $a_title,
                "TargetRelease" => $a_target_release,
                "InstallationId" => IL_INST_ID,
                "InstallationUrl" => ILIAS_HTTP_PATH)
        );

        $export_dir = $a_export_dir;
        $ts = time();
        
        // determine file name and subdirectory
        $sub_dir = $ts . '__' . IL_INST_ID . '__' . $a_type_for_file . '_' . $a_id;
        $new_file = $sub_dir . '.zip';
        
        $this->export_run_dir = $export_dir . "/" . $sub_dir;
        ilUtil::makeDirParents($this->export_run_dir);

        $this->cnt = array();
        
        $success = $this->processExporter($comp, $class, $a_entity, $a_target_release, $a_id);

        $this->manifest_writer->xmlEndTag('Manifest');

        $this->manifest_writer->xmlDumpFile($this->export_run_dir . "/manifest.xml", false);

        // zip the file
        ilUtil::zip($this->export_run_dir, $export_dir . "/" . $new_file);
        ilUtil::delDir($this->export_run_dir);

        return array(
            "success" => $success,
            "file" => $new_file,
            "directory" => $export_dir
            );
    }

    /**
     * Process exporter
     *
     * @param string $a_comp e.g. "Modules/Forum"
     * @param string $a_class
     * @param string $a_entity e.g. "frm"
     * @param string $a_target_release e.g. "5.1.0"
     * @param string $a_id id of entity (e.g. object id)
     * @return bool success true/false
     * @throws ilExportException
     */
    public function processExporter($a_comp, $a_class, $a_entity, $a_target_release, $a_id)
    {
        $success = true;

        $this->log->debug("process exporter, comp: " . $a_comp . ", class: " . $a_class . ", entity: " . $a_entity .
            ", target release " . $a_target_release . ", id: " . $a_id);

        if (!is_array($a_id)) {
            if ($a_id == "") {
                return;
            }
            $a_id = array($a_id);
        }

        // get exporter object
        if (!class_exists($a_class)) {
            $export_class_file = "./" . $a_comp . "/classes/class." . $a_class . ".php";
            if (!is_file($export_class_file)) {
                include_once("./Services/Export/exceptions/class.ilExportException.php");
                throw new ilExportException('Export class file "' . $export_class_file . '" not found.');
            }
            include_once($export_class_file);
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
        ilUtil::makeDirParents($set_dir_absolute);
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
        $this->log->debug("schema version for entity: $a_entity, target release: $a_target_release");
        $this->log->debug("...is: " . $sv["schema_version"] . ", namespace: " . $sv["namespace"] .
            ", xsd file: " . $sv["xsd_file"] . ", uses_dataset: " . ((int) $sv["uses_dataset"]));

        $attribs = array("InstallationId" => IL_INST_ID,
            "InstallationUrl" => ILIAS_HTTP_PATH,
            "Entity" => $a_entity, "SchemaVersion" => $sv["schema_version"], "TargetRelease" => $a_target_release,
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
            $xml = $exp->getXmlRepresentation($a_entity, $sv["schema_version"], $id);
            $export_writer->appendXml($xml);
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
                $s["ids"]
            );
            if (!$s) {
                $success = false;
            }
        }

        $this->log->debug("returning " . ((int) $success) . " for " . $a_entity);
        return $success;
    }
}
