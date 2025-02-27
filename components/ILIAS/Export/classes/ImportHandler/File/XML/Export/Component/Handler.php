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

namespace ILIAS\Export\ImportHandler\File\XML\Export\Component;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\File\XML\Export\Handler as XMLExportFile;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\HandlerInterface as XMLExportComponentFileInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ParserNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as PathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as SchemaFactory;
use ILIAS\Export\ImportHandler\I\Validation\Set\CollectionInterface as FileValidationSetCollectionInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\FactoryInterface as FileValidationSetFactoryInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ImportStatusCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;
use ILIAS\Export\ImportStatus\StatusType;
use ilLanguage;
use ilLogger;
use SplFileInfo;

class Handler extends XMLExportFile implements XMLExportComponentFileInterface
{
    protected FileValidationSetCollectionInterface $sets;

    public function __construct(
        FileNamespaceInterface $namespace,
        ImportStatusFactoryInterface $status,
        SchemaFactory $schema,
        ParserFactoryInterface $parser,
        PathFactoryInterface $path,
        ilLogger $logger,
        ParserNodeInfoAttributeFactoryInterface $attribute,
        FileValidationSetFactoryInterface $set,
        ilLanguage $lng
    ) {
        parent::__construct($namespace, $status, $schema, $parser, $path, $logger, $attribute, $set, $lng);
        $this->sets = $this->set->collection();
    }

    public function withFileInfo(SplFileInfo $file_info): XMLExportComponentFileInterface
    {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        return $clone;
    }

    public function getValidationSets(): FileValidationSetCollectionInterface
    {
        return $this->sets;
    }

    public function buildValidationSets(): ImportStatusCollectionInterface
    {
        $statuses = $this->status->collection();
        try {
            $sets = $this->set->collection();
            $export_schema_handler = $this->schema->collectionFrom($this, $this->pathToExportNode())
                ->current();
            $major_version_str = $export_schema_handler->getMajorVersionString();
            $major_structure_schema_version = new Version($major_version_str);
            $structure_schema_handler = $this->schema->handler()
                ->withType('exp')
                ->withSubType('comp')
                ->withVersion($major_structure_schema_version);
            $structure_xsd = $structure_schema_handler->getXSDFileHandlerByVersionOrLatest();
            if (!$structure_schema_handler->doesXSDFileWithMatchingVersionExist()) {
                $statuses = $statuses->withAddedStatus(
                    $this->getFailMsgNoMatchingVersionFound(
                        $this,
                        $structure_xsd,
                        $structure_schema_handler->getVersion()->__toString()
                    )
                );
                return $statuses;
            }
            if (!is_null($structure_xsd)) {
                // uncomment as soon as all export files use the exp:Export/exp:ExportItem/Component structure
                /*$sets = $sets->withElement(
                    $this->set->handler()
                        ->withXMLFileHandler($this)
                        ->withXSDFileHanlder($structure_xsd)
                        ->withFilePathHandler($path_to_export_node)
                );*/
            }
            if (is_null($structure_xsd)) {
                $statuses = $statuses->withAddedStatus($this->status->handler()
                    ->withType(StatusType::DEBUG)
                    ->withContent($this->status->content()->builder()->string()->withString(
                        'Missing schema xsd file for entity of type: exp_comp'
                    )));
            }
            $component_xsd = $export_schema_handler->getXSDFileHandlerByVersionOrLatest();
            if (is_null($component_xsd)) {
                $statuses = $statuses->withAddedStatus($this->status->handler()
                    ->withType(StatusType::DEBUG)
                    ->withContent($this->status->content()->builder()->string()->withString(
                        'Missing schema xsd file for entity of type: ' . $export_schema_handler->getTypeString()
                    )));
                return $statuses;
            }
            if (!$export_schema_handler->doesXSDFileWithMatchingVersionExist()) {
                $statuses = $statuses->withAddedStatus(
                    $this->getFailMsgNoMatchingVersionFound(
                        $this,
                        $component_xsd,
                        $export_schema_handler->getVersion()->__toString()
                    )
                );
            }
            $sets = $sets->withElement(
                $this->set->handler()
                    ->withXMLFileHandler($this)
                    ->withXSDFileHanlder($component_xsd)
                    ->withFilePathHandler($this->pathToExportItems())
            );
            $this->sets = $sets;
        } catch (ImportStatusException $e) {
            $statuses = $statuses->getMergedCollectionWith($e->getStatuses());
        }
        return $statuses;
    }

    public function getPathToComponentRootNodes(): PathInterface
    {
        return $this->path->handler()->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'))
            ->withNode($this->path->node()->simple()->withName('exp:ExportItem'));
    }

    protected function pathToExportItems(): PathInterface
    {
        return $this->path->handler()
            ->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'))
            ->withNode($this->path->node()->simple()->withName('exp:ExportItem'))
            ->withNode($this->path->node()->anyNode());
    }
}
