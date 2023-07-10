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
 * Exporter class for user data
 * Note: this is currently NOT used for the classic user export/import
 * It is mainly used for export personsl user data from the personal desktop
 * (settings, profile, calendar entries)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserExporter extends ilXmlExporter
{
    private ilUserDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilUserDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array // Missing array type.
    {
        if ($a_entity == "personal_data") {
            $cal_ids = array();
            foreach ($a_ids as $user_id) {
                foreach (ilCalendarCategories::lookupPrivateCategories($user_id) as $ct) {
                    $cal_ids[] = $ct["cat_id"];
                }
            }

            return array(
                array(
                    "component" => "Services/User",
                    "entity" => "usr_profile",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/User",
                    "entity" => "usr_multi",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/User",
                    "entity" => "usr_setting",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/Notes",
                    "entity" => "user_notes",
                    "ids" => $a_ids),
                array(
                    "component" => "Services/Calendar",
                    "entity" => "calendar",
                    "ids" => $cal_ids)
                );
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity): array // Missing array type.
    {
        return array(
            "4.3.0" => array(
                "namespace" => "https://www.ilias.de/Services/User/usr/4_3",
                "xsd_file" => "ilias_usr_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "4.4.99"),
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Services/User/usr/5_1",
                "xsd_file" => "ilias_usr_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.1.99"),
            "5.2.0" => array(
                "namespace" => "https://www.ilias.de/Services/User/usr/5_2",
                "xsd_file" => "ilias_usr_5_2.xsd",
                "uses_dataset" => true,
                "min" => "5.2.0",
                "max" => "5.2.99"),
            "5.3.0" => array(
                "namespace" => "https://www.ilias.de/Services/User/usr/5_3",
                "xsd_file" => "ilias_usr_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => "")
        );
    }
}
