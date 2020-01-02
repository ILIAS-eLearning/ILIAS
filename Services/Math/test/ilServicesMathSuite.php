<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilServicesMathSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return self
     */
    public static function suite()
    {
        $suite = new self();

        require_once 'Services/Math/test/ilMathTest.php';
        $suite->addTestSuite('ilMathTest');

        require_once 'Services/Math/test/ilMathPhpAdapterTest.php';
        $suite->addTestSuite('ilMathPhpAdapterTest');

        require_once 'Services/Math/test/ilMathBCAdapterTest.php';
        $suite->addTestSuite('ilMathBCAdapterTest');

        return $suite;
    }
}
