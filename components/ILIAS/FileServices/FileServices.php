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

declare(strict_types=1);

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\UI\Component\Input\Field\PhpUploadLimit;
use ILIAS\FileServices\FileServicesLegacyInitialisationAdapter;
use ILIAS\UI\Component\Input\Field\GlobalUploadLimit;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileServices\Upload\FileServicesPreProcessor;
use ILIAS\Filesystem\Configuration\FilesystemConfig;
use ILIAS\FileServices\Policy\UploadRestrictionBypass;
use ILIAS\FileServices\Policy\UploadRestrictionBypassLegacyProxy;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;
use ILIAS\Filesystem\FileSystems\FilesystemTemp;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Language\Language;
use ILIAS\Logging\Logger\LoggerFactoryInterface;
use ILIAS\FileServices\Download;
use ILIAS\FileServices\Legacy\RepositoryTreeProxy;
use ILIAS\AccessControl\PublicInterface\Access;

class FileServices implements Component
{
    public function init(
        array|\ArrayAccess &$define,
        array|\ArrayAccess &$implement,
        array|\ArrayAccess &$use,
        array|\ArrayAccess &$contribute,
        array|\ArrayAccess &$seek,
        array|\ArrayAccess &$provide,
        array|\ArrayAccess &$pull,
        array|\ArrayAccess &$internal,
    ): void {
        $define[] = UploadRestrictionBypass::class;
        $define[] = Download\DownloadLimit::class;

        $implement[UploadRestrictionBypass::class] = static fn() => new UploadRestrictionBypassLegacyProxy();
        $implement[PhpUploadLimit::class] = static fn() => new FileServicesLegacyInitialisationAdapter(
        );
        $implement[GlobalUploadLimit::class] = static fn(
        ) => new FileServicesLegacyInitialisationAdapter();

        $contribute[Agent::class] = static fn() => new \ilFileServicesSetupAgent(
            $pull[Factory::class]
        );

        $contribute[PreProcessor::class] = static fn() =>
            new FileServicesPreProcessor($use[FilesystemConfig::class]);
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'fils'
        );

        $contribute[Value::class] = static fn() => new Download\CopyDefinition();
        $contribute[Task::class] = static fn() =>
            new Download\CollectFilesJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                $use[Access::class],
                $internal[RepositoryTreeProxy::class]
            );
        $contribute[Task::class] = static fn() =>
            new Download\CheckSumOfFileSizesJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                $use[Access::class],
                $internal[RepositoryTreeProxy::class],
                $use[Download\DownloadLimit::class]
            );
        $contribute[Task::class] = static fn() =>
            new Download\CopyFilesToTempDirectoryJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal')
            );
        $contribute[Task::class] = static fn() =>
            new Download\ZipJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                new Archives()
            );
        $contribute[Task::class] = static fn() =>
            new Download\DownloadZipInteraction(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                $use[FilesystemTemp::class]
            );
        $contribute[Task::class] = static fn() =>
            new Download\SumOfFileSizesTooLargeInteraction(
                $use[Language::class]
            );

        // names of buckets created before ILIAS 12, remove with ILIAS 13
        $contribute[Value::class] = static fn() => new \ilCopyDefinition();
        $contribute[Task::class] = static fn() =>
            new \ilCollectFilesJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                $use[Access::class],
                $internal[RepositoryTreeProxy::class]
            );
        $contribute[Task::class] = static fn() =>
            new \ilCheckSumOfFileSizesJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                $use[Access::class],
                $internal[RepositoryTreeProxy::class],
                $use[Download\DownloadLimit::class]
            );
        $contribute[Task::class] = static fn() =>
            new \ilCopyFilesToTempDirectoryJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal')
            );
        $contribute[Task::class] = static fn() =>
            new \ilZipJob(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                new Archives()
            );
        $contribute[Task::class] = static fn() =>
            new \ilDownloadZipInteraction(
                $use[LoggerFactoryInterface::class]->getLazy('cal'),
                $use[FilesystemTemp::class]
            );
        $contribute[Task::class] = static fn() =>
            new \ilSumOfFileSizesTooLargeInteraction(
                $use[Language::class]
            );

        $internal[RepositoryTreeProxy::class] = static fn() => new RepositoryTreeProxy();
    }
}
