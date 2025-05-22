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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportHandler\I\Schema\CollectionInterface as SchemaCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as SchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\FactoryInterface as SchemaFolderFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as SchemaFolderInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as SchemaInterface;
use ILIAS\Export\ImportHandler\Schema\Collection as SchemaCollection;
use ILIAS\Export\ImportHandler\SchemaFolder\Factory as SchemaFolderFactory;
use ILIAS\Export\ImportHandler\Schema\Handler as Schema;
use ilLogger;

class Factory implements SchemaFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected DataFactory $data_factory;
    protected ilLogger $logger;
    protected SchemaFolderInterface $schema_folder;

    public function __construct(
        SchemaFolderInterface $schema_folder,
        ImportHandlerFactoryInterface $import_handler,
        DataFactory $data_factory,
        ilLogger $logger
    ) {
        $this->schema_folder = $schema_folder;
        $this->import_handler = $import_handler;
        $this->data_factory = $data_factory;
        $this->logger = $logger;
    }

    public function handler(): SchemaInterface
    {
        return new Schema(
            $this->schema_folder,
            $this->data_factory,
            $this->import_handler->parser(),
            $this->import_handler->file()->xsd()
        );
    }

    public function collection(): SchemaCollectionInterface
    {
        return new SchemaCollection();
    }

    public function collectionFrom(
        XMLFileInterface $xml_file_handler,
        PathInterface $path_to_entities
    ): SchemaCollectionInterface {
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
