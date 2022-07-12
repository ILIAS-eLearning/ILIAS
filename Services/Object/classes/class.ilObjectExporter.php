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
 * Exporter class for object related data (please note that title and description
 * are usually included in the specific object exporter classes, this class
 * takes care of additional general object related data (e.g. translations)
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilObjectExporter extends ilXmlExporter
{
    private ilObjectDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilObjectDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Get tail dependencies
     * @return array array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies(string $entity, string $target_release, array $ids) : array
    {
        return array();
    }

    public function getXmlRepresentation(string $entity, string $schema_version, string $id) : string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($entity, $schema_version, [$id], "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $entity) : array
    {
        return [
            "5.4.0" => [
                "namespace" => "http://www.ilias.de/Services/Object/obj/5_4",
                "xsd_file" => "ilias_obj_5_4.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => ""
            ],
            "5.1.0" => [
                "namespace" => "http://www.ilias.de/Services/Object/obj/5_1",
                "xsd_file" => "ilias_obj_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.3.99"
            ],
            "4.4.0" => [
                "namespace" => "http://www.ilias.de/Services/Object/obj/4_4",
                "xsd_file" => "ilias_obj_4_4.xsd",
                "uses_dataset" => true,
                "min" => "4.4.0",
                "max" => "5.0.99"
            ]
        ];
    }
}
