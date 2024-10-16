<?php

namespace ILIAS\Export\ExportHandler\Part\Component;

use ilExport;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\HandlerInterface as ilExportHanlderExportComponentInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHanlderExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\HandlerInterface as ilExportHandlerPartComponentInterface;
use ilXmlWriter;

class Handler implements ilExportHandlerPartComponentInterface
{
    protected ilExportHanlderExportInfoInterface $export_info;
    protected ilExportHanlderExportComponentInfoInterface $component_info;
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function withExportInfo(
        ilExportHanlderExportInfoInterface $export_info
    ): ilExportHandlerPartComponentInterface {
        $clone = clone $this;
        $clone->export_info = $export_info;
        return $clone;
    }

    public function withComponentInfo(
        ilExportHanlderExportComponentInfoInterface $component_info
    ): ilExportHandlerPartComponentInterface {
        $clone = clone $this;
        $clone->component_info = $component_info;
        return $clone;
    }

    public function getXML(bool $formatted = true): string
    {
        $attribs = array("InstallationId" => $this->export_info->getInstallationId(),
            "InstallationUrl" => $this->export_info->getHTTPPath(),
            "Entity" => $this->component_info->getTarget()->getComponent(),
            "SchemaVersion" => $this->component_info->getSchemaVersion(),
            /* "TargetRelease" => $a_target_release, */
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns:exp" => "http://www.ilias.de/Services/Export/exp/4_1",
            "xsi:schemaLocation" => $this->component_info->getXSDSchemaLocation()
        );
        if ($this->component_info->usesCustomNamespace()) {
            $attribs["xmlns"] = $this->component_info->getNamespace();
        }
        if ($this->component_info->usesDataset()) {
            $attribs["xmlns:ds"] = $this->component_info->getDatasetNamespace();
        }
        $xml_writer = new ilXmlWriter();
        $xml_writer->xmlHeader();
        $xml_writer->xmlStartTag('exp:Export', $attribs);
        foreach ($this->component_info->getTarget()->getObjectIds() as $id) {
            $xml_writer->xmlStartTag('exp:ExportItem', array("Id" => $id));
            $writer = $this->export_handler->consumer()->handler()->exportWriter($this->export_info->getCurrentElement());
            $export = new ilExport();
            $export->setExportDirInContainer($this->component_info->getComponentExportDirPathInContainer());
            $export->setExportWriter($writer);
            $export->export_run_dir = $this->export_info->getLegacyExportRunDir();
            $export->setExportDirectories(
                $this->export_info->getExportFolderName(),
                $this->export_info->getLegacyExportRunDir()
            );
            $comp_exporter = $this->component_info->getComponentExporter($export);
            $xml = $comp_exporter->getXmlRepresentation(
                $this->component_info->getTarget()->getType(),
                $this->component_info->getSchemaVersion(),
                (string) $id
            );
            $xml_writer->appendXML($xml);
            $xml_writer->xmlEndTag('exp:ExportItem');
        }
        $xml_writer->xmlEndTag('exp:Export');
        return $xml_writer->xmlDumpMem($formatted);
    }
}
