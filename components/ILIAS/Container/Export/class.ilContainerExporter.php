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

use ILIAS\Container\Sorting\Export\DataSet as SortingDataSet;

/**
 * container structure export
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerExporter extends ilXmlExporter
{
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    protected SortingDataSet $sorting_data_set;

    public function __construct()
    {
        global $DIC;

        $this->content_style_domain = $DIC->contentStyle()->domain();
        $this->sorting_data_set = $DIC->container()->internal()->domain()->sorting()->dataSet();
    }

    public function init(): void
    {
        $this->sorting_data_set->initByExporter($this);
        $this->sorting_data_set->setDSPrefix("ds");
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        if ($a_entity !== 'struct') {
            return [];
        }


        $res = [];

        // pages

        $pg_ids = [];

        // container pages
        foreach ($a_ids as $id) {
            if (ilContainerPage::_exists("cont", (int) $id)) {
                $pg_ids[] = "cont:" . $id;
            }
        }

        // container start objects pages
        foreach ($a_ids as $id) {
            if (ilContainerStartObjectsPage::_exists("cstr", (int) $id)) {
                $pg_ids[] = "cstr:" . $id;
            }
        }

        if (count($pg_ids)) {
            $res[] = [
                "component" => "components/ILIAS/COPage",
                "entity" => "pg",
                "ids" => $pg_ids
            ];
        }

        // style
        $style_ids = [];
        foreach ($a_ids as $id) {
            // see #24888
            $style = $this->content_style_domain->styleForObjId((int) $id);
            $style_id = $style->getExportStyleId();
            if ($style_id > 0) {
                $style_ids[] = $style_id;
            }
        }
        if (count($style_ids)) {
            $res[] = [
                "component" => "components/ILIAS/Style",
                "entity" => "sty",
                "ids" => $style_ids
            ];
        }

        // service settings
        $res[] = [
            "component" => "components/ILIAS/ILIASObject",
            "entity" => "common",
            "ids" => $a_ids
        ];

        // skill profiles
        $res[] = [
            "component" => "components/ILIAS/Skill",
            "entity" => "skl_local_prof",
            "ids" => $a_ids
        ];

        // news settings
        $res[] = [
            "component" => "components/ILIAS/News",
            "entity" => "news_settings",
            "ids" => $a_ids
        ];

        // conditions settings
        $res[] = [
            "component" => "components/ILIAS/Conditions",
            "entity" => "cond",
            "ids" => $a_ids
        ];

        // sorting
        $res[] = [
            "component" => "components/ILIAS/Container",
            "entity" => "sorting",
            "ids" => $a_ids
        ];
        $res[] = [
            "component" => "components/ILIAS/Container",
            "entity" => "sorting_settings",
            "ids" => $a_ids
        ];

        return $res;
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        global $DIC;

        $log = $DIC->logger()->forComponent('exp');
        if ($a_entity === 'struct') {
            $log->debug(__METHOD__ . ': Received id = ' . $a_id);
            $ref_ids = ilObject::_getAllReferences((int) $a_id);
            $writer = new ilContainerXmlWriter(end($ref_ids));
            $writer->write();
            return $writer->xmlDumpMem(false);
        } elseif ($a_entity === 'sorting' || $a_entity === 'sorting_settings') {
            return $this->sorting_data_set->getXmlRepresentation(
                $a_entity,
                $a_schema_version,
                [$a_id],
                '',
                true,
                true
            );
        }
        return "";
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array[]
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        if ($a_entity === "struct") {
            return [
                "4.1.0" => [
                    "namespace" => "https://www.ilias.de/Modules/Folder/fold/4_1",
                    "xsd_file" => "ilias_fold_4_1.xsd",
                    "uses_dataset" => false,
                    "min" => "4.1.0",
                    "max" => ""
                ]
            ];
        } elseif ($a_entity === "sorting") {
            return [
                "12.0" => [
                    "uses_dataset" => true,
                    "min" => "12.0",
                    "max" => ""
                ]
            ];
        } elseif ($a_entity === "sorting_settings") {
            return [
                "12.0" => [
                    "uses_dataset" => true,
                    "min" => "12.0",
                    "max" => ""
                ]
            ];
        }
        return [];
    }
}
