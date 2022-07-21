<?php declare(strict_types=1);
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
 * Exporter class for meta data
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesMetaData
 */
class ilMetaDataExporter extends ilXmlExporter
{
    public function init() : void
    {
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $id = explode(":", $a_id);
        $mdxml = new ilMD2XML((int) $id[0], (int) $id[1], (string) $id[2]);
        $mdxml->setExportMode();
        $mdxml->startExport();

        return $mdxml->getXML();
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array<string, array<string, string>>
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
                "xsd_file" => "ilias_md_4_1.xsd",
                "min" => "4.1.0",
                "max" => ""
            )
        );
    }
}
