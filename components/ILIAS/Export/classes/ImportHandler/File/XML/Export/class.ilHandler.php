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

namespace ILIAS\Export\ImportHandler\File\XML\Export;

use ILIAS\BookingManager\getObjectSettingsCommand;
use ilLogger;
use ILIAS\Export\ImportHandler\File\XML\ilHandler as ilXMLFileHandler;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilCollectionInterface as ilFileValidationSetCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\ilHandlerInterface as ilXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ilImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ILIAS\Export\ImportStatus\StatusType;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\Attribute\ilFactoryInterface as ilXMlFileInfoNodeAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\ilFactoryInterface as ilFileNamespaceHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilFactoryInterface as ilFileValidationSetFactoryInterface;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use ILIAS\Data\Version;
use SplFileInfo;

abstract class ilHandler extends ilXMLFileHandler implements ilXMLExportFileHandlerInterface
{
    protected ilXmlSchemaFactory $schema;
    protected ilParserFactoryInterface $parser;
    protected ilXSDFileFactoryInterface $xsd_file;
    protected ilFilePathFactoryInterface $path;
    protected ilXMlFileInfoNodeAttributeFactoryInterface $attribute;
    protected ilFileValidationSetFactoryInterface $set;
    protected ilLogger $logger;

    public function __construct(
        ilFileNamespaceHandlerInterface $namespace,
        ilImportStatusFactoryInterface $status,
        ilXmlSchemaFactory $schema,
        ilParserFactoryInterface $parser,
        ilXSDFileFactoryInterface $xsd_file,
        ilFilePathFactoryInterface $path,
        ilLogger $logger,
        ilXMlFileInfoNodeAttributeFactoryInterface $attribute,
        ilFileValidationSetFactoryInterface $set
    ) {
        parent::__construct($namespace, $status);
        $this->schema = $schema;
        $this->parser = $parser;
        $this->xsd_file = $xsd_file;
        $this->logger = $logger;
        $this->path = $path;
        $this->attribute = $attribute;
        $this->set = $set;
    }

    /**
     * @throws ilImportStatusException
     */
    public function withFileInfo(SplFileInfo $file_info): ilHandler
    {
        $clone = clone $this;
        $clone->xml_file_info = $file_info;
        return $clone;
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
            : $component_tree->getAttributePath($node, 'Title', DIRECTORY_SEPARATOR);
    }

    public function isContainerExportXML(): bool
    {
        return $this->getSubPathToDirBeginningAtPathEnd('temp')->pathContainsFolderName('Container');
    }

    public function hasComponentRootNode(): bool
    {
        try {
            $nodes = $this->parser->DOM()->withFileHandler($this)->getNodeInfoAt($this->getPathToComponentRootNodes());
        } catch (ilImportStatusException $e) {
            return false;
        }
        return count($nodes) > 0;
    }
}
