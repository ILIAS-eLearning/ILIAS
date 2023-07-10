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

namespace ILIAS\Filesystem\Util;

use ILIAS\DI\Container;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Filesystems;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class LegacyPathHelperTest extends TestCase
{
    private string $libsPath;
    private string $storagePath;
    private string $webPath;
    private \Mockery\MockInterface|\ILIAS\Filesystem\Filesystems $filesystemsMock;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $iliasAbsolutePath = '/dummy/var/www/html/ilias';
        $dataDir = '/dummy/var/www/ildata';
        $webDir = 'data';
        $clientId = 'default';

        //constants needed for test subject
        define("CLIENT_DATA_DIR", $dataDir . '/' . $clientId);
        define("CLIENT_WEB_DIR", $iliasAbsolutePath . '/' . $webDir . '/' . $clientId);
        define("ILIAS_ABSOLUTE_PATH", $iliasAbsolutePath);
        define("ILIAS_WEB_DIR", $webDir);
        define("CLIENT_ID", 'default');
        $this->libsPath = $iliasAbsolutePath . '/' . 'libs';
        $this->webPath = CLIENT_WEB_DIR;
        $this->storagePath = CLIENT_DATA_DIR;

        //create mock DI container
        $this->filesystemsMock = \Mockery::mock(Filesystems::class);

        $containerMock = Mockery::mock(Container::class);
        $containerMock->shouldReceive('filesystem')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->filesystemsMock);

        $GLOBALS['DIC'] = $containerMock;
    }


    /**
     * @Test
     * @small
     */
    public function testDeriveFilesystemFromWithWebTargetWhichShouldSucceed(): void
    {
        $target = $this->webPath . '/testtarget';

        $this->filesystemsMock
            ->shouldReceive('web')
            ->once()
            ->andReturn(Mockery::mock(Filesystem::class));

        $filesystem = LegacyPathHelper::deriveFilesystemFrom($target);
        $this->assertTrue($filesystem instanceof Filesystem, 'Expecting filesystem instance.');
    }


    /**
     * @Test
     * @small
     */
    public function testDeriveFilesystemFromWithStorageTargetWhichShouldSucceed(): void
    {
        $target = $this->storagePath . '/testtarget';

        $this->filesystemsMock
            ->shouldReceive('storage')
            ->once()
            ->andReturn(Mockery::mock(Filesystem::class));

        $filesystem = LegacyPathHelper::deriveFilesystemFrom($target);
        $this->assertTrue($filesystem instanceof Filesystem, 'Expecting filesystem instance.');
    }


    /**
     * @Test
     * @small
     */
    public function testDeriveFilesystemFromWithRelativeLibsTargetWhichShouldSucceed(): void
    {
        $target = './libs/bower/bower_components/mediaelement/build';

        $this->filesystemsMock
            ->shouldReceive('libs')
            ->once()
            ->andReturn(Mockery::mock(Filesystem::class));

        $filesystem = LegacyPathHelper::deriveFilesystemFrom($target);
        $this->assertTrue($filesystem instanceof Filesystem, 'Expecting filesystem instance.');
    }

    /**
     * @Test
     * @small
     */
    public function testDeriveFilesystemFromWithAbsoluteLibsTargetWhichShouldSucceed(): void
    {
        $target = $this->libsPath . 'libs/bower/bower_components/mediaelement/build';

        $this->filesystemsMock
            ->shouldReceive('libs')
            ->once()
            ->andReturn(Mockery::mock(Filesystem::class));

        $filesystem = LegacyPathHelper::deriveFilesystemFrom($target);
        $this->assertTrue($filesystem instanceof Filesystem, 'Expecting filesystem instance.');
    }


    /**
     * @Test
     * @small
     */
    public function testDeriveFilesystemFromWithInvalidTargetWhichShouldFail(): void
    {
        $target = '/invalid/path/to/testtarget';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$target}'");

        LegacyPathHelper::deriveFilesystemFrom($target);
    }


    /**
     * @Test
     * @small
     */
    public function testCreateRelativePathWithWebTargetWhichShouldSucceed(): void
    {
        $expectedPath = 'testtarget/subdir';
        $target = $this->webPath . '/' . $expectedPath;

        $result = LegacyPathHelper::createRelativePath($target);
        $this->assertEquals($expectedPath, $result);
    }


    /**
     * @Test
     * @small
     */
    public function testCreateRelativePathWithStorageTargetWhichShouldSucceed(): void
    {
        $expectedPath = 'testtarget/subdir';
        $target = $this->storagePath . '/' . $expectedPath;

        $result = LegacyPathHelper::createRelativePath($target);
        $this->assertEquals($expectedPath, $result);
    }


    /**
     * @Test
     * @small
     */
    public function testCreateRelativePathWithInvalidTargetWhichShouldFail(): void
    {
        $target = '/invalid/path/to/target';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$target}'");

        LegacyPathHelper::createRelativePath($target);
    }
}
