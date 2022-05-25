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
 * Exporter class for news
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsExporter extends ilXmlExporter
{
    private ilNewsDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilNewsDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $mob_ids = [];

        foreach ($a_ids as $id) {
            $mob_id = ilNewsItem::_lookupMobId((int) $id);
            if ($mob_id > 0) {
                $mob_ids[$mob_id] = $mob_id;
            }
        }

        return [
            [
                "component" => "Services/MediaObjects",
                "entity" => "mob",
                "ids" => $mob_ids
            ]
        ];
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return [
            "5.4.0" => [
                "namespace" => "https://www.ilias.de/Services/News/news/5_4",
                "xsd_file" => "ilias_news_5_4.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => ""
            ],
            "4.1.0" => [
                "namespace" => "https://www.ilias.de/Services/News/news/4_1",
                "xsd_file" => "ilias_news_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => ""
            ]
        ];
    }
}
