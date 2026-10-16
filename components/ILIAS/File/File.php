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

use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Component\Activities\Activity;
use ILIAS\Component\Component;
use ILIAS\Component\Resource\OfComponent;
use ILIAS\Component\Resource\PublicAsset;
use ILIAS\File\Activities\CopyFile;
use ILIAS\File\Activities\CreateFile;
use ILIAS\File\Activities\DeleteFile;
use ILIAS\File\Activities\DeliverFileVersion;
use ILIAS\File\Activities\DownloadContainerAsZip;
use ILIAS\File\Activities\ListFileVersions;
use ILIAS\File\Activities\MoveFile;
use ILIAS\File\Activities\UpdateFileMetadata;
use ILIAS\File\Activities\DeliverFileObject;
use ILIAS\File\Activities\ListContainerContent;
use ILIAS\File\Activities\ListFiles;
use ILIAS\File\Activities\ReplaceFileContent;
use ILIAS\File\Activities\RepositoryProvider;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\ResourceStorage\Activities\DeliverResource;
use ILIAS\ResourceStorage\Activities\ServiceProvider as StorageProvider;
use ILIAS\FileUpload\Activities\FileParameter;
use ILIAS\FileUpload\Activities\Parameter;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;

class File implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $contribute[Agent::class] = static fn() =>
            new \ilFileObjectAgent(
                $pull[Factory::class]
            );

        $contribute[PublicAsset::class] = fn() =>
            new OfComponent($this, "default_file_icons", "assets");

        $contribute[\ILIAS\StaticURL\Handler\Handler::class] = static fn() =>
            new \ilFileStaticURLHandler();

        /**
         * Creating a file object is one activity, not a sequence of calls: the
         * file comes with the call, the object is created, put into the tree and
         * given its content - or nothing is left behind.
         */
        $internal[RepositoryProvider::class] = static fn() => new RepositoryProvider(
            $use[Access::class],
            $use[RBAC::class],
        );

        $internal[CreateFile::class] = static fn() => new CreateFile(
            $pull[FileParameter::class],
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        $internal[ReplaceFileContent::class] = static fn() => new ReplaceFileContent(
            $pull[FileParameter::class],
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        /**
         * Listing what is below a node is a matter of the repository, not of
         * this component, so it is not contributed as an Activity. It stays as
         * code, because listing files is that listing plus what a file is.
         */
        $internal[ListContainerContent::class] = static fn() => new ListContainerContent(
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        $internal[ListFiles::class] = static fn() => new ListFiles(
            $internal[RepositoryProvider::class],
            $internal[ListContainerContent::class],
            $pull[Parameter::class],
        );

        /**
         * Reading a file of the repository is guarded by the read permission on
         * the object; the resource behind it is then delivered by the according
         * activity of the ResourceStorage, which trusts this check.
         */
        $internal[DeliverFileObject::class] = static fn() => new DeliverFileObject(
            $internal[RepositoryProvider::class],
            $pull[DeliverResource::class],
            $pull[FilePayloadFactory::class],
            $pull[Parameter::class],
        );

        /**
         * Everything else a file object can do: its data, its versions, where it
         * lives, and whether it lives at all.
         */
        $internal[UpdateFileMetadata::class] = static fn() => new UpdateFileMetadata(
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        $internal[DeleteFile::class] = static fn() => new DeleteFile(
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        $internal[MoveFile::class] = static fn() => new MoveFile(
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        $internal[CopyFile::class] = static fn() => new CopyFile(
            $internal[RepositoryProvider::class],
            $pull[Parameter::class],
        );

        $internal[ListFileVersions::class] = static fn() => new ListFileVersions(
            $internal[RepositoryProvider::class],
            $pull[StorageProvider::class],
            $pull[Parameter::class],
        );

        $internal[DeliverFileVersion::class] = static fn() => new DeliverFileVersion(
            $internal[RepositoryProvider::class],
            $pull[StorageProvider::class],
            $pull[FilePayloadFactory::class],
            $pull[Parameter::class],
        );

        /**
         * Packing a container performs the listing and the delivery of every
         * single file, so it cannot pack what a user may not read.
         */
        $internal[DownloadContainerAsZip::class] = static fn() =>
            new DownloadContainerAsZip(
                $internal[ListFiles::class],
                $internal[DeliverFileObject::class],
                $pull[FilePayloadFactory::class],
                $pull[Parameter::class],
            );

        /**
         * Whoever pulls one of these: `perform()` checks nothing, that is the
         * contract of an Activity - it is the door for code that has checked
         * already. With anything that comes from a request, use
         * `maybePerformAs()`, or `isAllowedToPerform()` first.
         */
        $provide[CreateFile::class] = static fn() => $internal[CreateFile::class];
        $provide[ReplaceFileContent::class] = static fn() =>
            $internal[ReplaceFileContent::class];
        $provide[ListFiles::class] = static fn() => $internal[ListFiles::class];
        $provide[ListContainerContent::class] = static fn() =>
            $internal[ListContainerContent::class];
        $provide[DeliverFileObject::class] = static fn() =>
            $internal[DeliverFileObject::class];

        $contribute[Activity::class] = static fn() => $internal[CreateFile::class];
        $contribute[Activity::class] = static fn() => $internal[ReplaceFileContent::class];
        $contribute[Activity::class] = static fn() => $internal[ListFiles::class];
        $contribute[Activity::class] = static fn() => $internal[DeliverFileObject::class];
        $contribute[Activity::class] = static fn() => $internal[UpdateFileMetadata::class];
        $contribute[Activity::class] = static fn() => $internal[DeleteFile::class];
        $contribute[Activity::class] = static fn() => $internal[MoveFile::class];
        $contribute[Activity::class] = static fn() => $internal[CopyFile::class];
        $contribute[Activity::class] = static fn() => $internal[ListFileVersions::class];
        $contribute[Activity::class] = static fn() => $internal[DeliverFileVersion::class];
        $contribute[Activity::class] = static fn() => $internal[DownloadContainerAsZip::class];
    }
}
