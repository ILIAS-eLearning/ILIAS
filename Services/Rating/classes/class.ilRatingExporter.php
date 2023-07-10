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
 * Exporter class for rating (categories)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRatingExporter extends ilXmlExporter
{
    private ilRatingDataSet $ds;

    /**
     * Initialisation
     */
    public function init(): void
    {
        $this->ds = new ilRatingDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @inheritDoc
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Services/Rating/rating_category/4_3",
                "xsd_file" => "ilias_rating_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
