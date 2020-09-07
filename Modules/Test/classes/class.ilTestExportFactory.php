<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestExportFactory
{
    /**
     * @var ilObjTest
     */
    protected $testOBJ;
    
    public function __construct(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;
    }

    /**
     * @param string $mode
     * @return ilTestExportDynamicQuestionSet|ilTestExportFixedQuestionSet|ilTestExportRandomQuestionSet
     */
    public function getExporter($mode = "xml")
    {
        switch ($this->testOBJ->getQuestionSetType()) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:
                
                require_once 'Modules/Test/classes/class.ilTestExportFixedQuestionSet.php';
                return new ilTestExportFixedQuestionSet($this->testOBJ, $mode);

            case ilObjTest::QUESTION_SET_TYPE_RANDOM:

                require_once 'Modules/Test/classes/class.ilTestExportRandomQuestionSet.php';
                return new ilTestExportRandomQuestionSet($this->testOBJ, $mode);

            case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:

                require_once 'Modules/Test/classes/class.ilTestExportDynamicQuestionSet.php';
                return new ilTestExportDynamicQuestionSet($this->testOBJ, $mode);
        }
    }
}
