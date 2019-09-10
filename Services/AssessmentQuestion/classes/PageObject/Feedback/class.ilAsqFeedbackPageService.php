<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @param int $feedbackIntId
     *
     * @return ilAsqGenericFeedbackPage
     */
    public function getGenericFeedbackPage(int $feedbackIntId) : ilAsqGenericFeedbackPage
    {
        return new ilAsqGenericFeedbackPage($feedbackIntId);
    }

    /**
     * @param int $parentObjId
     * @param int $feedbackIntId
     *
     * @return ilAsqGenericFeedbackPage
     */
    public function createGenericFeedbackPage(int $parentObjId, int $feedbackIntId) : ilAsqGenericFeedbackPage
    {
        $page = new ilAsqGenericFeedbackPage(0);
        $page->setId($feedbackIntId);
        $page->setParentId($parentObjId);
        $page->create();

        return $page;
    }
}
