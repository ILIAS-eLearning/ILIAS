<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilContentPageExporter extends ilXmlExporter implements ilContentPageObjectConstants
{
    protected ilContentPageDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilContentPageDataSet();
        $this->ds->setDSPrefix('ds');
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], '', true, true);
    }

    public function getValidSchemaVersions(string $a_entity) : array
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

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
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

            if ($copa->getStyleSheetId() > 0) {
                $styleIds[$copa->getStyleSheetId()] = $copa->getStyleSheetId();
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
