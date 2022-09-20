<?php

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

use Mockery\Adapter\Phpunit\MockeryTestCase;

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
class ilWACPathTest //extends MockeryTestCase
{
    /**
     * Setup
     */
    protected function setUp(): void
    {
    }


    public function testMobs(): void
    {
        $ilWacPath = new ilWACPath('http://trunk.local/data/trunk/mobs/mm_270/Koeniz_Komturei1.jpg');
        $this->assertEquals('mobs', $ilWacPath->getModuleType());
        $this->assertEquals('mm_270', $ilWacPath->getModuleIdentifier());
        $this->assertEquals('Koeniz_Komturei1.jpg', $ilWacPath->getAppendix());
        $this->assertEquals('trunk', $ilWacPath->getClient());
        $this->assertFalse($ilWacPath->isInSecFolder());
        $this->assertFalse($ilWacPath->isStreamable());
        $this->assertFalse($ilWacPath->isVideo());
        $this->assertFalse($ilWacPath->isAudio());
    }


    public function testUserImage(): void
    {
        $ilWacPath = new ilWACPath('http://trunk.local/data/trunk/usr_images/usr_6_small.jpg?t=63944');
        $this->assertEquals('usr_images', $ilWacPath->getModuleType());
        $this->assertEquals('./data/trunk/usr_images/', $ilWacPath->getModulePath());
        $this->assertEquals(null, $ilWacPath->getModuleIdentifier());
        $this->assertEquals('usr_6_small.jpg', $ilWacPath->getAppendix());
        $this->assertEquals('trunk', $ilWacPath->getClient());
        $this->assertFalse($ilWacPath->isInSecFolder());
        $this->assertFalse($ilWacPath->isStreamable());
        $this->assertFalse($ilWacPath->isVideo());
        $this->assertFalse($ilWacPath->isAudio());
    }


    public function testBlogInSec(): void
    {
        $ilWacPath = new ilWACPath('http://trunk.local/data/trunk/sec/ilBlog/blog_123/Header.mp4');
        $this->assertEquals('ilBlog', $ilWacPath->getModuleType());
        $this->assertEquals('./data/trunk/sec/ilBlog/', $ilWacPath->getModulePath());
        $this->assertEquals('blog_123', $ilWacPath->getModuleIdentifier());
        $this->assertEquals('Header.mp4', $ilWacPath->getAppendix());
        $this->assertEquals('trunk', $ilWacPath->getClient());
        $this->assertTrue($ilWacPath->isInSecFolder());
        $this->assertTrue($ilWacPath->isStreamable());
        $this->assertTrue($ilWacPath->isVideo());
        $this->assertFalse($ilWacPath->isAudio());
    }


    public function testSubfolders(): void
    {
        $ilWacPathBase = new ilWACPath('http://trunk.local/data/trunk/lm_data/lm_123456/start.html');
        $ilWacPathSub = new ilWACPath('http://trunk.local/data/trunk/lm_data/lm_123456/subfolder/image.png');
        $this->assertEquals($ilWacPathBase->getModulePath(), $ilWacPathSub->getModulePath());
    }
}
