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
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        $md_ids = [];
        foreach ($a_ids as $file_id) {
            $md_ids[] = $file_id . ":0:file";
        }

        return [
            [
                "component" => "Services/MetaData",
                "entity" => "md",
                "ids" => $md_ids,
            ],
            [
                "component" => "Services/Object",
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
        if (ilObject::_lookupType($a_id) == "file") {
            $file = new ilObjFile($a_id, false);
            $writer = new ilFileXMLWriter();
            $writer->setFile($file);
            $writer->setOmitHeader(true);
            $writer->setAttachFileContents(ilFileXMLWriter::$CONTENT_ATTACH_COPY);
            ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
            $writer->setFileTargetDirectories(
                $this->getRelativeExportDirectory(),
                $this->getAbsoluteExportDirectory()
            );
            $writer->start();
            $xml = $writer->getXml();
        }

        return $xml;
    }


    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/File/file/4_1",
                "xsd_file" => "ilias_file_4_1.xsd",
                "min" => "4.1.0",
                "max" => "",
            ),
        );
    }
}
