<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/test/ilMathBaseAdapterTest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathBCAdapterTest extends ilMathBaseAdapterTest
{
    /**
     * @inheritDoc
     */
    public function setUp()
    {
        if (!extension_loaded('bcmath')) {
            $this->markTestSkipped('Could not execute test due to missing bcmath extension!');
            return;
        }

        require_once 'Services/Math/classes/class.ilMathBCMathAdapter.php';
        $this->mathAdapter = new ilMathBCMathAdapter();
        parent::setUp();
    }

    /**
     * @return array
     */
    public function powData()
    {
        return array_merge([
            ['2', '64', '18446744073709551616', self::DEFAULT_SCALE],
        ], parent::powData());
    }
}
