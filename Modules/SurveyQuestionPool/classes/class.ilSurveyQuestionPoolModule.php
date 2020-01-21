<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TestQuestionPool Module.
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilSurveyQuestionPoolModule extends ilModule
{
    /**
    * Core modules vs. plugged in modules
    */
    public function isCore()
    {
        return true;
    }

    /**
    * Get version of module. This is especially important for
    * non-core modules.
    */
    public function getVersion()
    {
        return "-";
    }
}
