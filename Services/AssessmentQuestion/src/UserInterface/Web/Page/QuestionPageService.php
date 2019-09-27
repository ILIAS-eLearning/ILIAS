<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

/**
 * Class QuestionPageService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionPageService extends AbstractPageService
{

    const PAGE_GUI_CLASS_NAME = 'ilAsqGenericFeedbackPageGUI';


    public function getPage() : Page
    {
        global $DIC;
        return Page::getPage('QuestionPage',$this->getIlObjectIntId(),$this->getQuestionIntId(),$DIC->language()->getDefaultLanguage());
    }


    /**
     * @return string
     */
    protected function getPageGUIClassName() : string
    {
        return self::PAGE_GUI_CLASS_NAME;
    }



}
