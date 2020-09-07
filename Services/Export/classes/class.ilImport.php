<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilImport
{
    /**
     * @var ilLogger
     */
    protected $log;

    protected $install_id = "";
    protected $install_url = "";
    protected $entities = "";
    protected $tmp_import_dir = "";

    protected $mapping = null;
    protected $skip_entity = array();
    protected $configs = array();

    /**
     * Constructor
     *
     * @param int id of parent container
     * @return
     */
    public function __construct($a_target_id = 0)
    {
        include_once("./Services/Export/classes/class.ilImportMapping.php");
        $this->mapping = new ilImportMapping();
        $this->mapping->setTargetId($a_target_id);
        $this->log = ilLoggerFactory::getLogger('exp');
    }

    /**
     * Get configuration (note that configurations are optional, null may be returned!)
     *
     * @param string $a_comp component (e.g. "Modules/Glossary")
     * @return ilImportConfig $a_comp configuration object or null
     * @throws ilImportException
     */
    public function getConfig($a_comp)
    {
        // if created, return existing config object
        if (isset($this->configs[$a_comp])) {
            return $this->configs[$a_comp];
        }

        // create instance of export config object
        $comp_arr = explode("/", $a_comp);
        $a_class = "il" . $comp_arr[1] . "ImportConfig";
        $import_config_file = "./" . $a_comp . "/classes/class." . $a_class . ".php";
        if (!is_file($import_config_file)) {
            include_once("./Services/Export/exceptions/class.ilImportException.php");
            throw new ilImportException('Component "' . $a_comp . '" does not provide ImportConfig class.');
        }
        include_once($import_config_file);
        $imp_config = new $a_class();
        $this->configs[$a_comp] = $imp_config;

        return $imp_config;
    }

    /**
     * Get mapping object
     * @return ilImportMapping ilImportMapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Set entity types
     *
     * @param	array	entity types
     */
    final public function setEntityTypes($a_val)
    {
        $this->entity_types = $a_val;
    }
    
    /**
     * Get entity types
     *
     * @return	array	entity types
     */
    final public function getEntityTypes()
    {
        return $this->entity_types;
    }
    
    /**
     * Add skip entity
     *
     * @param string $a_val component
     * @param string $a_val entity
     */
    public function addSkipEntity($a_component, $a_entity, $skip = true)
    {
        $this->skip_entity[$a_component][$a_entity] = $skip;
    }
    
    /**
     * Set currrent dataset
     *
     * @param	object	currrent dataset
     */
    public function setCurrentDataset($a_val)
    {
        $this->current_dataset = $a_val;
    }
    
    /**
     *
     * Get currrent dataset
     *
     * @return	object	currrent dataset
     */
    public function getCurrentDataset()
    {
        return $this->current_dataset;
    }
    
    /**
     * After entity types are parsed
     */
    public function afterEntityTypes()
    {
        $this->getCurrentDataset()->setImport($this);
    }

    /**
     * After entity types are parsed
     *
     * @param
     */
    public function importRecord($a_entity, $a_types, $a_record)
    {
        $this->getCurrentDataset()->importRecord($a_entity, $a_types, $a_record);
    }
    
    
    /**
     * Import entity
     */
    final public function importEntity(
        $a_tmp_file,
        $a_filename,
        $a_entity,
        $a_component,
        $a_copy_file = false
    ) {
        $this->importObject(null, $a_tmp_file, $a_filename, $a_entity, $a_component, $a_copy_file);
    }
    
    
    /**
     * Import repository object export file
     *
     * @param	string		absolute filename of temporary upload file
     */
    final public function importObject(
        $a_new_obj,
        $a_tmp_file,
        $a_filename,
        $a_type,
        $a_comp = "",
        $a_copy_file = false
    ) {

        // create temporary directory
        $tmpdir = ilUtil::ilTempnam();
        ilUtil::makeDir($tmpdir);
        if ($a_copy_file) {
            copy($a_tmp_file, $tmpdir . "/" . $a_filename);
        } else {
            ilUtil::moveUploadedFile($a_tmp_file, $a_filename, $tmpdir . "/" . $a_filename);
        }

        $this->log->debug("unzip: " . $tmpdir . "/" . $a_filename);

        ilUtil::unzip($tmpdir . "/" . $a_filename);
        $dir = $tmpdir . "/" . substr($a_filename, 0, strlen($a_filename) - 4);

        $this->setTemporaryImportDir($dir);

        $this->log->debug("dir: " . $dir);

        $ret = $this->doImportObject($dir, $a_type, $a_comp, $tmpdir);
        $new_id = null;
        if (is_array($ret)) {
            $new_id = $ret['new_id'];
        }
        
        // delete temporary directory
        ilUtil::delDir($tmpdir);
        return $new_id;
    }

    /**
     * Import from directory
     *
     * @param
     * @return
     */
    public function importFromDirectory($dir, $a_type, $a_comp)
    {
        $ret = $this->doImportObject($dir, $a_type, $a_comp);
        
        if (is_array($ret)) {
            return $ret['new_id'];
        }
        return null;
    }


    /**
     * Set temporary import directory
     *
     * @param string $a_val temporary import directory (used to unzip and read import)
     */
    protected function setTemporaryImportDir($a_val)
    {
        $this->tmp_import_dir = $a_val;
    }

    /**
     * Get temporary import directory
     *
     * @return string temporary import directory (used to unzip and read import)
     */
    public function getTemporaryImportDir()
    {
        return $this->tmp_import_dir;
    }
    
    /**
     * Import repository object export file
     *
     * @param	string		absolute filename of temporary upload file
     */
    protected function doImportObject($dir, $a_type, $a_component = "", $a_tmpdir = "")
    {
        if ($a_component == "") {
            include_once("./Services/Export/classes/class.ilImportExportFactory.php");
            $a_component = ilImportExportFactory::getComponentForExport($a_type);
        }
        $this->comp = $a_component;

        // get import class
        $success = true;
        
        // process manifest file
        include_once("./Services/Export/classes/class.ilManifestParser.php");
        if (!is_file($dir . "/manifest.xml")) {
            include_once("./Services/Export/exceptions/class.ilManifestFileNotFoundImportException.php");
            $mess = (DEVMODE)
                ? 'Manifest file not found: "' . $dir . "/manifest.xml" . '".'
                : 'Manifest file not found: "manifest.xml."';
            $e = new ilManifestFileNotFoundImportException($mess);
            $e->setManifestDir($dir);
            $e->setTmpDir($a_tmpdir);
            throw $e;
        }
        $parser = new ilManifestParser($dir . "/manifest.xml");
        $this->mapping->setInstallUrl($parser->getInstallUrl());
        $this->mapping->setInstallId($parser->getInstallId());

        // check for correct type
        if ($parser->getMainEntity() != $a_type) {
            include_once("./Services/Export/exceptions/class.ilImportObjectTypeMismatchException.php");
            $e = new ilImportObjectTypeMismatchException("Object type does not match. Import file has type '" . $parser->getMainEntity() . "' but import being processed for '" . $a_type . "'.");
            throw $e;
        }

        // process export files
        $expfiles = $parser->getExportFiles();
        
        include_once("./Services/Export/classes/class.ilExportFileParser.php");
        include_once("./Services/Export/classes/class.ilImportExportFactory.php");
        $all_importers = array();
        foreach ($expfiles as $expfile) {
            $comp = $expfile["component"];
            $class = ilImportExportFactory::getImporterClass($comp);

            // log a warning for inactive page component plugins, but continue import
            // page content will be imported, but not its additional data
            // (other plugins throw an exception in ilImportExportFactory)
            if ($class == '') {
                $this->log->warning("no class found for component: $comp");
                continue;
            }

            $this->log->debug("create new class = $class");

            $this->importer = new $class();
            $this->importer->setImport($this);
            $all_importers[] = $this->importer;
            $this->importer->setImportDirectory($dir);
            $this->importer->init();
            $this->current_comp = $comp;
            try {
                $this->log->debug("Process file: " . $dir . "/" . $expfile["path"]);
                $parser = new ilExportFileParser($dir . "/" . $expfile["path"], $this, "processItemXml");
            } catch (Exception $e) {
                $this->log->error("Import failed: " . $e->getMessage());
                throw $e;
            }
        }

        // write import ids before(!) final processing
        $obj_map = $this->getMapping()->getMappingsOfEntity('Services/Container', 'objs');
        if (is_array($obj_map)) {
            foreach ($obj_map as $obj_id_old => $obj_id_new) {
                ilObject::_writeImportId($obj_id_new, "il_" . $this->mapping->getInstallId() . "_" . ilObject::_lookupType($obj_id_new) . "_" . $obj_id_old);
            }
        }

        // final processing
        foreach ($all_importers as $imp) {
            $this->log->debug("Call finalProcessing for: " . get_class($imp));
            $imp->finalProcessing($this->mapping);
        }

        // we should only get on mapping here
        $top_mapping = $this->mapping->getMappingsOfEntity($this->comp, $a_type);
        $new_id = (int) current($top_mapping);
        return array(
            'new_id' => $new_id,
            'importers' => (array) $all_importers
        );
    }

    /**
     * Process item xml
     *
     * @global ilObjectDefinition $objDefinition
     */
    public function processItemXml($a_entity, $a_schema_version, $a_id, $a_xml, $a_install_id, $a_install_url)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        
        // skip
        if ($this->skip_entity[$this->current_comp][$a_entity]) {
            return;
        }

        if ($objDefinition->isRBACObject($a_entity) &&
            $this->getMapping()->getMapping('Services/Container', 'imported', $a_id)) {
            $this->log->info('Ignoring referenced ' . $a_entity . ' with id ' . $a_id);
            return;
        }
        $this->importer->setInstallId($a_install_id);
        $this->importer->setInstallUrl($a_install_url);
        $this->importer->setSchemaVersion($a_schema_version);
        $this->importer->setSkipEntities($this->skip_entity);
        $new_id = $this->importer->importXmlRepresentation($a_entity, $a_id, $a_xml, $this->mapping);

        // Store information about imported obj_ids in mapping to avoid double imports of references
        if ($objDefinition->isRBACObject($a_entity)) {
            $this->getMapping()->addMapping('Services/Container', 'imported', $a_id, 1);
        }

        // @TODO new id is not always set
        if ($new_id && $new_id !== true) {
            $this->mapping->addMapping($this->comp, $a_entity, $a_id, $new_id);
        }
    }
}
