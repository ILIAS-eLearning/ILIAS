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
 * Exporter class for sessions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesForum
 */
class ilForumExporter extends ilXmlExporter implements ilForumObjectConstants
{
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function init(): void
    {
        global $DIC;
        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();
    }


    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $xml = '';

        if (ilObject::_lookupType((int) $a_id) === 'frm') {
            $writer = new ilForumXMLWriter();
            $writer->setForumId((int) $a_id);
            ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
            $writer->setFileTargetDirectories($this->getRelativeExportDirectory(), $this->getAbsoluteExportDirectory());
            $writer->start();
            $xml .= $writer->getXML();
        }

        return $xml;
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        $deps = [];

        if ('frm' === $a_entity) {
            $deps[] = [
                'component' => 'Services/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];

            $deps[] = [
                "component" => "Services/News",
                "entity" => "news_settings",
                "ids" => $a_ids
            ];
        }

        $pageObjectIds = [];
        $styleIds = [];

        foreach ($a_ids as $frmObjId) {
            $frm = ilObjectFactory::getInstanceByObjId($frmObjId, false);
            if (!$frm || !($frm instanceof ilObjForum)) {
                continue;
            }

            $frmPageObjIds = $frm->getPageObjIds();
            foreach ($frmPageObjIds as $frmPageObjId) {
                $pageObjectIds[] = self::OBJ_TYPE . ':' . $frmPageObjId;
            }

            $style_id = $this->content_style_domain->styleForObjId((int) $frmObjId)->getStyleId();
            if ($style_id > 0) {
                $styleIds[$style_id] = $style_id;
            }
        }

        if (count($pageObjectIds) > 0) {
            $deps[] = [
                'component' => 'Services/COPage',
                'entity' => 'pg',
                'ids' => $pageObjectIds,
            ];
        }

        if (count($styleIds) > 0) {
            $deps[] = [
                'component' => 'Services/Style',
                'entity' => 'sty',
                'ids' => array_values($styleIds),
            ];
        }

        return $deps;
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
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/4_1",
                "xsd_file" => "ilias_frm_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "4.4.999"
            ],
            "4.5.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/4_5",
                "xsd_file" => "ilias_frm_4_5.xsd",
                "uses_dataset" => false,
                "min" => "4.5.0",
                "max" => "5.0.999"
            ],
            "5.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/5_1",
                "xsd_file" => "ilias_frm_5_1.xsd",
                "uses_dataset" => false,
                "min" => "5.1.0",
                "max" => ""
            ]
        ];
    }
}
