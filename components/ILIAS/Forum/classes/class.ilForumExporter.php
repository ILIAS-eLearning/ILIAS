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

declare(strict_types=1);

/**
 * Exporter class for sessions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup components\ILIASForum
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
                'component' => 'components/ILIAS/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];

            $deps[] = [
                "component" => "components/ILIAS/News",
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

        if ($pageObjectIds !== []) {
            $deps[] = [
                'component' => 'components/ILIAS/COPage',
                'entity' => 'pg',
                'ids' => $pageObjectIds,
            ];
        }

        if ($styleIds !== []) {
            $deps[] = [
                'component' => 'components/ILIAS/Style',
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
            "5.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/5_1",
                "xsd_file" => "ilias_frm_5_1.xsd",
                "uses_dataset" => false,
                "min" => "7.0",
                "max" => "7.999"
            ],
            "8.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/8",
                "xsd_file" => "ilias_frm_8.xsd",
                "uses_dataset" => false,
                "min" => "8.0",
                "max" => "8.999"
            ],
            "9.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/9",
                "xsd_file" => "ilias_frm_9.xsd",
                "uses_dataset" => false,
                "min" => "9.0",
                "max" => ""
            ]
        ];
    }
}
