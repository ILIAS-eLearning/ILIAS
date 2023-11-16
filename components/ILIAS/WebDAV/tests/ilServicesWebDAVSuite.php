<?php

declare(strict_types=1);

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

require_once __DIR__ . "/../../../../vendor/composer/vendor/autoload.php";

/**
 * @author Stephan Winiker <stephan.winiker@hslu.ch>
 * @version 1.0.0
 */
class ilServicesWebDAVSuite extends TestSuite
{
    public static function suite(): ilServicesWebDAVSuite
    {
        $suite = new ilServicesWebDAVSuite();

        require_once "./components/ILIAS/WebDAV/tests/traits/ilWebDAVCheckValidTitleTraitTest.php";
        $suite->addTestSuite("ilWebDAVCheckValidTitleTraitTest");

        require_once "./components/ILIAS/WebDAV/tests/lock/ilWebDAVLockUriPathResolverTest.php";
        $suite->addTestSuite("ilWebDAVLockUriPathResolverTest");

        require_once "./components/ILIAS/WebDAV/tests/dav/class.ilDAVContainerTest.php";
        $suite->addTestSuite("ilDAVContainerTest");

        require_once "./components/ILIAS/WebDAV/tests/dav/class.ilDAVClientNodeTest.php";
        $suite->addTestSuite("ilDAVClientNodeTest");

        return $suite;
    }
}
