<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio template page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageEditorGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageObjectGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilCalendarMonthGUI, ilConsultationHoursGUI
 */
class ilPortfolioTemplatePageGUI extends ilPortfolioPageGUI
{
    public function getParentType()
    {
        return "prtt";
    }
    
    protected function getPageContentUserId($a_user_id)
    {
        $ilUser = $this->user;
        
        // user
        if (!$this->may_write) {
            return $ilUser->getId();
        }
        // author
        else {
            return $a_user_id;
        }
    }
    
    public function showPage()
    {
        if (!$this->getPageObject()) {
            return;
        }
        
        switch ($this->getPageObject()->getType()) {
            case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
                return $this->renderPageElement("BlogTemplate", $this->renderBlogTemplate());
                
            default:
                
                // needed for placeholders
                $this->tpl->addCss(ilObjStyleSheet::getPlaceHolderStylePath());
                
                return parent::showPage();
        }
    }
    
    protected function renderPageElement($a_type, $a_html)
    {
        return parent::renderPageElement($a_type, $this->addPlaceholderInfo($a_html));
    }
    
    protected function addPlaceholderInfo($a_html)
    {
        return '<fieldset style="border: 1px dashed red; padding: 3px; margin: 5px;">' .
                    '<legend style="color: red; font-style: italic;" class="small">' .
                        $this->lng->txt("prtf_template_editor_placeholder_info") .
                    '</legend>' .
                    trim($a_html) .
                '</fieldset>';
    }
    
    protected function renderBlogTemplate()
    {
        return $this->renderTeaser("blog_template", $this->lng->txt("obj_blog"));
    }

    /**
     * Get view page link
     *
     * @param
     * @return
     */
    public function getViewPageLink()
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $ctrl->setParameterByClass("ilobjportfoliotemplategui", "user_page", $this->requested_ppage);
        return $ctrl->getLinkTargetByClass("ilobjportfoliotemplategui", "preview");
    }

    /**
     * @inheritdoc
     */
    protected function getCourseSortAction($ctrl)
    {
        return $ctrl->getFormActionByClass("ilobjportfoliotemplategui", "preview");
    }

    public function finishEditing()
    {
        $this->ctrl->redirectByClass("ilObjPortfolioTemplateGUI", "view");
    }

}
