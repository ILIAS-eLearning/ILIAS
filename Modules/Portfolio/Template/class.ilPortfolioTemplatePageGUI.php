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
 * Portfolio template page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageEditorGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageObjectGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilCalendarMonthGUI, ilConsultationHoursGUI
 */
class ilPortfolioTemplatePageGUI extends ilPortfolioPageGUI
{
    protected bool $may_write = false;

    public function getParentType(): string
    {
        return "prtt";
    }

    protected function getPageContentUserId(int $a_user_id): int
    {
        $ilUser = $this->user;

        // user
        if (!$this->may_write) {
            return $ilUser->getId();
        }
        // author
        return $a_user_id;
    }

    public function showPage(): string
    {
        if (!$this->getPageObject()) {
            return "";
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

    protected function renderPageElement(
        string $a_type,
        string $a_html
    ): string {
        return parent::renderPageElement($a_type, $this->addPlaceholderInfo($a_html));
    }

    protected function addPlaceholderInfo(string $a_html): string
    {
        return '<fieldset style="border: 1px dashed red; padding: 3px; margin: 5px;">' .
                    '<legend style="color: red; font-style: italic;" class="small">' .
                        $this->lng->txt("prtf_template_editor_placeholder_info") .
                    '</legend>' .
                    trim($a_html) .
                '</fieldset>';
    }

    protected function renderBlogTemplate(): string
    {
        return $this->renderTeaser("blog_template", $this->lng->txt("obj_blog"));
    }

    public function getViewPageLink(): string
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $ctrl->setParameterByClass("ilobjportfoliotemplategui", "user_page", $this->requested_ppage);
        return $ctrl->getLinkTargetByClass("ilobjportfoliotemplategui", "preview");
    }

    protected function getCourseSortAction(ilCtrl $ctrl): string
    {
        return $ctrl->getFormActionByClass("ilobjportfoliotemplategui", "preview");
    }

    public function finishEditing(): void
    {
        $this->ctrl->redirectByClass("ilObjPortfolioTemplateGUI", "view");
    }
}
