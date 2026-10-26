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
use ILIAS\FileUpload\Activities\FileParameter;
use ILIAS\FileUpload\Activities\Parameter;
use ILIAS\FileUpload\Activities\TempFileStore;
use ILIAS\FileUpload\FileUpload as FileUploadInterface;
use ILIAS\FileUpload\FileUploadImpl;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\Processor\PreProcessorCollection;
use ILIAS\FileUpload\Processor\PreProcessorCollectionImpl;
use ILIAS\FileUpload\Processor\PreProcessorManagerImpl;
use ILIAS\UI\Component\Input\Field\GlobalUploadLimit;
use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\FileSystems\FilesystemTemp;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

class FileUpload implements Component
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
        $define[] = FileUploadInterface::class;

        $internal[PreProcessorCollection::class] = static fn() =>
            new PreProcessorCollectionImpl($seek[PreProcessor::class]);

        $internal[PreProcessorManagerImpl::class] = static fn() =>
            new PreProcessorManagerImpl($internal[PreProcessorCollection::class]);

        $implement[FileUploadInterface::class] = static fn() =>
            new FileUploadImpl(
                $internal[PreProcessorManagerImpl::class],
                $use[Filesystems::class],
                $use[GlobalHttpState::class]
            );

        $internal[TempFileStore::class] = static fn() => new TempFileStore(
            $use[FilesystemTemp::class],
        );

        /**
         * How an activity reads its other parameters: strictly, with the
         * Refinery, so that no cast makes an answer of a wrong question.
         */
        $internal[Parameter::class] = static fn() => new Parameter(
            $pull[Refinery::class],
        );

        /**
         * How an activity takes a file: the description of the parameters and
         * the reading of them. Uploading is no activity of its own - a user does
         * not want a handle, they want a file somewhere - so the bytes travel
         * with the activity that uses them, and this is the code doing that,
         * shared by all of them. See src/Activities/README.md.
         */
        $internal[FileParameter::class] = static fn() => new FileParameter(
            $internal[TempFileStore::class],
            $internal[PreProcessorManagerImpl::class],
            $internal[Parameter::class],
            $use[GlobalUploadLimit::class],
        );

        $provide[FileParameter::class] = static fn() => $internal[FileParameter::class];
        $provide[TempFileStore::class] = static fn() => $internal[TempFileStore::class];
        $provide[Parameter::class] = static fn() => $internal[Parameter::class];
    }
}
