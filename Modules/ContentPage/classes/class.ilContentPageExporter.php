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

use ILIAS\Style\Content\DomainService;

class ilContentPageExporter extends ilXmlExporter implements ilContentPageObjectConstants
{
    protected ilContentPageDataSet $ds;
    protected DomainService $content_style_domain;

    public function init(): void
    {
        global $DIC;

        $this->ds = new ilContentPageDataSet();
        $this->ds->setDSPrefix('ds');
        $this->content_style_domain = $DIC->contentStyle()
                                          ->domain();
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], '', true, true);
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            '5.4.0' => [
                'namespace' => 'http://www.ilias.de/Modules/ContentPage/' . self::OBJ_TYPE . '/5_4',
                'xsd_file' => 'ilias_' . self::OBJ_TYPE . '_5_4.xsd',
                'uses_dataset' => true,
                'min' => '5.4.0',
                'max' => '',
            ],
        ];
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        $pageObjectIds = [];
        $styleIds = [];

        foreach ($a_ids as $copaObjId) {
            $copa = ilObjectFactory::getInstanceByObjId($copaObjId, false);
            if (!$copa || !($copa instanceof ilObjContentPage)) {
                continue;
            }

            $copaPageObjIds = $copa->getPageObjIds();
            foreach ($copaPageObjIds as $copaPageObjId) {
                $pageObjectIds[] = self::OBJ_TYPE . ':' . $copaPageObjId;
            }

            $style_id = $this->content_style_domain
                ->styleForObjId($copa->getId())
                ->getStyleId();
            if ($style_id > 0) {
                $styleIds[$style_id] = $style_id;
            }
        }

        $deps = [];

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

        if (self::OBJ_TYPE === $a_entity) {
            $deps[] = [
                'component' => 'Services/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];
        }

        return $deps;
    }
}
