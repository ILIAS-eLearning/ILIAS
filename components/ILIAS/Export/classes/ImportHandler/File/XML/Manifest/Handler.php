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

use ILIAS\Export\ImportHandler\File\XML\Handler as XMLFileHandler;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\CollectionInterface as XMLExportFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\FactoryInterface as XMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerCollectionInterface as ManifestXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as XMLFileManifestInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\FactoryInterface as XSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\HandlerInterface as ParserInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as PathFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as ImportStatusSchemaFolderInterface;
use ILIAS\Export\ImportHandler\I\Validation\HandlerInterface as ValidationInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;
use SplFileInfo;

class Handler extends XMLFileHandler implements XMLFileManifestInterface
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

    protected XSDFileInterface $manifest_xsd_handler;
    protected ParserFactoryInterface $parser_factory;
    protected ParserInterface $parser_handler;
    protected ValidationInterface $validation;
    protected PathFactoryInterface $file_path_factory;
    protected XMLFileFactoryInterface $xml_file_factory;

    public function __construct(
        FileNamespaceFactoryInterface $namespace_factory,
        ImportStatusFactoryInterface $import_status_factory,
        ValidationInterface $validation,
        ParserFactoryInterface $parser_factory,
        PathFactoryInterface $file_path_factory,
        XMLFileFactoryInterface $xml_file_factory,
        XSDFileFactoryInterface $xsd_file_factory,
        ImportStatusSchemaFolderInterface $schema_folder
    ) {
        parent::__construct($namespace_factory, $import_status_factory);
        $schema_info = $schema_folder->getLatest(
            self::XSD_TYPE,
            self::XSD_SUB_TYPE
        );
        $this->manifest_xsd_handler = $xsd_file_factory->handler()
            ->withFileInfo(is_null($schema_info) ? null : $schema_info->getFile());
        $this->file_path_factory = $file_path_factory;
        $this->xml_file_factory = $xml_file_factory;
        $this->parser_factory = $parser_factory;
        $this->validation = $validation;
    }

    /**
     * @throws ImportStatusException
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
     * @throws ImportStatusException
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

    public function validateManifestXML(): ImportStatusHandlerCollectionInterface
    {
        return $this->validation->validateXMLFile($this, $this->manifest_xsd_handler);
    }

    /**
     * @throws ImportStatusException
     */
    public function findXMLFileHandlers(): XMLExportFileCollectionInterface
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
     * @throws ImportStatusException
     */
    public function findManifestXMLFileHandlers(): ManifestXMLFileCollectionInterface
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
