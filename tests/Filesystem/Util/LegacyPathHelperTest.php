<?php

namespace ILIAS\Filesystem\Util;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\DI\Container;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Filesystems;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class LegacyPathHelperTest
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class LegacyPathHelperTest extends TestCase
{
    private $libsPath;
    private $customizingPath;
    private $tempPath;
    private $storagePath;
    private $webPath;
    /**
     * @var MockInterface | Filesystems $filesystemsMock
     */
    private $filesystemsMock;


    /**
     * @inheritDoc
     */
    protected function setUp()
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

        $this->customizingPath = $iliasAbsolutePath . '/' . 'Customizing';
        $this->libsPath = $iliasAbsolutePath . '/' . 'libs';
        $this->webPath = CLIENT_WEB_DIR;
        $this->storagePath = CLIENT_DATA_DIR;
        $this->tempPath = sys_get_temp_dir();

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
    public function testDeriveFilesystemFromWithWebTargetWhichShouldSucceed()
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
    public function testDeriveFilesystemFromWithStorageTargetWhichShouldSucceed()
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
    public function testDeriveFilesystemFromWithRelativeLibsTargetWhichShouldSucceed()
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
    public function testDeriveFilesystemFromWithAbsoluteLibsTargetWhichShouldSucceed()
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
    public function testDeriveFilesystemFromWithInvalidTargetWhichShouldFail()
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
    public function testCreateRelativePathWithWebTargetWhichShouldSucceed()
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
    public function testCreateRelativePathWithStorageTargetWhichShouldSucceed()
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
    public function testCreateRelativePathWithInvalidTargetWhichShouldFail()
    {
        $target = '/invalid/path/to/target';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$target}'");

        LegacyPathHelper::createRelativePath($target);
    }
}
