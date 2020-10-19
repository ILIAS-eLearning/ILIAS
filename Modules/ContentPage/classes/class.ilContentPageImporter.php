<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;

/**
 * Class ilContentPageImporter
 */
class ilContentPageImporter extends ilXmlImporter implements ilContentPageObjectConstants
{
    /** @var ilContentPageDataSet */
    protected $ds;
    /** @var PageMetricsService */
    private $pageMetricsService;

    /**
     *
     */
    public function init()
    {
        global $DIC;

        $this->ds = new ilContentPageDataSet();
        $this->ds->setDSPrefix('ds');
        $this->ds->setImportDirectory($this->getImportDirectory());

        $this->pageMetricsService = new PageMetricsService(
            new PageMetricsRepositoryImp($DIC->database()),
            $DIC->refinery()
        );
    }

    /**
     * @inheritdoc
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
    }

    /**
     * @inheritdoc
     */
    public function finalProcessing($a_mapping)
    {
        parent::finalProcessing($a_mapping);

        $copaMap = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
        foreach ($copaMap as $oldCopaId => $newCopaId) {
            $newCopaId = substr($newCopaId, strlen(self::OBJ_TYPE) + 1);

            ilContentPagePage::_writeParentId(self::OBJ_TYPE, $newCopaId, $newCopaId);

            $translations = ilContentPagePage::lookupTranslations(self::OBJ_TYPE, $newCopaId);
            foreach ($translations as $language) {
                $this->pageMetricsService->store(
                    new StorePageMetricsCommand(
                        (int) $newCopaId,
                        $language
                    )
                );
            }
        }

        $styleMapping = $a_mapping->getMappingsOfEntity('Modules/ContentPage', 'style');
        foreach ($styleMapping as $newCopaId => $oldStyleId) {
            $newStyleId = (int) $a_mapping->getMapping('Services/Style', 'sty', $oldStyleId);
            if ($newCopaId > 0 && $newStyleId > 0) {
                $copa = ilObjectFactory::getInstanceByObjId($newCopaId, false);
                if (!$copa || !($copa instanceof ilObjContentPage)) {
                    continue;
                }
                $copa->writeStyleSheetId($newStyleId);
                $copa->update();
            }
        }
    }
}
