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

use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Util\Archive\Unzip;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ImportHandler\File\XML\Manifest\ilExportObjectType;
use ImportHandler\ilFactory as ilImportFactory;
use ImportStatus\ilFactory as ilImportStatusFactory;
use ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\StatusType;
use ImportStatus\Exception\ilException as ilImportStatusException;

/**
 * Import class
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilImport
{
    protected ilLogger $log;
    protected ilObjectDefinition $objDefinition;
    protected array $entity_types = [];
    protected ?ilXmlImporter $importer = null;
    protected string $comp = '';
    protected string $current_comp = '';
    protected string $entities = "";
    protected string $tmp_import_dir = "";
    protected ?ilImportMapping $mapping = null;
    protected array $skip_entity = array();
    protected array $configs = array();
    protected array $skip_importer = [];
    protected Archives $archives;
    protected Filesystems $filesystem;
    protected ilImportFactory $import;
    protected ilImportStatusFactory $import_status;

    public function __construct(int $a_target_id = 0)
    {
        global $DIC;
        $this->objDefinition = $DIC['objDefinition'];
        $this->mapping = new ilImportMapping();
        $this->mapping->setTargetId($a_target_id);
        $this->log = $DIC->logger()->exp();
        $this->archives = $DIC->archives();
        $this->filesystem = $DIC->filesystem();
        $this->import = new ilImportFactory();
        $this->import_status = new ilImportStatusFactory();
    }

    /**
     * Get configuration (note that configurations are optional, null may be returned!)
     */
    public function getConfig(string $a_comp): ilImportConfig
    {
        // if created, return existing config object
        if (isset($this->configs[$a_comp])) {
            return $this->configs[$a_comp];
        }
        // create instance of export config object
        $comp_arr = explode("/", $a_comp);
        $a_class = "il" . $comp_arr[1] . "ImportConfig";
        $imp_config = new $a_class();
        $this->configs[$a_comp] = $imp_config;
        return $imp_config;
    }

    public function getMapping(): ilImportMapping
    {
        return $this->mapping;
    }

    final public function setEntityTypes(array $a_val): void
    {
        $this->entity_types = $a_val;
    }

    final public function getEntityTypes(): array
    {
        return $this->entity_types;
    }

    /**
     * Add skip entity
     */
    public function addSkipEntity(string $a_component, string $a_entity, bool $skip = true): void
    {
        $this->skip_entity[$a_component][$a_entity] = $skip;
    }

    public function addSkipImporter(string $a_component, bool $skip = true): void
    {
        $this->skip_importer[$a_component] = $skip;
    }

    /**
     * Import entity
     */
    final public function importEntity(
        string $a_tmp_file,
        string $a_filename,
        string $a_entity,
        string $a_component,
        bool $a_copy_file = false
    ): int {
        return $this->importObject(null, $a_tmp_file, $a_filename, $a_entity, $a_component, $a_copy_file);
    }

    protected function unzipFile(
        string $zip_file_name,
        string $path_to_tmp_upload,
        bool $file_is_on_server
    ): ilImportStatusHandlerCollectionInterface {
        $import_status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        $tmp_dir_info = new SplFileInfo(ilFileUtils::ilTempnam());
        $this->filesystem->temp()->createDir($tmp_dir_info->getFilename());
        $target_file_path_str = $tmp_dir_info->getRealPath() . DIRECTORY_SEPARATOR . $zip_file_name;
        $target_dir_path_str = substr($target_file_path_str, 0, -4);
        // Copy/move zip to tmp out directory
        // File is not uploaded with the ilias storage system, therefore the php copy functions are used.
        if (
            (
                $file_is_on_server &&
                !copy($path_to_tmp_upload, $target_file_path_str)
            ) || (
                !$file_is_on_server &&
                !ilFileUtils::moveUploadedFile($path_to_tmp_upload, $zip_file_name, $target_file_path_str)
            )
        ) {
            return $import_status_collection->withAddedStatus(
                $this->import_status->handler()
                ->withType(StatusType::FAILED)
                ->withContent($this->import_status->content()->builder()->string()->withString(
                    'Could not move file.'
                ))
            );
        }
        /** @var Unzip $unzip **/
        $unzip = $this->archives->unzip(
            Streams::ofResource(fopen($target_file_path_str, 'rb')),
            (new UnzipOptions())
                ->withZipOutputPath($tmp_dir_info->getRealPath())
                ->withOverwrite(false)
                ->withFlat(false)
                ->withEnsureTopDirectoy(true)
        );
        return $unzip->extract()
            ? $import_status_collection->withAddedStatus(
                $this->import_status->handler()->withType(StatusType::SUCCESS)
                ->withContent($this->import_status->content()->builder()->string()->withString($target_dir_path_str))
            )
            : $import_status_collection->withAddedStatus(
                $this->import_status->handler()->withType(StatusType::FAILED)
                ->withContent($this->import_status->content()->builder()->string()->withString('Unzip failed.'))
            );
    }

    protected function validateXMLFiles(SplFileInfo $manifest_spl): ilImportStatusHandlerCollectionInterface
    {
        $export_files = $this->import->file()->xml()->export()->collection();
        $manifest_handlers = $this->import->file()->xml()->manifest()->handlerCollection();
        $statuses = $this->import_status->collection();
        // Find export xmls
        try {
            $manifest_handlers = $manifest_handlers->withElement(
                $this->import->file()->xml()->manifest()->handler()->withFileInfo($manifest_spl)
            );
            // VALIDATE 1st manifest file, can be either export-set or export-file
            $statuses = $manifest_handlers->validateElements();
            if($statuses->hasStatusType(StatusType::FAILED)) {
                return $statuses;
            }
            // If export set look for the export file manifests + VALIDATE
            if ($manifest_handlers->containsExportObjectType(ilExportObjectType::EXPORT_SET)) {
                $manifest_handlers = $manifest_handlers->findNextFiles();
                $statuses = $manifest_handlers->validateElements();
            }
            if($statuses->hasStatusType(StatusType::FAILED)) {
                return $statuses;
            }
            // If export file look for the export xmls
            if ($manifest_handlers->containsExportObjectType(ilExportObjectType::EXPORT_FILE)) {
                foreach ($manifest_handlers as $manfiest_file_handler) {
                    $export_files = $export_files->withMerged($manfiest_file_handler->findXMLFileHandlers());
                }
            }
        } catch (ilImportStatusException $e) {
            $this->checkStatuses($e->getStatuses());
        }
        // VALIDATE export xmls
        $path_to_export_item_child = $this->import->file()->path()->handler()
            ->withStartAtRoot(true)
            ->withNode($this->import->file()->path()->node()->simple()->withName('exp:Export'))
            ->withNode($this->import->file()->path()->node()->simple()->withName('exp:ExportItem'))
            ->withNode($this->import->file()->path()->node()->anyNode());
        $component_tree = $this->import->file()->xml()->node()->info()->tree();
        foreach ($export_files as $export_file) {
            if ($export_file->isContainerExportXML()) {
                $component_tree = $component_tree->withRootInFile($export_file, $path_to_export_item_child);
                break;
            }
        }
        foreach ($export_files as $export_file) {
            $found_statuses = $export_file->loadExportInfo();
            $xsd_file = $found_statuses->hasStatusType(StatusType::FAILED)
                ? null
                : $export_file->getXSDFileHandler();
            if (!$found_statuses->hasStatusType(StatusType::FAILED) && is_null($xsd_file)) {
                $found_statuses = $found_statuses->withAddedStatus($this->import_status->handler()
                    ->withType(StatusType::DEBUG)
                    ->withContent($this->import_status->content()->builder()->string()->withString(
                        'Missing schema xsd file for entity of type: '
                        . $export_file->getType()
                        . ($export_file->getSubType() === '' ? '' : '_' . $export_file->getSubType())
                    )));
            }
            if(!$found_statuses->hasStatusType(StatusType::FAILED) && !is_null($xsd_file)) {
                try {
                    $found_statuses = $this->import->file()->validation()->handler()->validateXMLAtPath(
                        $export_file,
                        $xsd_file,
                        $path_to_export_item_child
                    );
                } catch (ilImportStatusException $e) {
                    $found_statuses = $e->getStatuses();
                }
            }
            if (!$found_statuses->hasStatusType(StatusType::FAILED)) {
                continue;
            }
            $info_str = "<br> Location: " . $export_file->getILIASPath($component_tree) . "<br>";
            $found_statuses = $found_statuses->mergeContentToElements(
                $this->import_status->content()->builder()->string()->withString($info_str)
            );
            $statuses = $statuses->getMergedCollectionWith($found_statuses);
        }
        return $statuses;
    }

    protected function checkStatuses(ilImportStatusHandlerCollectionInterface $import_status_collection): void
    {
        if ($import_status_collection->hasStatusType(StatusType::FAILED)) {
            throw new ilImportException($import_status_collection
                ->withNumberingEnabled(true)
                ->toString(StatusType::FAILED));
        }
    }

    final public function importObject(
        ?object $a_new_obj,
        string $a_tmp_file,
        string $a_filename, // Verwerfen (sollte zip sein)
        string $a_type,
        string $a_comp = "",
        bool $a_copy_file = false
    ): ?int {
        // Unzip
        $status_collection = $this->unzipFile(
            $a_filename,
            $a_tmp_file,
            $a_copy_file
        );
        $this->checkStatuses($status_collection);
        $success_status = $status_collection->getCollectionOfAllByType(StatusType::SUCCESS)->current();
        $target_dir_info = new SplFileInfo($success_status->getContent()->toString());
        $delete_dir_info = new SplFileInfo($target_dir_info->getPath());
        $manifest_spl = new SplFileInfo($target_dir_info->getRealPath() . DIRECTORY_SEPARATOR . 'manifest.xml');
        // Validate manifest files
        try {
            $status_collection = $this->validateXMLFiles($manifest_spl);
            $this->checkStatuses($status_collection);
        } catch (Exception $e) {
            $this->filesystem->temp()->deleteDir($delete_dir_info->getFilename());
            throw $e;
        }
        // Import
        try {
            $this->setTemporaryImportDir($target_dir_info->getRealPath());
            $ret = $this->doImportObject($target_dir_info->getRealPath(), $a_type, $a_comp, $target_dir_info->getPath());
            $new_id = null;
            if (is_array($ret) && array_key_exists('new_id', $ret)) {
                $new_id = $ret['new_id'];
            }
        } catch (Exception $e) {
            $this->filesystem->temp()->deleteDir($delete_dir_info->getFilename());
            throw $e;
        }
        // Delete tmp files
        $this->filesystem->temp()->deleteDir($delete_dir_info->getFilename());
        return $new_id;
    }

    public function importFromDirectory(string $dir, string $a_type, string $a_comp): ?int
    {
        $ret = $this->doImportObject($dir, $a_type, $a_comp);
        if (is_array($ret)) {
            return $ret['new_id'];
        }
        return null;
    }

    /**
     * Set temporary import directory
     * @param string $a_val temporary import directory (used to unzip and read import)
     */
    protected function setTemporaryImportDir(string $a_val)
    {
        $this->tmp_import_dir = $a_val;
    }

    /**
     * Get temporary import directory
     * @return string temporary import directory (used to unzip and read import)
     */
    public function getTemporaryImportDir(): string
    {
        return $this->tmp_import_dir;
    }

    /**
     * Import repository object export file
     */
    protected function doImportObject(
        string $dir,
        string $a_type,
        string $a_component = "",
        string $a_tmpdir = ""
    ): array {
        if ($a_component == "") {
            $a_component = ilImportExportFactory::getComponentForExport($a_type);
        }
        $this->comp = $a_component;

        // get import class
        $success = true;

        // process manifest file
        if (!is_file($dir . "/manifest.xml")) {
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
            throw new ilImportObjectTypeMismatchException(
                "Object type does not match. Import file has type '" .
                $parser->getMainEntity() . "' but import being processed for '" . $a_type . "'."
            );
        }

        // process export files
        $expfiles = $parser->getExportFiles();

        $all_importers = array();
        foreach ($expfiles as $expfile) {
            $comp = $expfile["component"];

            if (isset($this->skip_importer[$comp]) && $this->skip_importer[$comp] === true) {
                continue;
            }

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
                $this->log->error('XML failed: ' . file_get_contents($dir . '/' . $expfile['path']));
                throw $e;
            }
        }

        // write import ids before(!) final processing
        $obj_map = $this->getMapping()->getMappingsOfEntity('Services/Container', 'objs');
        if (is_array($obj_map)) {
            foreach ($obj_map as $obj_id_old => $obj_id_new) {
                ilObject::_writeImportId(
                    (int) $obj_id_new,
                    "il_" . $this->mapping->getInstallId() . "_" . ilObject::_lookupType((int) $obj_id_new) . "_" . $obj_id_old
                );
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
            'importers' => $all_importers
        );
    }

    /**
     * Process item xml
     */
    public function processItemXml(
        string $a_entity,
        string $a_schema_version,
        string $a_id,
        string $a_xml,
        string $a_install_id,
        string $a_install_url
    ): void {
        // skip
        if (isset($this->skip_entity[$this->current_comp][$a_entity]) &&
            $this->skip_entity[$this->current_comp][$a_entity]) {
            return;
        }

        if ($this->objDefinition->isRBACObject($a_entity) &&
            $this->getMapping()->getMapping('Services/Container', 'imported', $a_id)) {
            $this->log->info('Ignoring referenced ' . $a_entity . ' with id ' . $a_id);
            return;
        }
        $this->importer->setInstallId($a_install_id);
        $this->importer->setInstallUrl($a_install_url);
        $this->importer->setSchemaVersion($a_schema_version);
        $this->importer->setSkipEntities($this->skip_entity);
        $this->importer->importXmlRepresentation($a_entity, $a_id, $a_xml, $this->mapping);

        // Store information about imported obj_ids in mapping to avoid double imports of references
        if ($this->objDefinition->isRBACObject($a_entity)) {
            $this->getMapping()->addMapping('Services/Container', 'imported', $a_id, '1');
        }
    }
}
