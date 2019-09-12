<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
class ilAsqFeedbackPageService
{
    /**
     * @var AuthoringContextContainer
     */
    protected $contextContainer;


    /**
     * ilAsqFeedbackPageService constructor.
     *
     * @param AuthoringContextContainer $contextContainer
     */
    public function __construct(AuthoringContextContainer $contextContainer)
    {
        $this->contextContainer = $contextContainer;
    }


    /**
     * @param int $feedbackIntId
     *
     * @return ilAsqGenericFeedbackPage
     */
    public function getGenericFeedbackPage(int $feedbackIntId) : ilAsqGenericFeedbackPage
    {
        return new ilAsqGenericFeedbackPage($feedbackIntId);
    }


    /**
     * @param int $feedbackIntId
     *
     * @return ilAsqGenericFeedbackPage
     */
    public function createGenericFeedbackPage(int $feedbackIntId) : ilAsqGenericFeedbackPage
    {
        $page = new ilAsqGenericFeedbackPage(0);
        $page->setId($feedbackIntId);
        $page->setParentId($this->contextContainer->getObjId());
        $page->create();

        return $page;
    }


    /**
     * @param string $pageObjectType
     * @param int    $feedbackIntId
     */
    public function ensureFeedbackPageExists(string $pageObjectType, int $feedbackIntId)
    {
        if( \ilPageObject::_exists($pageObjectType, $feedbackIntId) )
        {
            return;
        }

        switch($pageObjectType)
        {
            case \ilAsqGenericFeedbackPage::PARENT_TYPE:

                    $this->createGenericFeedbackPage($feedbackIntId);
                    return;
        }

        throw new \InvalidArgumentException(
            'invalid page parent type given: '.$pageObjectType
        );
    }


    /**
     * @param string $pageObjectType
     * @param int    $feedbackIntId
     *
     * @return string
     */
    public function getFeedbackPageContent(string $pageObjectType, int $feedbackIntId)
    {
        switch($pageObjectType)
        {
            case \ilAsqGenericFeedbackPage::PARENT_TYPE:

                $pageObjectGUI = new \ilAsqGenericFeedbackPageGUI($feedbackIntId);
                $pageObjectGUI->setOutputMode('presentation');

                return $pageObjectGUI->presentation('presentation');
        }

        throw new \InvalidArgumentException(
            'invalid page parent type given: '.$pageObjectType
        );
    }


    /**
     * @param string $pageObjectType
     * @param int    $feedbackIntId
     *
     * @return string
     */
    public function getFeedbackPageEditingLink(string $pageObjectType, int $feedbackIntId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        switch($pageObjectType)
        {
            case \ilAsqGenericFeedbackPage::PARENT_TYPE:

                $class = \ilAsqGenericFeedbackPageGUI::class;
                break;

            default:

                throw new \InvalidArgumentException(
                    'invalid page parent type given: '.$pageObjectType
                );

        }

        $DIC->ctrl()->setParameterByClass($class, 'feedback_type', $pageObjectType);
        $DIC->ctrl()->setParameterByClass($class, 'feedback_id', $feedbackIntId);

        $linkHREF = $DIC->ctrl()->getLinkTargetByClass($class, 'edit');
        $linkTEXT = $DIC->language()->txt('asq_link_edit_feedback_page');

        return "<a href='$linkHREF'>$linkTEXT</a>";
    }
}
