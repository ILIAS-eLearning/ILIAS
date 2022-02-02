<?php
use PHPUnit\Framework\TestSuite;

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
/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilServicesMathSuite extends TestSuite
{
    public static function suite() : \ilServicesMathSuite
    {
        $suite = new self();
        $suite->addTestSuite('ilMathTest');
        $suite->addTestSuite('ilMathPhpAdapterTest');
        $suite->addTestSuite('ilMathBCAdapterTest');

        return $suite;
    }
}
