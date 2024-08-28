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

namespace ILIAS\Export\ExportHandler\Info\Export\Component;

use ilExport;
use ilExportException;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\CollectionInterface as ilExportHandlerExportComponentInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\HandlerInterface as ilExportHandlerExportComponentInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ilExportHandlerTargetInterface;
use ilXmlExporter;

class Handler implements ilExportHandlerExportComponentInfoInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilExportHandlerTargetInterface $export_target;
    protected array $sv;
    protected string $path_in_container;
    protected string $component_export_dir_path_in_container;
    protected string $exporter_class_name;

    public function __construct(ilExportHandlerFactoryInterface $export_handler)
    {
        $this->export_handler = $export_handler;
        $this->sv = [];
    }

    protected function init(): void
    {
        $component = $this->getTarget()->getComponent() === "components/ILIAS/Object" ? "components/ILIAS/ILIASObject" : $this->getTarget()->getComponent();
        $this->exporter_class_name = $this->getTarget()->getClassname() === "ilObjectExporter" ? "ilILIASObjectExporter" : $this->getTarget()->getClassname();
        if (!class_exists($this->exporter_class_name)) {
            $export_class_file = "./" . $component . "/classes/class." . $this->exporter_class_name . ".php";
            if (!is_file($export_class_file)) {
                throw new ilExportException('Export class file "' . $export_class_file . '" not found.');
            }
        }
        $this->sv = $this->getMinimalComponentExporter()->determineSchemaVersion($component, $this->getTarget()->getTargetRelease());
        $this->sv["uses_dataset"] ??= false;
        $this->sv['xsd_file'] ??= '';
    }

    public function withExportTarget(ilExportHandlerTargetInterface $export_target): ilExportHandlerExportComponentInfoInterface
    {
        $clone = clone $this;
        $clone->export_target = $export_target;
        $clone->init();
        return $clone;
    }

    public function withExportFilePathInContainer(
        string $path_in_container
    ): ilExportHandlerExportComponentInfoInterface {
        $clone = clone $this;
        $clone->path_in_container = $path_in_container;
        return $clone;
    }

    public function withComponentExportDirPathInContainer(
        string $component_export_dir_path_in_container
    ): ilExportHandlerExportComponentInfoInterface {
        $clone = clone $this;
        $clone->component_export_dir_path_in_container = $component_export_dir_path_in_container;
        return $clone;
    }

    public function getTarget(): ilExportHandlerTargetInterface
    {
        return $this->export_target;
    }

    public function getExportFilePathInContainer(): string
    {
        return $this->path_in_container;
    }

    public function getComponentExportDirPathInContainer(): string
    {
        return $this->component_export_dir_path_in_container;
    }

    public function getXSDSchemaLocation(): string
    {
        $schema_location = "http://www.ilias.de/Services/Export/exp/4_1 " . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/ilias_export_4_1.xsd";
        if ($this->usesCustomNamespace()) {
            $schema_location .= " " . $this->sv["namespace"] . " " . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/" . $this->sv["xsd_file"];
        }
        if ($this->usesDataset()) {
            $schema_location .= " " . "http://www.ilias.de/Services/DataSet/ds/4_3 " . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/ilias_ds_4_3.xsd";
        }
        return $schema_location;
    }

    public function getComponentExporter(
        ilExport $il_export
    ): ilXmlExporter {
        /** @var ilXmlExporter $exporter */
        $exporter = new ($this->exporter_class_name)();
        $exporter->setExport($il_export);
        $exporter->init();
        return $exporter;
    }

    protected function getComponentInfos(array $sequence): ilExportHandlerExportComponentInfoCollectionInterface
    {
        $component_infos = $this->export_handler->info()->export()->component()->collection();
        foreach ($sequence as $s) {
            $comp = explode("/", $s["component"]);
            $component = str_replace("_", "", $comp[2]);
            $exp_class = "il" . $component . "Exporter";
            $component_infos = $component_infos->withComponent((new Handler($this->export_handler))->withExportTarget(
                $this->export_handler->target()->handler()
                    ->withClassname($exp_class)
                    ->withComponent($s["component"])
                    ->withType($s["entity"])
                    ->withTargetRelease($this->getTarget()->getTargetRelease())
                    ->withObjectIds((array) $s["ids"])
            ));
        }
        return $component_infos;
    }

    public function getHeadComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface
    {
        return $this->getComponentInfos($this->getMinimalComponentExporter()->getXmlExportHeadDependencies(
            $this->getTarget()->getType(),
            $this->getTarget()->getTargetRelease(),
            $this->getTarget()->getObjectIds()
        ));
    }

    public function getSchemaVersion(): string
    {
        return $this->sv["schema_version"] ?? "";
    }

    public function getTailComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface
    {
        return $this->getComponentInfos($this->getMinimalComponentExporter()->getXmlExportTailDependencies(
            $this->getTarget()->getType(),
            $this->getTarget()->getTargetRelease(),
            $this->getTarget()->getObjectIds()
        ));
    }

    public function getNamespace(): string
    {
        return $this->sv["namespace"];
    }

    public function getDatasetNamespace(): string
    {
        return "http://www.ilias.de/Services/DataSet/ds/4_3";
    }

    public function usesDataset(): bool
    {
        return $this->sv["uses_dataset"];
    }

    public function usesCustomNamespace(): bool
    {
        return ($this->sv["namespace"] ?? "") !== "" && ($this->sv["xsd_file"] ?? "") !== "";
    }

    protected function getMinimalComponentExporter(): ilXmlExporter
    {
        $exporter = new ($this->exporter_class_name)();
        $exporter->setExport(new ilExport());
        $exporter->init();
        return $exporter;
    }
}
