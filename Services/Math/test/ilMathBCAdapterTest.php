<?php
require_once 'Services/Math/test/ilMathBaseAdapterTest.php';
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
class ilMathBCAdapterTest extends ilMathBaseAdapterTest
{
    /**
     * @inheritDoc
     */
    public function setUp() : void
    {
        if (!extension_loaded('bcmath')) {
            $this->markTestSkipped('Could not execute test due to missing bcmath extension!');
            return;
        }
        $this->mathAdapter = new ilMathBCMathAdapter();
        parent::setUp();
    }

    /**
     * @return mixed[]
     */
    public function powData() : array
    {
        return array_merge([
            ['2', '64', '18446744073709551616', 0],
        ], parent::powData());
    }
}
