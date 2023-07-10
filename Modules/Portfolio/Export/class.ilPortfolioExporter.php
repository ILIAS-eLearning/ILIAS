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
 * Portfolio definition
 * Only for portfolio templates!
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioExporter extends ilXmlExporter
{
    protected ilPortfolioDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilPortfolioDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $pg_ids = array();
        foreach ($a_ids as $id) {
            foreach (ilPortfolioTemplatePage::getAllPortfolioPages($id) as $p) {
                $pg_ids[] = "prtt:" . $p["id"];
            }
        }

        $deps[] =
            array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids);

        // style
        $obj_ids = (is_array($a_ids))
            ? $a_ids
            : array($a_ids);
        $deps[] = array(
            "component" => "Services/Style",
            "entity" => "object_style",
            "ids" => $obj_ids
        );

        // common object properties
        $deps[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids
        );

        return $deps;
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(
        string $a_entity
    ): array {
        return array(
                "4.4.0" => array(
                        "namespace" => "https://www.ilias.de/Modules/Portfolio/4_4",
                        "xsd_file" => "ilias_portfolio_4_4.xsd",
                        "uses_dataset" => true,
                        "min" => "4.4.0",
                        "max" => "4.9.9"),
                "5.0.0" => array(
                        "namespace" => "https://www.ilias.de/Modules/Portfolio/5_0",
                        "xsd_file" => "ilias_portfolio_5_0.xsd",
                        "uses_dataset" => true,
                        "min" => "5.0.0",
                        "max" => "")
        );
    }
}
