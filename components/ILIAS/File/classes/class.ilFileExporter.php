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

/**
 * Exporter class for files
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesFile
 */
class ilFileExporter extends ilXmlExporter
{
    /**
     * Initialisation
     */
    public function init(): void
    {
    }

    /**
     * Get tail dependencies
     * @param string        entity
     * @param string        target release
     * @param array        ids
     * @return        array        array of array with keys "component", entity", "ids"
     */
    #[\Override]
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        $md_ids = [];
        foreach ($a_ids as $file_id) {
            $md_ids[] = $file_id . ":0:file";
        }

        return [
            [
                "component" => "components/ILIAS/MetaData",
                "entity" => "md",
                "ids" => $md_ids,
            ],
            [
                "component" => "components/ILIAS/ILIASObject",
                "entity" => "common",
                "ids" => $a_ids
            ]
        ];
    }

    /**
     * Get xml representation
     * @param string        entity
     * @param string        target release
     * @param string        id
     * @return    string        xml string
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $xml = '';
        if (ilObject::_lookupType((int) $a_id) === 'file') {
            $file = new ilObjFile((int) $a_id, false);
            $writer = new ilFileXMLWriter();
            $writer->setFile($file);
            $writer->setOmitHeader(true);
            $writer->setAttachFileContents(ilFileXMLWriter::$CONTENT_ATTACH_COPY);
            $this->prepareExportDirectories($writer);
            $writer->start();
            $xml = $writer->getXml();
        }
        return $xml;
    }

    protected function prepareExportDirectories(
        ilFileXMLWriter $writer
    ): void {
        $path = str_replace('\\', '/', $this->exp->getExportDirInContainer());
        $segments = explode('/', $path);
        array_shift($segments);
        $target_dir_relative = implode('/', $segments) . '/expDir_1';
        $target_dir_absolute = rtrim($this->getAbsoluteExportDirectory(), '/') . '/' . $target_dir_relative;
        ilFileUtils::makeDirParents($target_dir_absolute);
        $writer->setFileTargetDirectories(
            $target_dir_relative,
            $target_dir_absolute
        );
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "4.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/File/file/4_1",
                "xsd_file" => "ilias_file_4_1.xsd",
                "min" => "4.1.0",
                "max" => ""
            ]
        ];
    }
}
