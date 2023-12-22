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

use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\ilHandlerInterface as ilDataSetXMLExportFileHandlerInterface;
use ilLogger;
use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilCollectionInterface as ilFileValidationSetCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\ilHandlerInterface as ilComponentXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\File\XML\Export\ilHandler as ilXMLExportFileHandler;
use ILIAS\Export\ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusCollectionInterface;
use ILIAS\Export\ImportHandler\File\XML\ilHandler as ilXMLFileHandler;
use ILIAS\Export\ImportHandler\I\File\XML\Export\ilHandlerInterface as ilXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ilImportStatusException;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ILIAS\Export\ImportStatus\StatusType;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\Attribute\ilFactoryInterface as ilXMlFileInfoNodeAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\ilFactoryInterface as ilFileNamespaceHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilFactoryInterface as ilFileValidationSetFactoryInterface;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use SplFileInfo;
use ILIAS\Data\Version;

class ilHandler extends ilXMLExportFileHandler implements ilDataSetXMLExportFileHandlerInterface
{
    protected ilFileValidationSetCollectionInterface $sets;

    public function __construct(
        ilFileNamespaceFactoryInterface $namespace,
        ilImportStatusFactoryInterface $status,
        ilXmlSchemaFactory $schema,
        ilParserFactoryInterface $parser,
        ilXSDFileFactoryInterface $xsd_file,
        ilFilePathFactoryInterface $path,
        ilLogger $logger,
        ilXMlFileInfoNodeAttributeFactoryInterface $attribute,
        ilFileValidationSetFactoryInterface $set
    ) {
        parent::__construct($namespace, $status, $schema, $parser, $xsd_file, $path, $logger, $attribute, $set);
        $this->sets = $this->set->collection();
    }

    public function withFileInfo(SplFileInfo $file_info): ilHandler
    {
        $clone = clone $this;
        $clone->xml_file_info = $file_info;
        return $clone;
    }

    public function getValidationSets(): ilFileValidationSetCollectionInterface
    {
        return $this->sets;
    }

    public function buildValidationSets(): ilImportStatusCollectionInterface
    {
        $statuses = $this->status->collection();
        $xml = $this->withAdditionalNamespace(
            $this->namespace->handler()
                ->withNamespace(ilDataSet::DATASET_NS)
                ->withPrefix(ilDataSet::DATASET_NS_PREFIX)
        );
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
            // General structure validation set
            $structure_spl = $this->schema->getLatest('exp', 'dataset');
            $structure_xsd = is_null($structure_spl)
                ? null
                : $this->xsd_file->withFileInfo($structure_spl);
            if (!is_null($structure_xsd)) {
                $sets = $sets->withElement(
                    $this->set->handler()
                        ->withXMLFileHandler($xml)
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
            $node_info = null;
            $node_info = $this->parser->DOM()->withFileHandler($xml)
                ->getNodeInfoAt($path_to_export_node)
                ->current();
            $type_str = $node_info->getValueOfAttribute('Entity');
            $types = str_contains($type_str, '_')
                ? explode('_', $type_str)
                : [$type_str, ''];
            $version_str = $node_info->getValueOfAttribute('SchemaVersion');
            $version = new Version($version_str);
            $nodes = $this->parser->DOM()->withFileHandler($xml)
                ->getNodeInfoAt($path_to_dataset_child_nodes);

            for ($i = 0; $i < $nodes->count(); $i++) {
                $node = $nodes->toArray()[$i];
                $type_str = $node->getValueOfAttribute('Entity');
                $types = str_contains($type_str, '_')
                    ? explode('_', $type_str)
                    : [$type_str, ''];
                $xsd_schema_spl = $this->schema->getByVersionOrLatest($version, $types[0], $types[1]);
                if (is_null($xsd_schema_spl)) {
                    $statuses = $statuses->withAddedStatus($this->status->handler()
                        ->withType(StatusType::DEBUG)
                        ->withContent($this->status->content()->builder()->string()->withString(
                            'Missing schema xsd file for entity of type: ' . $type_str
                        )));
                    continue;
                }
                $xsd_handler = $this->xsd_file->withFileInfo($xsd_schema_spl);
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
                        ->withXMLFileHandler($xml)
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

    public function getPathToComponentRootNodes(): ilFilePathHandlerInterface
    {
        return $this->path->handler()
            ->withStartAtRoot(true)
            ->withNode($this->path->node()->simple()->withName('exp:Export'))
            ->withNode($this->path->node()->simple()->withName('exp:ExportItem'))
            ->withNode($this->path->node()->simple()->withName('ds:DataSet'));
    }
}
