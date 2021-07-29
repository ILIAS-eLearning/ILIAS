<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

/**
 * Class ilModulesChatroomSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilModulesChatroomSuite extends TestSuite
{
    /**
     * @return self
     * @throws ReflectionException
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once 'class.ilChatroomAbstractTest.php';
        require_once 'class.ilChatroomAbstractTaskTest.php';

        require_once 'class.ilObjChatroomTest.php';
        $suite->addTestSuite(ilObjChatroomTest::class);

        require_once 'class.ilChatroomServerSettingsTest.php';
        $suite->addTestSuite(ilChatroomServerSettingsTest::class);

        require_once 'class.ilObjChatroomAdminAccessTest.php';
        $suite->addTestSuite(ilObjChatroomAdminAccessTest::class);

        require_once 'class.ilObjChatroomAccessTest.php';
        $suite->addTestSuite(ilObjChatroomAccessTest::class);

        require_once 'class.ilChatroomUserTest.php';
        $suite->addTestSuite(ilChatroomUserTest::class);

        return $suite;
    }
}
