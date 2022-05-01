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
        if ($this->testOBJ->isFixedTest()) {
            return new ilTestExportFixedQuestionSet($this->testOBJ, $mode);
        } elseif ($this->testOBJ->isRandomTest()) {
            return new ilTestExportRandomQuestionSet($this->testOBJ, $mode);
        }
        return new ilTestExportDynamicQuestionSet($this->testOBJ, $mode);
    }
}
