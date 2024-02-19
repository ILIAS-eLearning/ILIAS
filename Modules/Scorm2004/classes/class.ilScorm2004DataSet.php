<?php

declare(strict_types=1);

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
 * Class ilScorm2004DataSet
 * @author Alexander Killing <killing@leifos.de>
 */
class ilScorm2004DataSet extends ilDataSet
{
    protected array $temp_dir = array();

    /**
     * Note: this is currently used for SCORM authoring lms
     * Get supported versions
     * @return string[]
     */
    public function getSupportedVersions(): array
    {
        return array("5.1.0");
    }

    public function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "http://www.ilias.de/xml/Modules/Scorm2004/" . $a_entity;
    }

    /**
     * @return array<string, class-string<\directory>>|array<string, string>
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === "sahs") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Editable" => "integer",
                        "Dir" => "directory",
                        "File" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // sahs
        if ($a_entity === "sahs") {
            $this->data = array();

            switch ($a_version) {
                case "5.1.0":
                    foreach ($a_ids as $sahs_id) {
                        if (ilObject::_lookupType((int) $sahs_id) === "sahs") {
                            $this->data[] = array("Id" => $sahs_id,
                                "Title" => ilObject::_lookupTitle((int) $sahs_id),
                                "Description" => ilObject::_lookupDescription((int) $sahs_id),
                                "Editable" => 1
                            );
                        }
                    }
                    break;
            }
        }
    }



    public function afterXmlRecordWriting(string $a_entity, string $a_version, array $a_set): void
    {
        if ($a_entity === "sahs") {
            // delete our temp dir
            if (isset($this->temp_dir[$a_set["Id"]]) && is_dir($this->temp_dir[$a_set["Id"]])) {
                ilFileUtils::delDir($this->temp_dir[$a_set["Id"]]);
            }
        }
    }


}
