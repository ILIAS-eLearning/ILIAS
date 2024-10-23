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
use ILIAS\Export\ImportHandler\File\XML\Export\Handler as ilXMLExportFileHandler;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as ilFileNamespaceHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\HandlerInterface as ilComponentXMLExportFileHandlerInterface;
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

class Handler extends ilXMLExportFileHandler implements ilComponentXMLExportFileHandlerInterface
{
    protected ilFileValidationSetCollectionInterface $sets;

    public function __construct(
        ilFileNamespaceHandlerInterface $namespace,
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
            $export_schema_handler = $this->schema->collectionFrom($this, $path_to_export_node)
                ->current();
            $major_version_str = $export_schema_handler->getVersion()->getMajor() . ".0.0";
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
            $path_to_component = $this->path->handler()
                ->withStartAtRoot(true)
                ->withNode($this->path->node()->simple()->withName('exp:Export'))
                ->withNode($this->path->node()->simple()->withName('exp:ExportItem'))
                ->withNode($this->path->node()->anyNode());
            $sets = $sets->withElement(
                $this->set->handler()
                    ->withXMLFileHandler($this)
                    ->withXSDFileHanlder($component_xsd)
                    ->withFilePathHandler($path_to_component)
            );
            $this->sets = $sets;
        } catch (ilImportStatusException $e) {
            $statuses = $statuses->getMergedCollectionWith($e->getStatuses());
        }
        return $statuses;
    }

    public function getPathToComponentRootNodes(): ilImportHandlerPathInterface
    {
        return $this->path->handler()->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'))
            ->withNode($this->path->node()->simple()->withName('exp:ExportItem'));
    }
}
