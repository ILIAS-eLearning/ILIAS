<?php declare(strict_types=1);

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


use ILIAS\DI\Container;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ilSessionTest
 */
class ilECSUserTest extends TestCase
{
    protected function setUp() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable(
            'ilSetting',
            $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock()
        );

        parent::setUp();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    public function testConstructorWithArray() : void
    {
        $testdata = [];
        $testdata['ecs_login'] = 'testlogin';
        $testdata['ecs_firstname'] = 'test_firstname';
        $testdata['ecs_lastname'] = 'test_lastname';

        $testdata['ecs_institution'] = 'test_institution';
        $testdata['ecs_email'] = 'test@email.nowhere';
        $testdata['ecs_uid_hash'] = 'test_hash';

        $user = new ilECSUser($testdata);
        $this->assertEquals('testlogin', $user->getLogin());
    }
}
