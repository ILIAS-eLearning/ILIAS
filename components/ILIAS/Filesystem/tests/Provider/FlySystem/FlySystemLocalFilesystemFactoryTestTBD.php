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

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\FilesystemFacade;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
class FlySystemLocalFilesystemFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private \ILIAS\Filesystem\Provider\FlySystem\FlySystemLocalFilesystemFactory $subject;


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FlySystemLocalFilesystemFactory();
    }

    /**
     * @Test
     * @small
     */
    public function testCreationOfFilesystemWithLinkSkipBehaviourWhichShouldSucceed(): void
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
    public function testCreationOfFilesystemWithInvalidLinkBehaviourWhichShouldFail(): void
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
    public function testCreationOfFilesystemWithInvalidFileLockModeWhichShouldFail(): void
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
