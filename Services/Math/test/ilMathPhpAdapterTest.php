<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Math/test/ilMathBaseAdapterTest.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilMathPhpAdapterTest extends ilMathBaseAdapterTest
{
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        require_once 'Services/Math/classes/class.ilMathPhpAdapter.php';
        $this->mathAdapter = new ilMathPhpAdapter();
        parent::setUp();
    }
}
