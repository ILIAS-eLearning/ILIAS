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

namespace ILIAS\Export\ImportHandler\File\XML\Manifest;

use ILIAS\Export\ImportHandler\File\XML\Handler as ilImportHandlerXMLFileHandler;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as ilImportHandlerFileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\CollectionInterface as ilImportHandlerXMLExportFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\FactoryInterface as ilImportHandlerXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerCollectionInterface as ilImportHandlerManifestXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as ilImportHandlerXMLFileManifestInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\FactoryInterface as ilImportHandlerXSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as ilImportHandlerXSDFileInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ilImportHandlerParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\HandlerInterface as ilImportHandlerParserInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as ilImportHandlerPathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\Folder\HandlerInterface as ilImportStatusSchemaFolderInterface;
use ILIAS\Export\ImportHandler\I\Validation\HandlerInterface as ilImportHandlerValidationInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ilImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use SplFileInfo;

class Handler extends ilImportHandlerXMLFileHandler implements ilImportHandlerXMLFileManifestInterface
{
    protected const EXPORT_NODE_NAME = 'Export';
    protected const EXPORT_SET_NODE_NAME = 'ExportSet';
    protected const EXPORT_FILE_NODE_NAME = 'ExportFile';
    protected const ENTITY_ATTRIBUTE_NAME = 'Entity';
    protected const FILE_PATH_ATTRIBUTE_NAME = 'Path';
    protected const MANIFEST_FILE_NAME = 'manifest.xml';
    protected const MANIFEST_NODE_NAME = 'Manifest';
    protected const XSD_TYPE = 'exp';
    protected const XSD_SUB_TYPE = 'manifest';

    protected ilImportHandlerXSDFileInterface $manifest_xsd_handler;
    protected ilImportHandlerParserFactoryInterface $parser_factory;
    protected ilImportHandlerParserInterface $parser_handler;
    protected ilImportHandlerValidationInterface $validation;
    protected ilImportHandlerPathFactoryInterface $file_path_factory;
    protected ilImportHandlerXMLFileFactoryInterface $xml_file_factory;

    public function __construct(
        ilImportHandlerFileNamespaceFactoryInterface $namespace_factory,
        ilImportStatusFactoryInterface $import_status_factory,
        ilImportHandlerValidationInterface $validation,
        ilImportHandlerParserFactoryInterface $parser_factory,
        ilImportHandlerPathFactoryInterface $file_path_factory,
        ilImportHandlerXMLFileFactoryInterface $xml_file_factory,
        ilImportHandlerXSDFileFactoryInterface $xsd_file_factory,
        ilImportStatusSchemaFolderInterface $schema_folder
    ) {
        parent::__construct($namespace_factory, $import_status_factory);
        $this->manifest_xsd_handler = $xsd_file_factory->withFileInfo($schema_folder->getLatest(
            self::XSD_TYPE,
            self::XSD_SUB_TYPE
        ));
        $this->file_path_factory = $file_path_factory;
        $this->xml_file_factory = $xml_file_factory;
        $this->parser_factory = $parser_factory;
        $this->validation = $validation;
    }

    /**
     * @throws ilImportStatusException
     */
    public function withFileInfo(SplFileInfo $file_info): Handler
    {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        $clone->parser_handler = $clone->parser_factory->DOM()->handler()
            ->withFileHandler($clone);
        return $clone;
    }

    /**
     * @throws ilImportStatusException
     */
    public function getExportObjectType(): ExportObjectType
    {
        $exp_file_file_path = $this->file_path_factory->handler()
            ->withNode($this->file_path_factory->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file_path_factory->node()->simple()->withName(self::EXPORT_FILE_NODE_NAME));
        $exp_set_file_path = $this->file_path_factory->handler()
            ->withNode($this->file_path_factory->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file_path_factory->node()->simple()->withName(self::EXPORT_SET_NODE_NAME));
        $export_file_node_info = $this->parser_handler->getNodeInfoAt($exp_file_file_path);
        $export_set_node_info = $this->parser_handler->getNodeInfoAt($exp_set_file_path);
        if (
            $export_file_node_info->count() > 0 &&
            $export_set_node_info->count() > 0
        ) {
            return ExportObjectType::MIXED;
        }
        if ($export_file_node_info->count() > 0) {
            return ExportObjectType::EXPORT_FILE;
        }
        if ($export_set_node_info->count() > 0) {
            return ExportObjectType::EXPORT_SET;
        }
        return ExportObjectType::NONE;
    }

    public function validateManifestXML(): ilImportStatusHandlerCollectionInterface
    {
        return $this->validation->validateXMLFile($this, $this->manifest_xsd_handler);
    }

    /**
     * @throws ilImportStatusException
     */
    public function findXMLFileHandlers(): ilImportHandlerXMLExportFileCollectionInterface
    {
        $type_name = ExportObjectType::toString($this->getExportObjectType());
        $path = $this->file_path_factory->handler()
            ->withStartAtRoot(true)
            ->withNode($this->file_path_factory->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file_path_factory->node()->simple()->withName($type_name));
        $file_handlers = $this->xml_file_factory->export()->collection();
        foreach ($this->parser_handler->getNodeInfoAt($path) as $node_info) {
            $file_name = $node_info->getNodeName() === ExportObjectType::toString(ExportObjectType::EXPORT_SET)
                ? DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME
                : '';
            $file_handlers = $file_handlers->withElement($this->xml_file_factory->export()
                ->withFileInfo(new SplFileInfo(
                    $this->getPathToFileLocation()
                    . DIRECTORY_SEPARATOR
                    . $node_info->getValueOfAttribute(self::FILE_PATH_ATTRIBUTE_NAME)
                    . $file_name
                )));
        }
        return $file_handlers;
    }

    /**
     * @throws ilImportStatusException
     */
    public function findManifestXMLFileHandlers(): ilImportHandlerManifestXMLFileCollectionInterface
    {
        $export_obj_type = $this->getExportObjectType();
        $type_name = ExportObjectType::toString($export_obj_type);
        $path = $this->file_path_factory->handler()
            ->withStartAtRoot(true)
            ->withNode($this->file_path_factory->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file_path_factory->node()->simple()->withName($type_name));
        $xml_file_infos = $this->xml_file_factory->manifest()->collection();
        foreach ($this->parser_handler->getNodeInfoAt($path) as $node_info) {
            $file_name = $node_info->getNodeName() === ExportObjectType::toString(ExportObjectType::EXPORT_SET)
                ? DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME
                : '';
            $xml_file_infos = $xml_file_infos->withElement($this->xml_file_factory->manifest()->handler()
                ->withFileInfo(new SplFileInfo(
                    $this->getPathToFileLocation()
                    . DIRECTORY_SEPARATOR
                    . $node_info->getValueOfAttribute(self::FILE_PATH_ATTRIBUTE_NAME)
                    . $file_name
                )));
        }
        return $xml_file_infos;
    }
}
