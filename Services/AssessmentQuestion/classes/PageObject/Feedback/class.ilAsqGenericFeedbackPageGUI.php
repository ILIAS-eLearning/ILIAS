<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAsqGenericFeedbackPageGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqGenericFeedbackPageGUI extends ilPageObjectGUI
{
    public function __construct($feedbackIntId = 0)
    {
        parent::__construct(
            ilAsqGenericFeedbackPage::PARENT_TYPE, $feedbackIntId
        );
    }
}
