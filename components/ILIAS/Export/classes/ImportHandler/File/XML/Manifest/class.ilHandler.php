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

namespace ImportHandler\File\XML\Manifest;

use ilImportException;
use ilLogger;
use ImportHandler\File\ilFactory as ilFileFactory;
use ImportHandler\File\XML\ilHandler as ilXMLFileHandler;
use ImportHandler\I\File\XML\Export\ilCollectionInterface as ilXMLExportFileCollectionInterface;
use ImportHandler\I\File\XML\Manifest\ilHandlerCollectionInterface as ilManifestXMLFileHandlerCollectionInterface;
use ImportHandler\I\File\XML\Manifest\ilHandlerInterface as ilManifestHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\Parser\ilFactory as ilParserFactory;
use ImportHandler\Parser\ilHandler as ilParserHandler;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;
use ImportStatus\StatusType;
use Schema\ilXmlSchemaFactory;
use SplFileInfo;

class ilHandler extends ilXMLFileHandler implements ilManifestHandlerInterface
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

    protected ilXSDFileHandlerInterface $manifest_xsd_handler;
    protected ilImportStatusFactoryInterface $status;
    protected ilFileFactory $file;
    protected ilParserFactory $parser;
    protected ilParserHandler $parser_handler;
    protected ilXmlSchemaFactory $schema;
    protected ilLogger $logger;

    public function __construct(
        ilXmlSchemaFactory $schema,
        ilImportStatusFactoryInterface $status,
        ilFileFactory $file,
        ilParserFactory $parser,
        ilLogger $logger,
    ) {
        parent::__construct($status);
        $this->status = $status;
        $this->manifest_xsd_handler = $file->xsd()->handler()
            ->withFileInfo($schema->getLatest(
                self::XSD_TYPE,
                self::XSD_SUB_TYPE
            ));
        $this->logger = $logger;
        $this->parser = $parser;
        $this->file = $file;
        $this->schema = $schema;
    }

    /**
     * @throws ilImportStatusException
     */
    public function withFileInfo(SplFileInfo $file_info): ilHandler
    {
        $clone = clone $this;
        $clone->xml_file_info = $file_info;
        $clone->parser_handler = $clone->parser->handler()->withFileHandler($clone);
        return $clone;
    }

    /**
     * @throws ilImportStatusException
     */
    public function getExportObjectType(): ilExportObjectType
    {
        $exp_file_file_path = $this->file->path()->handler()
            ->withNode($this->file->path()->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file->path()->node()->simple()->withName(self::EXPORT_FILE_NODE_NAME));
        $exp_set_file_path = $this->file->path()->handler()
            ->withNode($this->file->path()->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file->path()->node()->simple()->withName(self::EXPORT_SET_NODE_NAME));
        $export_file_node_info = $this->parser_handler->getNodeInfoAt($exp_file_file_path);
        $export_set_node_info = $this->parser_handler->getNodeInfoAt($exp_set_file_path);
        if (
            $export_file_node_info->count() > 0 &&
            $export_set_node_info->count() > 0
        ) {
            return ilExportObjectType::MIXED;
        }
        if ($export_file_node_info->count() > 0) {
            return ilExportObjectType::EXPORT_FILE;
        }
        if ($export_set_node_info->count() > 0) {
            return ilExportObjectType::EXPORT_SET;
        }
        return ilExportObjectType::NONE;
    }

    public function validateManifestXML(): ilImportStatusHandlerCollectionInterface
    {
        return $this->file->validation()->handler()->validateXMLFile($this, $this->manifest_xsd_handler);
    }

    /**
     * @throws ilImportStatusException
     */
    public function findXMLFileHandlers(): ilXMLExportFileCollectionInterface
    {
        $type_name = ilExportObjectType::toString($this->getExportObjectType());
        $path = $this->file->path()->handler()
            ->withStartAtRoot(true)
            ->withNode($this->file->path()->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file->path()->node()->simple()->withName($type_name));
        $file_handlers = $this->file->xml()->export()->collection();
        foreach ($this->parser_handler->getNodeInfoAt($path) as $node_info) {
            $file_name = $node_info->getNodeName() === ilExportObjectType::toString(ilExportObjectType::EXPORT_SET)
                ? DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME
                : '';
            $file_handlers = $file_handlers->withElement($this->file->xml()->export()->handler()
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
    public function findManifestXMLFileHandlers(): ilManifestXMLFileHandlerCollectionInterface
    {
        $export_obj_type = $this->getExportObjectType();
        $this->logger->debug(
            "\n\n\nFinding Manifest File Handlers\nType: "
            . ilExportObjectType::toString($export_obj_type) . "\n\n"
        );
        $type_name = ilExportObjectType::toString($export_obj_type);
        $path = $this->file->path()->handler()
            ->withStartAtRoot(true)
            ->withNode($this->file->path()->node()->simple()->withName(self::MANIFEST_NODE_NAME))
            ->withNode($this->file->path()->node()->simple()->withName($type_name));
        $xml_file_infos = $this->file->xml()->manifest()->handlerCollection();
        foreach ($this->parser_handler->getNodeInfoAt($path) as $node_info) {
            $file_name = $node_info->getNodeName() === ilExportObjectType::toString(ilExportObjectType::EXPORT_SET)
                ? DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME
                : '';
            $xml_file_infos = $xml_file_infos->withElement($this->file->xml()->manifest()->handler()
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
