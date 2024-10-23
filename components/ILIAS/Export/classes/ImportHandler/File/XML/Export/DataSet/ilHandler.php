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

namespace ILIAS\Export\ImportHandler\File\XML\Export\DataSet;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\File\XML\Export\Handler as ilXMLExportFileHandler;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as ilFileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\HandlerInterface as ilDataSetXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ilXMlFileInfoNodeAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as ilImportHandlerPathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as ilImportHandlerPathInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as ilImportHandlerSchemaFactory;
use ILIAS\Export\ImportHandler\I\Validation\Set\CollectionInterface as ilFileValidationSetCollectionInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\FactoryInterface as ilFileValidationSetFactoryInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ilImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ILIAS\Export\ImportStatus\StatusType;
use ilLanguage;
use ilLogger;
use SplFileInfo;

class Handler extends ilXMLExportFileHandler implements ilDataSetXMLExportFileHandlerInterface
{
    protected ilFileValidationSetCollectionInterface $sets;

    public function __construct(
        ilFileNamespaceFactoryInterface $namespace,
        ilImportStatusFactoryInterface $status,
        ilImportHandlerSchemaFactory $schema,
        ilParserFactoryInterface $parser,
        ilImportHandlerPathFactoryInterface $path,
        ilLogger $logger,
        ilXMlFileInfoNodeAttributeFactoryInterface $attribute,
        ilFileValidationSetFactoryInterface $set,
        ilLanguage $lng
    ) {
        parent::__construct($namespace, $status, $schema, $parser, $path, $logger, $attribute, $set, $lng);
        $this->sets = $this->set->collection();
    }

    public function withFileInfo(SplFileInfo $file_info): Handler
    {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        return $clone;
    }

    public function getValidationSets(): ilFileValidationSetCollectionInterface
    {
        return $this->sets;
    }

    public function buildValidationSets(): ilImportStatusCollectionInterface
    {
        $statuses = $this->status->collection();
        try {
            $sets = $this->set->collection();
            $path_to_export_node = $this->path->handler()
                ->withStartAtRoot(true)
                ->withNode($this->path->node()->simple()->withName('exp:Export'));
            $path_to_dataset_child_nodes = $this->path->handler()
                ->withStartAtRoot(true)
                ->withNode($this->path->node()->simple()->withName('exp:Export'))
                ->withNode($this->path->node()->simple()->withName('exp:ExportItem'))
                ->withNode($this->path->node()->simple()->withName('ds:DataSet'))
                ->withNode($this->path->node()->simple()->withName('ds:Rec'));
            $export_schema_handler = $this->schema->collectionFrom($this, $path_to_export_node)
                ->current();
            $major_version_str = $export_schema_handler->getVersion()->getMajor() . ".0.0";
            $major_structure_schema_version = new Version($major_version_str);
            $structure_schema_handler = $this->schema->handler()
                ->withType('exp')
                ->withSubType('dataset')
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
                $sets = $sets->withElement(
                    $this->set->handler()
                        ->withXMLFileHandler($this)
                        ->withXSDFileHanlder($structure_xsd)
                        ->withFilePathHandler($path_to_export_node)
                );
            }
            if (is_null($structure_xsd)) {
                $statuses = $statuses->withAddedStatus($this->status->handler()
                    ->withType(StatusType::DEBUG)
                    ->withContent($this->status->content()->builder()->string()->withString(
                        'Missing schema xsd file for entity of type: exp_dataset'
                    )));
            }
            // Content validation set
            $content_schemas = $this->schema->collectionFrom($this, $path_to_dataset_child_nodes);
            for ($i = 0; $i < $content_schemas->count(); $i++) {
                $content_schema = $content_schemas->toArray()[$i];
                $content_schema = $content_schema->withVersion($export_schema_handler->getVersion());
                $xsd_handler = $content_schema->getXSDFileHandlerByVersionOrLatest();
                if (is_null($xsd_handler)) {
                    $statuses = $statuses->withAddedStatus($this->status->handler()
                        ->withType(StatusType::DEBUG)
                        ->withContent($this->status->content()->builder()->string()->withString(
                            'Missing schema xsd file for entity of type: ' . $content_schema->getTypeString()
                        )));
                    continue;
                }
                if (!$export_schema_handler->doesXSDFileWithMatchingVersionExist()) {
                    $statuses = $statuses->withAddedStatus(
                        $this->getFailMsgNoMatchingVersionFound(
                            $this,
                            $xsd_handler,
                            $export_schema_handler->getVersion()->__toString()
                        )
                    );
                    continue;
                }
                $path_to_rec = $this->path->handler()
                    ->withStartAtRoot(true)
                    ->withNode($this->path->node()->openRoundBracked())
                    ->withNode($this->path->node()->simple()->withName('exp:Export'))
                    ->withNode($this->path->node()->simple()->withName('exp:ExportItem'))
                    ->withNode($this->path->node()->simple()->withName('ds:DataSet'))
                    ->withNode($this->path->node()->simple()->withName('ds:Rec'))
                    ->withNode($this->path->node()->anyNode())
                    ->withNode($this->path->node()->closeRoundBracked())
                    ->withNode($this->path->node()->index()->withIndex($i + 1));
                $sets = $sets->withElement(
                    $this->set->handler()
                        ->withXMLFileHandler($this)
                        ->withXSDFileHanlder($xsd_handler)
                        ->withFilePathHandler($path_to_rec)
                );
            }
            $this->sets = $sets;
        } catch (ilImportStatusException $e) {
            $statuses = $statuses->getMergedCollectionWith($e->getStatuses());
        }
        return $statuses;
    }

    public function getPathToComponentRootNodes(): ilImportHandlerPathInterface
    {
        return $this->path->handler()
            ->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'))
            ->withNode($this->path->node()->simple()->withName('exp:ExportItem'))
            ->withNode($this->path->node()->simple()->withName('ds:DataSet'));
    }
}
