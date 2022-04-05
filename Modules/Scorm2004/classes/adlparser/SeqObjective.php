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
    class SeqObjective
    {
        public string $mObjID = "_primary_";
        
        public bool $mSatisfiedByMeasure = false;
        
        public bool $mActiveMeasure = true;
        
        public float $mMinMeasure = 1.0;
        
        public bool $mContributesToRollup = false;
        
        public ?array $mMaps = null;
        
        public function __construct()
        {
        }
    }
