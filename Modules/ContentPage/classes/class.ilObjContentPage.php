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

use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\Style\Content\DomainService;

class ilObjContentPage extends ilObject2 implements ilContentPageObjectConstants
{
    protected int $styleId = 0;
    protected ?ilObjectTranslation $objTrans = null;
    private PageMetricsService $pageMetricsService;
    protected DomainService $content_style_domain;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->initTranslationService();
        $this->initPageMetricsService($DIC->refinery());
        $this->content_style_domain = $DIC->contentStyle()
            ->domain();
    }

    private function initTranslationService(): void
    {
        if (null === $this->objTrans && $this->getId() > 0) {
            $this->objTrans = ilObjectTranslation::getInstance($this->getId());
        }
    }

    private function initPageMetricsService(ILIAS\Refinery\Factory $refinery): void
    {
        $this->pageMetricsService = new PageMetricsService(
            new PageMetricsRepositoryImp($this->db),
            $refinery
        );
    }

    public function getObjectTranslation(): ilObjectTranslation
    {
        return $this->objTrans;
    }

    protected function initType(): void
    {
        $this->type = self::OBJ_TYPE;
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        assert($new_obj instanceof ilObjContentPage);
        parent::doCloneObject($new_obj, $a_target_id, $a_copy_id);

        $ot = ilObjectTranslation::getInstance($this->getId());
        $ot->copy($new_obj->getId());

        if (ilContentPagePage::_exists($this->getType(), $this->getId())) {
            $translations = ilContentPagePage::lookupTranslations($this->getType(), $this->getId());
            foreach ($translations as $language) {
                $originalPageObject = new ilContentPagePage($this->getId(), 0, $language);
                $copiedXML = $originalPageObject->copyXmlContent();

                $duplicatePageObject = new ilContentPagePage();
                $duplicatePageObject->setId($new_obj->getId());
                $duplicatePageObject->setParentId($new_obj->getId());
                $duplicatePageObject->setLanguage($language);
                $duplicatePageObject->setXMLContent($copiedXML);
                $duplicatePageObject->createFromXML();

                $this->pageMetricsService->store(
                    new StorePageMetricsCommand(
                        $new_obj->getId(),
                        $duplicatePageObject->getLanguage()
                    )
                );
            }
        }

        $style = $this->content_style_domain->styleForObjId($this->getId());
        $style->cloneTo($new_obj->getId());

        ilContainer::_writeContainerSetting(
            $new_obj->getId(),
            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            (string) ((bool) ilContainer::_lookupContainerSetting(
                $this->getId(),
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                '1'
            ))
        );

        $lpSettings = new ilLPObjSettings($this->getId());
        $lpSettings->cloneSettings($new_obj->getId());

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        //copy online status if object is not the root copy object
        if (!$cwo->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus($this->getOfflineStatus());
        } else {
            $new_obj->setOfflineStatus(true);
        }
        $new_obj->update();
    }

    protected function doRead(): void
    {
        parent::doRead();

        $this->initTranslationService();
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        parent::doCreate($clone_mode);

        $this->initTranslationService();

        $this->db->manipulateF(
            'INSERT INTO content_page_data (content_page_id, stylesheet ) VALUES(%s, %s)',
            ['integer', 'integer'],
            [$this->getId(), 0]
        );

        $this->setOfflineStatus(true);
        $this->update();
    }

    protected function doUpdate(): void
    {
        parent::doUpdate();

        $this->initTranslationService();

        $trans = $this->getObjectTranslation();
        $trans->setDefaultTitle($this->getTitle());
        $trans->setDefaultDescription($this->getLongDescription());
        $trans->save();
    }

    protected function doDelete(): void
    {
        parent::doDelete();

        if (ilContentPagePage::_exists($this->getType(), $this->getId())) {
            $originalPageObject = new ilContentPagePage($this->getId());
            $originalPageObject->delete();
        }

        $this->initTranslationService();
        $this->objTrans->delete();

        $this->db->manipulateF(
            'DELETE FROM content_page_metrics WHERE content_page_id = %s',
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
            'DELETE FROM content_page_data WHERE content_page_id = %s',
            ['integer'],
            [$this->getId()]
        );
    }

    /**
     * @return int[]
     */
    public function getPageObjIds(): array
    {
        $pageObjIds = [];

        $sql = 'SELECT DISTINCT page_id FROM page_object WHERE parent_id = %s AND parent_type = %s';
        $res = $this->db->queryF(
            $sql,
            ['integer', 'text'],
            [$this->getId(), $this->getType()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $pageObjIds[] = (int) $row['page_id'];
        }

        return $pageObjIds;
    }

    public function trackProgress(int $usrId): void
    {
        ilChangeEvent::_recordReadEvent(
            $this->getType(),
            $this->getRefId(),
            $this->getId(),
            $usrId
        );

        $lp = ilObjectLP::getInstance($this->getId());
        if ($lp->isActive() && $lp->getCurrentMode() === ilLPObjSettings::LP_MODE_CONTENT_VISITED) {
            $current_status = (int) ilLPStatus::_lookupStatus($this->getId(), $usrId, false);
            if ($current_status !== ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                ilLPStatusWrapper::_updateStatus(
                    $this->getId(),
                    $usrId
                );
            }
        }
    }
}
