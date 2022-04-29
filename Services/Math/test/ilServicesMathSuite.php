<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestSuite;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesMathSuite extends TestSuite
{
    public static function suite() : \ilServicesMathSuite
    {
        $suite = new self();
        require_once 'Services/Math/test/ilMathTest.php';
        $suite->addTestSuite(ilMathTest::class);
        require_once 'Services/Math/test/ilMathPhpAdapterTest.php';
        $suite->addTestSuite(ilMathPhpAdapterTest::class);
        require_once 'Services/Math/test/ilMathBCAdapterTest.php';
        $suite->addTestSuite(ilMathBCAdapterTest::class);

        return $suite;
    }
}
