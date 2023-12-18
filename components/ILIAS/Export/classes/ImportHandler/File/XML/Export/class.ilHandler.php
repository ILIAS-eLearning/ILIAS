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

namespace ImportHandler\File\XML\Export;

use ilLogger;
use ImportHandler\File\XML\ilHandler as ilXMLFileHandler;
use ImportHandler\I\File\XML\Export\ilHandlerInterface as ilXMLExportFileHandlerInterface;
use ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;
use ImportStatus\I\ilCollectionInterface as ilImportStatusCollectionInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ImportStatus\StatusType;
use ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilFactoryInterface as ilXMlFileInfoNodeAttributeFactoryInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use Schema\ilXmlSchemaFactory;
use ILIAS\Data\Version;
use SplFileInfo;

class ilHandler extends ilXMLFileHandler implements ilXMLExportFileHandlerInterface
{
    protected ilXmlSchemaFactory $schema;
    protected ilParserFactoryInterface $parser;
    protected ilXSDFileFactoryInterface $xsd_file;
    protected ilFilePathFactoryInterface $path;
    protected ilXMlFileInfoNodeAttributeFactoryInterface $attribute;
    protected ilLogger $logger;

    protected Version $version;
    protected string $type;
    protected string $subtype;

    public function __construct(
        ilImportStatusFactoryInterface $status,
        ilXmlSchemaFactory $schema,
        ilParserFactoryInterface $parser,
        ilXSDFileFactoryInterface $xsd_file,
        ilFilePathFactoryInterface $path,
        ilLogger $logger,
        ilXMlFileInfoNodeAttributeFactoryInterface $attribute
    ) {
        parent::__construct($status);
        $this->schema = $schema;
        $this->parser = $parser;
        $this->xsd_file = $xsd_file;
        $this->logger = $logger;
        $this->path = $path;
        $this->attribute = $attribute;
    }

    public function withFileInfo(SplFileInfo $file_info): ilHandler
    {
        $clone = clone $this;
        $clone->xml_file_info = $file_info;
        return $clone;
    }

    public function loadExportInfo(): ilImportStatusCollectionInterface
    {
        $path_to_export = $this->path->handler()
            ->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'));
        $node_info = null;
        try {
            $node_info = $this->parser->handler()
                ->withFileHandler($this)
                ->getNodeInfoAt($path_to_export)
                ->current();
        } catch (ilImportStatusException $e) {
            return $e->getStatuses();
        }
        $type_str = $node_info->getValueOfAttribute('Entity');
        $types = str_contains($type_str, '_')
            ? explode('_', $type_str)
            : [$type_str, ''];
        $version_str = $node_info->getValueOfAttribute('SchemaVersion');
        $this->type = $types[0];
        $this->subtype = $types[1];
        $this->version = new Version($version_str);
        return $this->status->collection();
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubType(): string
    {
        return $this->subtype;
    }

    /**
     * @throws ilImportStatusException
     */
    public function getXSDFileHandler(): ilXSDFileHandlerInterface|null
    {
        $latest_file_info = $this->schema->getByVersionOrLatest($this->version, $this->type, $this->subtype);
        return is_null($latest_file_info)
            ? null
            : $this->xsd_file->handler()->withFileInfo($latest_file_info);
    }

    public function getILIASPath(ilXMLFileNodeInfoTreeInterface $component_tree): string
    {
        $matches = [];
        $pattern = '/([0-9]+)__([0-9]+)__([a-z_]+)_([0-9]+)/';
        $path_part = $this->getSubPathToDirBeginningAtPathEnd('temp')->getPathPart($pattern);
        if (
            is_null($path_part) ||
            preg_match($pattern, $path_part, $matches) !== 1
        ) {
            return 'No path found';
        };
        $node = $component_tree->getFirstNodeWith(
            $this->attribute->collection()
                ->withElement($this->attribute->pair()->withValue($matches[4])->withKey('Id'))
                ->withElement($this->attribute->pair()->withValue($matches[3])->withKey('Type'))
        );
        return is_null($node)
            ? ''
            : $node->getAttributePath('Title', DIRECTORY_SEPARATOR);
    }

    public function isContainerExportXML(): bool
    {
        return $this->getSubPathToDirBeginningAtPathEnd('temp')->pathContainsFolderName('Container');
    }
}
