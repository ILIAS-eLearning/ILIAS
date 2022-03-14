<?php declare(strict_types=1);
/******************************************************************************
     *
     * This file is part of ILIAS, a powerful learning management system.
     *
     * ILIAS is licensed with the GPL-3.0, you should have received a copy
     * of said license along with the source code.
     *
     * If this is not the case or you just want to try ILIAS, you'll find
     * us at:
     *      https://www.ilias.de
     *      https://github.com/ILIAS-eLearning
     *
     *****************************************************************************/
    class SeqRollupRuleset
    {
        public ?array $mRollupRules = null;

        public bool $mIsSatisfied = false;

        public bool $mIsNotSatisfied = false;

        public bool $mIsCompleted = false;

        public bool $mIsIncomplete = false;
        
        
        public function __construct(?array $iRules)
        {
            $this->mRollupRules = $iRules;
        }
    }
