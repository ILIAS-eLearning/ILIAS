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

namespace ILIAS\Export\ExportHandler\Part\Manifest;

use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHanlderExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Part\Manifest\HandlerInterface as ilExportHandlerPartManifestInterface;
use ilObject;
use ilXmlWriter;

class Handler implements ilExportHandlerPartManifestInterface
{
    protected ilExportHanlderExportInfoInterface $export_info;

    public function withInfo(ilExportHanlderExportInfoInterface $export_info): ilExportHandlerPartManifestInterface
    {
        $clone = clone $this;
        $clone->export_info = $export_info;
        return $clone;
    }

    public function getXML(bool $formatted = true): string
    {
        $manifest_writer = new ilXmlWriter();
        $manifest_writer->xmlHeader();
        $manifest_writer->xmlStartTag(
            'Manifest',
            array(
                "MainEntity" => $this->export_info->getTarget()->getType(),
                "Title" => ilObject::_lookupTitle($this->export_info->getTarget()->getObjectIds()[0]),
                /* "TargetRelease" => $a_target_release, */
                "InstallationId" => IL_INST_ID,
                "InstallationUrl" => ILIAS_HTTP_PATH
            )
        );
        foreach ($this->export_info->getComponentInfos() as $component_info) {
            $component = $component_info->getTarget()->getComponent();
            $path_without_export_dir_name = substr(
                $component_info->getExportFilePathInContainer(),
                strpos($component_info->getExportFilePathInContainer(), DIRECTORY_SEPARATOR) + 1
            );
            $manifest_writer->xmlElement(
                "ExportFile",
                [
                    "Component" => $component,
                    "Path" => $path_without_export_dir_name,
                ]
            );
        }
        $manifest_writer->xmlEndTag('Manifest');
        return $manifest_writer->xmlDumpMem($formatted);
    }
}
