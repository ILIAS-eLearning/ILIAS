<?php

use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\Services;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use org\bovigo\vfs;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

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
 * TestCase for the ilWACCheckingInstanceTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 * @version                1.0.0
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilWACCheckingInstanceTest //extends MockeryTestCase
{
    /**
     * @var vfs\vfsStreamFile
     */
    protected $file_one;
    /**
     * @var vfs\vfsStreamDirectory
     */
    protected $root;


    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->root = vfs\vfsStream::setup('ilias.de');
        $this->file_one = vfs\vfsStream::newFile('data/trunk/mobs/mm_123/dummy.jpg')
                                       ->at($this->root)->setContent('dummy');

        //setup container for HttpServiceAware classes
        $container = new \ILIAS\DI\Container();
        $container['http'] = fn ($c) => Mockery::mock(Services::class);


        $GLOBALS["DIC"] = $container;
        ilWACToken::setSALT('TOKEN');
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState    disabled
     * @backupGlobals          disabled
     * @backupStaticAttributes disabled
     */
    public function testDeliver(): void
    {
        self::markTestSkipped("WIP");
    }


    public function testBasic(): void
    {
        self::markTestSkipped("Can't run test without db.");
    }


    public function testBasicWithFileSigning(): void
    {
        self::markTestSkipped("WIP");
    }


    public function testBasicWithFolderSigning(): void
    {
        self::markTestSkipped("WIP");
    }


    /**
     * @Test
     */
    public function testNonCheckingInstanceNoSec(): void
    {
        self::markTestSkipped("Can't run test without db.");

        return;
        //		$this->assertTrue($check); // Currently not able to init ILIAS in WAC during PHPUnit
        //		$this->assertEquals(array(
        //			$ilWebAccessChecker::CM_SECFOLDER,
        //		), $ilWebAccessChecker->getAppliedCheckingMethods());
    }
}
