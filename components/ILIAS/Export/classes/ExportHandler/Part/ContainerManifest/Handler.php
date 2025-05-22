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

namespace ILIAS\Export\ExportHandler\Part\ContainerManifest;

use ILIAS\Export\ExportHandler\I\Info\Export\CollectionInterface as ilExportHandlerExportInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Part\ContainerManifest\HandlerInterface as ilExportHandlerComponentContainerManifestInterface;
use ilObject;
use ilXmlWriter;

class Handler implements ilExportHandlerComponentContainerManifestInterface
{
    protected ilExportHandlerExportInfoInterface $main_entity_export_info;
    protected ilExportHandlerExportInfoCollectionInterface $export_infos;

    public function getXML(bool $formatted = true): string
    {
        $container_writer = new ilXmlWriter();
        $container_writer->xmlHeader();
        $container_writer->xmlStartTag(
            'Manifest',
            array(
                "MainEntity" => $this->main_entity_export_info->getTarget()->getType(),
                "Title" => ilObject::_lookupTitle($this->main_entity_export_info->getTarget()->getObjectIds()[0]),
                "InstallationId" => IL_INST_ID,
                "InstallationUrl" => ILIAS_HTTP_PATH
            )
        );
        foreach ($this->export_infos as $export_info) {
            $container_writer->xmlElement(
                'ExportSet',
                array(
                    'Path' => 'set_' . $export_info->getSetNumber() . DIRECTORY_SEPARATOR . $export_info->getExportFolderName(),
                    'Type' => ilObject::_lookupType($export_info->getTarget()->getObjectIds()[0])
                )
            );
        }
        $container_writer->xmlEndTag('Manifest');
        return $container_writer->xmlDumpMem($formatted);
    }

    public function withMainEntityExportInfo(
        ilExportHandlerExportInfoInterface $export_info
    ): ilExportHandlerComponentContainerManifestInterface {
        $clone = clone $this;
        $clone->main_entity_export_info = $export_info;
        return $clone;
    }

    public function withExportInfos(
        ilExportHandlerExportInfoCollectionInterface $export_infos
    ): ilExportHandlerComponentContainerManifestInterface {
        $clone = clone $this;
        $clone->export_infos = $export_infos;
        return $clone;
    }
}
