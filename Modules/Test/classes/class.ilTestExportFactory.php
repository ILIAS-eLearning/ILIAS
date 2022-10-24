<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
