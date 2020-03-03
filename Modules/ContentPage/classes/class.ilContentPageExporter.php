<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageExporter
 */
class ilContentPageExporter extends \ilXmlExporter implements \ilContentPageObjectConstants
{
    /**
     * @var \ilContentPageDataSet
     */
    protected $ds;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->ds = new \ilContentPageDataSet();
        $this->ds->setDSPrefix('ds');
    }

    /**
     * @inheritdoc
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        \ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, '', true, true);
    }

    /**
     * @inheritdoc
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            '5.4.0' => array(
                'namespace'    => 'http://www.ilias.de/Modules/ContentPage/' . self::OBJ_TYPE . '/5_4',
                'xsd_file'     => 'ilias_' . self::OBJ_TYPE . '_5_4.xsd',
                'uses_dataset' => true,
                'min'          => '5.4.0',
                'max'          => '',
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $pageObjectIds = [];
        $styleIds      = [];

        foreach ($a_ids as $copaObjId) {
            $copa = \ilObjectFactory::getInstanceByObjId($copaObjId, false);
            if (!$copa || !($copa instanceof \ilObjContentPage)) {
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
                'entity'    => 'pg',
                'ids'       => $pageObjectIds,
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
}
