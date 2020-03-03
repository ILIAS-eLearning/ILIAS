<?php

namespace ILIAS\Filesystem\Provider\FlySystem;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\FilesystemFacade;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Class FlySystemLocalFilesystemFactoryTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FlySystemLocalFilesystemFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FlySystemLocalFilesystemFactory $subject
     */
    private $subject;


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subject = new FlySystemLocalFilesystemFactory();
    }

    /**
     * @Test
     * @small
     */
    public function testCreationOfFilesystemWithLinkSkipBehaviourWhichShouldSucceed()
    {
        $rootPath = __DIR__;

        $privateAccessFile = 0700;
        $privateAccessDir = 0700;

        $publicAccessFile = 0744;
        $publicAccessDir = 0755;
        $lockMode = LOCK_EX;

        $config = new LocalConfig(
            $rootPath,
            $publicAccessFile,
            $privateAccessFile,
            $publicAccessDir,
            $privateAccessDir,
            $lockMode,
            LocalConfig::SKIP_LINKS
        );

        $filesystem = $this->subject->getInstance($config);
        $this->assertInstanceOf(FilesystemFacade::class, $filesystem, "Filesystem type must be " . FilesystemFacade::class);
    }

    /**
     * @Test
     * @small
     */
    public function testCreationOfFilesystemWithInvalidLinkBehaviourWhichShouldFail()
    {
        $rootPath = __DIR__;

        $privateAccessFile = 0700;
        $privateAccessDir = 0700;

        $publicAccessFile = 0744;
        $publicAccessDir = 0755;
        $lockMode = LOCK_EX;
        $invalidLinkBehaviour = 9999;

        $config = new LocalConfig(
            $rootPath,
            $publicAccessFile,
            $privateAccessFile,
            $publicAccessDir,
            $privateAccessDir,
            $lockMode,
            $invalidLinkBehaviour
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The supplied value \"$invalidLinkBehaviour\" is not a valid LocalConfig link behaviour constant.");

        $this->subject->getInstance($config);
    }

    /**
     * @Test
     * @small
     */
    public function testCreationOfFilesystemWithInvalidFileLockModeWhichShouldFail()
    {
        $rootPath = __DIR__;

        $privateAccessFile = 0700;
        $privateAccessDir = 0700;

        $publicAccessFile = 0744;
        $publicAccessDir = 0755;
        $invalidLockMode = 9999;

        $config = new LocalConfig(
            $rootPath,
            $publicAccessFile,
            $privateAccessFile,
            $publicAccessDir,
            $privateAccessDir,
            $invalidLockMode,
            LocalConfig::SKIP_LINKS
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The supplied value \"$invalidLockMode\" is not a valid file lock mode please check your local file storage configurations.");

        $this->subject->getInstance($config);
    }
}
