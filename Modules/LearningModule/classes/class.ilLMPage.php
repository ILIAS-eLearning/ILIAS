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

/**
 * Extension of ilPageObject for learning modules
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPage extends ilPageObject
{
    protected \ILIAS\LearningModule\ReadingTime\ReadingTimeManager $lm_reading_time_manager;

    public function getParentType() : string
    {
        return "lm";
    }
    
    public function afterConstructor() : void
    {
        $this->getPageConfig()->configureByObjectId($this->getParentId());
        $this->lm_reading_time_manager = new \ILIAS\LearningModule\ReadingTime\ReadingTimeManager();
    }

    public function beforePageContentUpdate(ilPageContent $a_page_content) : void
    {
        if ($a_page_content->getType() == "par") {
            $glos = ilObjContentObject::lookupAutoGlossaries($this->getParentId());
            $a_page_content->autoLinkGlossaries($glos);
        }
    }

    public function afterUpdate(DOMDocument $domdoc, string $xml) : void
    {
        // send notifications
        $references = ilObject::_getAllReferences($this->getParentId());
        $notification = new ilLearningModuleNotification(
            ilLearningModuleNotification::ACTION_UPDATE,
            ilNotification::TYPE_LM_PAGE,
            new ilObjLearningModule(reset($references)),
            $this->getId()
        );
        $notification->send();

        // update lm reading time
        if ((int) $this->getParentId() > 0) {
            $this->lm_reading_time_manager->updateReadingTime($this->getParentId());
        }
    }

    protected function afterDelete() : void
    {
        if ((int) $this->getParentId() > 0) {
            $this->lm_reading_time_manager->updateReadingTime($this->getParentId());
        }
    }

    public function createWithLayoutId(int $a_layout_id) : void
    {
        //get XML Data for Layout
        $layout_obj = new ilPageLayout($a_layout_id);
        parent::setXMLContent($layout_obj->getXMLContent());
        parent::create(false);
    }
}
