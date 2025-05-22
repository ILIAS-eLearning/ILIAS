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

use ilDataSet;
use ILIAS\Export\ImportHandler\File\XML\Handler as XMLFile;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\HandlerInterface as XMLExportFileInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ParserNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Tree\HandlerInterface as ParserNodeInfoTreeInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as PathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as SchemaFactory;
use ILIAS\Export\ImportHandler\I\Validation\Set\FactoryInterface as FileValidationSetFactoryInterface;
use ILIAS\Export\ImportHandler\Validation\Handler as FileValidationHandler;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;
use ILIAS\Export\ImportStatus\I\ilHandlerInterface as ImportStatusInterface;
use ILIAS\Export\ImportStatus\StatusType;
use ilLanguage;
use ilLogger;
use SplFileInfo;

abstract class Handler extends XMLFile implements XMLExportFileInterface
{
    protected SchemaFactory $schema;
    protected ParserFactoryInterface $parser;
    protected PathFactoryInterface $path;
    protected ParserNodeInfoAttributeFactoryInterface $attribute;
    protected FileValidationSetFactoryInterface $set;
    protected ilLogger $logger;
    protected ilLanguage $lng;

    public function __construct(
        FileNamespaceHandlerInterface $namespace,
        ImportStatusFactoryInterface $status,
        SchemaFactory $schema,
        ParserFactoryInterface $parser,
        PathFactoryInterface $path,
        ilLogger $logger,
        ParserNodeInfoAttributeFactoryInterface $attribute,
        FileValidationSetFactoryInterface $set,
        ilLanguage $lng
    ) {
        parent::__construct($namespace, $status);
        $this->schema = $schema;
        $this->parser = $parser;
        $this->logger = $logger;
        $this->path = $path;
        $this->attribute = $attribute;
        $this->set = $set;
        $this->lng = $lng;
    }

    /**
     * @throws ImportStatusException
     */
    public function withFileInfo(SplFileInfo $file_info): XMLExportFileInterface
    {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        return $clone;
    }

    public function getILIASPath(ParserNodeInfoTreeInterface $component_tree): string
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
                ->withElement($this->attribute->handler()->withValue($matches[4])->withKey('Id'))
                ->withElement($this->attribute->handler()->withValue($matches[3])->withKey('Type'))
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
        $xml = $this->withAdditionalNamespace(
            $this->namespace->handler()
                ->withNamespace(ilDataSet::DATASET_NS)
                ->withPrefix(ilDataSet::DATASET_NS_PREFIX)
        );
        try {
            $nodes = $this->parser->DOM()->handler()
                ->withFileHandler($xml)
                ->getNodeInfoAt($this->getPathToComponentRootNodes());
        } catch (ImportStatusException $e) {
            return false;
        }
        return count($nodes) > 0;
    }

    public function pathToExportNode(): PathInterface
    {
        return $this->path->handler()
            ->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'));
    }

    protected function getFailMsgNoMatchingVersionFound(
        XMLFileHandlerInterface $xml_file_handler,
        ?XSDFileHandlerInterface $xsd_file_handler,
        string $version_str
    ): ImportStatusInterface {
        $xml_str = "<br>XML-File: " . $xml_file_handler->getSubPathToDirBeginningAtPathEnd(FileValidationHandler::TMP_DIR_NAME)->getFilePath();
        $xsd_str = "<br>XSD-File: " . (is_null($xsd_file_handler) ? "null" : $xsd_file_handler->getSubPathToDirBeginningAtPathEnd(FileValidationHandler::XML_DIR_NAME)->getFilePath());
        $msg = sprintf($this->lng->txt('exp_import_validation_err_no_matching_xsd'), $version_str);
        $content = $this->status->content()->builder()->string()->withString(
            "Validation FAILED"
            . $xml_str
            . $xsd_str
            . "<br>ERROR Message: " . $msg
        );
        return $this->status->handler()
            ->withType(StatusType::FAILED)
            ->withContent($content);
    }
}
