<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;

/**
 * Class ilAsqFeedbackPageService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class GenericFeedbackPageService extends AbstractPageService
{

    const PAGE_SUB_TYPE = 'f';
    const PAGE_GUI_CLASS_NAME = 'ilAsqGenericFeedbackPageGUI';


    /**
     * @return string
     */
    protected function getPageSubType() : string
    {
        return self::PAGE_SUB_TYPE;
    }


    /**
     * @return string
     */
    protected function getPageGUIClassName() : string
    {
        return self::PAGE_GUI_CLASS_NAME;
    }
}
