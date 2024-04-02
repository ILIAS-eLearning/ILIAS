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

use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;

class ilContentPageImporter extends ilXmlImporter implements ilContentPageObjectConstants
{
    protected ilContentPageDataSet $ds;
    private PageMetricsService $pageMetricsService;
    private \ILIAS\Style\Content\DomainService $content_style_domain;

    public function init(): void
    {
        global $DIC;

        $this->ds = new ilContentPageDataSet();
        $this->ds->setDSPrefix('ds');
        $this->ds->setImportDirectory($this->getImportDirectory());

        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();

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

        $style_map = $a_mapping->getMappingsOfEntity('Services/Style', 'sty');
        foreach ($style_map as $old_style_id => $new_style_id) {
            if (isset(ilContentPageDataSet::$style_map[$old_style_id]) &&
                is_array(ilContentPageDataSet::$style_map[$old_style_id])) {
                foreach (ilContentPageDataSet::$style_map[$old_style_id] as $new_copa_id) {
                    $this->content_style_domain
                        ->styleForObjId($new_copa_id)
                        ->updateStyleId((int) $new_style_id);
                }
            }
        }
    }
}
