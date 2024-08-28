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

namespace ILIAS\Export\ExportHandler\Manager;

use ilAccessHandler;
use ilFileUtils;
use ILIAS\components\ResourceStorage\Container\Wrapper\ZipReader;
use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\HandlerInterface as ilExportHandlerContainerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionBuilderInterface as ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Manager\HandlerInterface as ilExportHandlerManagerInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ilExportHandlerTargetInterface;
use ILIAS\Export\ExportHandler\Info\Export\handler as ilExportHandlerExportInfo;
use ILIAS\Filesystem\Stream\Streams;
use ilImportExportFactory;
use ilObject;
use ilObjectDefinition;
use ilObjFileAccess;
use ilTree;

class Handler implements ilExportHandlerManagerInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilObjectDefinition $obj_definition,
        ilTree $tree,
        ilAccessHandler $access
    ) {
        $this->export_handler = $export_handler;
        $this->obj_definition = $obj_definition;
        $this->tree = $tree;
        $this->access = $access;
    }

    protected function getExportTarget(
        ObjectId $object_id
    ): ilExportHandlerTargetInterface {
        $obj_id = $object_id->toInt();
        $type = ilObject::_lookupType($obj_id);
        $class = ilImportExportFactory::getExporterClass($type);
        $comp = ilImportExportFactory::getComponentForExport($type);
        $v = explode(".", ILIAS_VERSION_NUMERIC);
        $target_release = $v[0] . "." . $v[1] . ".0";
        return $this->export_handler->target()->handler()
            ->withTargetRelease($target_release)
            ->withType($type)
            ->withObjectIds([$obj_id])
            ->withClassname($class)
            ->withComponent($comp);
    }

    protected function writeToElement(
        string $path_in_container,
        ilExportHandlerExportInfo $export_info,
        ilExportHandlerRepositoryElementInterface $element
    ): void {
        $export_info = $export_info->withCurrentElement($element);
        $manifest = $this->export_handler->part()->manifest()->handler()
            ->withInfo($export_info);
        $element->getIRSS()->write(
            Streams::ofString($manifest->getXML()),
            $path_in_container . DIRECTORY_SEPARATOR . $export_info->getExportFolderName() . DIRECTORY_SEPARATOR . "manifest.xml"
        );
        foreach ($export_info->getComponentInfos() as $component_info) {
            $component = $this->export_handler->part()->component()->handler()
                ->withExportInfo($export_info)
                ->withComponentInfo($component_info);
            $element->getIRSS()->write(
                Streams::ofString($component->getXML()),
                $path_in_container . DIRECTORY_SEPARATOR . $component_info->getExportFilePathInContainer()
            );
        }
    }

    public function createContainerExport(
        int $user_id,
        ilExportHandlerContainerExportInfoInterface $container_export_info
    ): ilExportHandlerRepositoryElementInterface {
        $main_export_info = $container_export_info->getMainEntityExportInfo();
        $main_element = $this->createExport($user_id, $main_export_info, "set_" . $main_export_info->getSetNumber());
        $repository = $this->export_handler->repository();
        foreach ($container_export_info->getExportInfos() as $export_info) {
            $stream = null;
            # Test, TestQuestionPool special case (Test does not return a xml export)
            $special_case = in_array($export_info->getTarget()->getType(), ["tst", "qpl"]);
            if ($special_case) {
                $this->createExport($user_id, $export_info, "");
                $stream = Streams::ofResource(fopen($export_info->getLegacyExportRunDir() . ".zip", 'r'));
            }
            if (!$special_case) {
                $keys = $repository->key()->collection()
                    ->withElement($repository->key()->handler()->withObjectId($export_info->getTargetObjectId()));
                $element = $export_info->getReuseExport()
                    ? $this->export_handler->repository()->handler()->getElements($keys)->newest()
                    : $this->createExport($user_id, $export_info, "");
                $element = $element->getIRSS()->isContainerExport()
                    ? $this->createExport($user_id, $export_info, "")
                    : $element;
                $stream = $element->getIRSSInfo()->getStream();
            }
            $zip_reader = new ZipReader($stream);
            $zip_structure = $zip_reader->getStructure();
            foreach ($zip_structure as $path_inside_zip => $item) {
                if ($item['is_dir']) {
                    continue;
                }
                $stream = $zip_reader->getItem($path_inside_zip, $zip_structure)[0];
                $main_element->getIRSS()->write($stream, "set_" . $export_info->getSetNumber() . DIRECTORY_SEPARATOR . $path_inside_zip);
            }
        }
        $container = $this->export_handler->part()->container()->handler()
            ->withExportInfos($container_export_info->getExportInfos()->withElementAtHead($main_export_info))
            ->withMainEntityExportInfo($main_export_info);
        $main_element->getIRSS()->write(Streams::ofString($container->getXML()), "manifest.xml");
        return $main_element;
    }

    public function createExport(
        int $user_id,
        ilExportHandlerExportInfoInterface $export_info,
        string $path_in_container
    ): ilExportHandlerRepositoryElementInterface {
        # make legacy export run dir
        # tmp solution, remove later if no longer needed
        ilFileUtils::makeDirParents($export_info->getLegacyExportRunDir());

        $stakeholder = $this->export_handler->repository()->stakeholder()->handler()->withOwnerId($user_id);
        $object_id = new ObjectId($export_info->getTarget()->getObjectIds()[0]);
        $element = $this->export_handler->repository()->handler()->createElement(
            $object_id,
            $export_info,
            $stakeholder
        );
        $this->writeToElement($path_in_container, $export_info, $element);

        # write files from legacy export run dir to irss
        # tmp solution, remove later if no longer needed
        $writer = $this->export_handler->consumer()->handler()->exportWriter($element);
        $writer->writeDirectoryRecursive(
            $export_info->getLegacyExportRunDir(),
            $export_info->getExportFolderName()
        );

        # delete legacy export run dir
        # tmp solution, remove if no longer needed
        ilFileUtils::delDir($export_info->getLegacyExportRunDir());

        # Test special case
        # Remove export if the component is Test, TestQuestionPool
        if (in_array($export_info->getTarget()->getType(), ["tst", "qpl"])) {
            $keys = $this->export_handler->repository()->key()->collection()
                ->withElement($element->getKey());
            $this->export_handler->repository()->handler()->deleteElements(
                $keys,
                $stakeholder
            );
        }
        return $element;
    }

    public function getExportInfo(
        ObjectId $object_id,
        int $time_stamp
    ): ilExportHandlerExportInfoInterface {
        return $this->export_handler->info()->export()->handler()
            ->withTarget($this->getExportTarget($object_id), $time_stamp);
    }

    public function getContainerExportInfo(
        ObjectId $main_entity_object_id,
        ilExportHandlerContainerExportInfoObjectIdCollectionInterface $object_ids
    ): ilExportHandlerContainerExportInfoInterface {
        return $this->export_handler->info()->export()->container()->handler()
            ->withMainExportEntity($main_entity_object_id)
            ->withObjectIds($object_ids)
            ->withTimestamp(time());
    }

    public function getObjectIdCollectioBuilder(): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface
    {
        return $this->export_handler->info()->export()->container()->objectId()->collectionBuilder();
    }

    public function getObjectIdCollectionBuilderFrom(
        ReferenceId $container_reference_id,
        bool $public_access = false
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface {
        $id_collection_builder = $this->export_handler->info()->export()->container()->objectId()->collectionBuilder();
        $tree_nodes = $this->tree->getSubTree($this->tree->getNodeData($container_reference_id->toInt()));
        foreach ($tree_nodes as $node) {
            if (
                $node['type'] == 'rolf' or
                !$this->obj_definition->allowExport($node['type']) or
                ($node['type'] == "file" && ilObjFileAccess::_isFileHidden($node['title'])) or
                !$this->access->checkAccess('write', '', (int) $node['ref_id'])
            ) {
                continue;
            }
            $reference_id = new ReferenceId((int) $node['ref_id']);
            $object_id = $reference_id->toObjectId();
            $keys = $this->export_handler->repository()->key()->collection()->withElement(
                $this->export_handler->repository()->key()->handler()->withObjectId($object_id)
            );
            $elements = $this->export_handler->repository()->handler()->getElements($keys);
            $create_new_export = (
                ($public_access and !$this->export_handler->publicAccess()->handler()->hasPublicAccessFile($object_id)) or
                !$public_access and $elements->count() == 0
            );
            $id_collection_builder = $id_collection_builder->addObjectId($object_id, $create_new_export);
        }
        return $id_collection_builder;
    }
}
