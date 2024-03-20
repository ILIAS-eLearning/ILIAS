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

namespace ILIAS\Export\ImportHandler\File\XML\Schema;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ILIAS\Export\ImportHandler\File\XML\Schema\ilCollection as ilXMLFileSchemaCollection;
use ILIAS\Export\ImportHandler\File\XML\Schema\ilHandler as ilXMLFileSchemaHandler;
use ILIAS\Export\ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilXMLFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Schema\ilCollectionInterface as ilXMLFileSchemaCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Schema\ilFactoryInterface as ilXMLFileSchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Schema\ilHandlerInterface as ilXMLFileSchemaHandlerInterface;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use ilLanguage;
use ilLogger;

class ilFactory implements ilXMLFileSchemaFactoryInterface
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilXmlSchemaFactory $schema_factory;

    public function __construct(
        ilLogger $logger,
        ilLanguage $lng,
        ilXmlSchemaFactory $schema_factory
    ) {
        $this->logger = $logger;
        $this->lng = $lng;
        $this->schema_factory = $schema_factory;
    }

    public function handler(): ilXMLFileSchemaHandlerInterface
    {
        return new ilXMLFileSchemaHandler(
            new ilFilePathFactory($this->logger),
            $this->schema_factory,
            new ilParserFactory($this->logger),
            new ilXSDFileFactory()
        );
    }

    public function handlersFromXMLFileHandlerAtPath(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXMLFilePathHandlerInterface $path_to_entities
    ): ilXMLFileSchemaCollectionInterface {
        $parser = new ilParserFactory($this->logger);
        $path = new ilFilePathFactory($this->logger);
        $xml_file_node_info = $parser->DOM()->withFileHandler($xml_file_handler)
            ->getNodeInfoAt($this->getPathToExportNode($path))
            ->current();
        $nodes = $parser->DOM()->withFileHandler($xml_file_handler)->getNodeInfoAt($path_to_entities);
        $collection = new ilXMLFileSchemaCollection();
        foreach ($nodes as $node) {
            $collection = $collection->withElement($this->initSchemaFileFromXMLNodeInfoHandler($node));
        }
        return $collection;
    }

    protected function initSchemaFileFromXMLNodeInfoHandler(
        ilXMLFileNodeInfoHandlerInterface $xml_file_node_info
    ): ilXMLFileSchemaHandler {
        $types = $this->getTypesArray($xml_file_node_info);
        $version_str = $this->getVersionString($xml_file_node_info);
        if ($version_str === '') {
            return $this->handler()
                ->withType($types[0])
                ->withSubType($types[1]);
        }
        return $this->handler()
            ->withType($types[0])
            ->withSubType($types[1])
            ->withVersion(new Version($version_str));
    }

    /**
     * @return string[]
     */
    protected function getTypesArray(ilXMLFileNodeInfoHandlerInterface $xml_file_node_info): array
    {
        $type_str = $xml_file_node_info->getValueOfAttribute('Entity');
        return str_contains($type_str, '_')
            ? explode('_', $type_str)
            : [$type_str, ''];
    }

    protected function getVersionString(ilXMLFileNodeInfoHandlerInterface $xml_file_node_info): string
    {
        return $xml_file_node_info->hasAttribute('SchemaVersion')
            ? $xml_file_node_info->getValueOfAttribute('SchemaVersion')
            : '';
    }

    protected function getPathToExportNode(ilFilePathFactoryInterface $path): ilFilePathHandlerInterface
    {
        return $path->handler()
            ->withStartAtRoot(true)
            ->withNode($path->node()->simple()->withName('exp:Export'));
    }
}
