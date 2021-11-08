<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Extension of ilPageObject for learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilLMPage extends ilPageObject
{
    /**
     * Get parent type
     * @return string parent type
     */
    public function getParentType() : string
    {
        return "lm";
    }
    
    /**
     * After constructor
     * @param
     * @return void
     */
    public function afterConstructor() : void
    {
        $this->getPageConfig()->configureByObjectId($this->getParentId());
    }


    /**
     * Before page content update
     * Note: This one is "work in progress", currently only text paragraphs call this hook
     * It is called before the page content object invokes the update procedure of
     * ilPageObject
     * @param
     * @return void
     */
    public function beforePageContentUpdate(ilPageContent $a_page_content) : void
    {
        if ($a_page_content->getType() == "par") {
            $glos = ilObjContentObject::lookupAutoGlossaries($this->getParentId());
            $a_page_content->autoLinkGlossaries($glos);
        }
    }

    /**
     * After update content send notifications.
     * @param DOMDocument $domdoc
     * @param string      $xml
     */
    public function afterUpdate(DOMDocument $domdoc, string $xml) : void
    {
        $references = ilObject::_getAllReferences($this->getParentId());
        $notification = new ilLearningModuleNotification(
            ilLearningModuleNotification::ACTION_UPDATE,
            ilNotification::TYPE_LM_PAGE,
            new ilObjLearningModule(reset($references)),
            $this->getId()
        );

        $notification->send();
    }

    /**
     * Create page with layout
     * @param int $a_layout_id
     */
    public function createWithLayoutId(int $a_layout_id)
    {

        //get XML Data for Layout
        $layout_obj = new ilPageLayout($a_layout_id);

        parent::setXMLContent($layout_obj->getXMLContent());
        parent::create(false);
    }
}
