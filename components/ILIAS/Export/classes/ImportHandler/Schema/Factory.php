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

namespace ILIAS\Export\ImportHandler\Schema;

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as ilImportHandlerXMLFileInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as ilImportHandlerPathInterface;
use ILIAS\Export\ImportHandler\I\Schema\CollectionInterface as ilImportHandlerSchemaCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as ilImportHandlerSchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\Folder\FactoryInterface as ilImportHandlerSchemaFolderFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as ilImportHandlerSchemaInterface;
use ILIAS\Export\ImportHandler\I\Schema\Info\FactoryInterface as ilImportHandlerSchemaInfoFactoryInterface;
use ILIAS\Export\ImportHandler\Schema\Collection as ilImportHandlerSchemaCollection;
use ILIAS\Export\ImportHandler\Schema\Folder\Factory as ilImportHandlerSchemaFolderFactory;
use ILIAS\Export\ImportHandler\Schema\Handler as ilImportHandlerSchema;
use ILIAS\Export\ImportHandler\Schema\Info\Factory as ilImportHandlerSchemaInfoFactory;
use ilLogger;

class Factory implements ilImportHandlerSchemaFactoryInterface
{
    protected ilImportHandlerFactoryInterface $import_handler;
    protected ilDataFactory $data_factory;
    protected ilLogger $logger;

    public function __construct(
        ilImportHandlerFactoryInterface $import_handler,
        ilDataFactory $data_factory,
        ilLogger $logger
    ) {
        $this->import_handler = $import_handler;
        $this->data_factory = $data_factory;
        $this->logger = $logger;
    }

    public function handler(): ilImportHandlerSchemaInterface
    {
        return new ilImportHandlerSchema(
            $this->folder()->handler(),
            $this->data_factory,
            $this->import_handler->parser(),
            $this->import_handler->file()->xsd()
        );
    }

    public function collection(): ilImportHandlerSchemaCollectionInterface
    {
        return new ilImportHandlerSchemaCollection();
    }

    public function folder(): ilImportHandlerSchemaFolderFactoryInterface
    {
        return new ilImportHandlerSchemaFolderFactory(
            $this->import_handler,
            $this->logger
        );
    }

    public function info(): ilImportHandlerSchemaInfoFactoryInterface
    {
        return new ilImportHandlerSchemaInfoFactory(
            $this->logger
        );
    }

    public function collectionFrom(
        ilImportHandlerXMLFileInterface $xml_file_handler,
        ilImportHandlerPathInterface $path_to_entities
    ): ilImportHandlerSchemaCollectionInterface {
        $parser_factory = $this->import_handler->parser();
        $path_factory = $this->import_handler->path();
        $path_to_export_node = $path_factory->handler()
            ->withStartAtRoot(true)
            ->withNode($path_factory->node()->simple()->withName('exp:Export'));
        $xml_file_node_info = $parser_factory->DOM()->handler()
            ->withFileHandler($xml_file_handler)
            ->getNodeInfoAt($path_to_export_node)
            ->current();
        $nodes = $parser_factory->DOM()->handler()
            ->withFileHandler($xml_file_handler)
            ->getNodeInfoAt($path_to_entities);
        $collection = $this->import_handler->schema()->collection();
        foreach ($nodes as $node) {
            $element = $this->handler()
                ->withInformationOf($node);
            $collection = $collection
                ->withElement($element);
        }
        return $collection;
    }
}
