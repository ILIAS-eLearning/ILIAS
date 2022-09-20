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

use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;

class ilContentPageImporter extends ilXmlImporter implements ilContentPageObjectConstants
{
    protected ilContentPageDataSet $ds;
    private PageMetricsService $pageMetricsService;

    public function init(): void
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

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        $parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        parent::finalProcessing($a_mapping);

        $copaMap = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
        foreach ($copaMap as $oldCopaId => $newCopaId) {
            $newCopaId = (int) substr($newCopaId, strlen(self::OBJ_TYPE) + 1);

            ilContentPagePage::_writeParentId(self::OBJ_TYPE, $newCopaId, $newCopaId);

            $translations = ilContentPagePage::lookupTranslations(self::OBJ_TYPE, $newCopaId);
            foreach ($translations as $language) {
                $this->pageMetricsService->store(
                    new StorePageMetricsCommand(
                        $newCopaId,
                        $language
                    )
                );
            }
        }

        $styleMapping = $a_mapping->getMappingsOfEntity('Modules/ContentPage', 'style');
        foreach ($styleMapping as $newCopaId => $oldStyleId) {
            $newStyleId = (int) $a_mapping->getMapping('Services/Style', 'sty', $oldStyleId);
            if ($newCopaId > 0 && $newStyleId > 0) {
                $copa = ilObjectFactory::getInstanceByObjId((int) $newCopaId, false);
                if (!$copa || !($copa instanceof ilObjContentPage)) {
                    continue;
                }
                $copa->update();
            }
        }
    }
}
