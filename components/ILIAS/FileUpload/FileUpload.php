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
use ILIAS\FileUpload\FileUpload as FileUploadInterface;
use ILIAS\FileUpload\FileUploadImpl;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\Processor\PreProcessorCollection;
use ILIAS\FileUpload\Processor\PreProcessorCollectionImpl;
use ILIAS\FileUpload\Processor\PreProcessorManagerImpl;
use ILIAS\Filesystem\Filesystems;
use ILIAS\HTTP\GlobalHttpState;

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

        $internal[PreProcessorCollection::class] = static fn(): PreProcessorCollection =>
            new PreProcessorCollectionImpl($seek[PreProcessor::class]);

        $internal[PreProcessorManagerImpl::class] = static fn(): PreProcessorManagerImpl =>
            new PreProcessorManagerImpl($internal[PreProcessorCollection::class]);

        $implement[FileUploadInterface::class] = static fn(): FileUploadInterface =>
            new FileUploadImpl(
                $internal[PreProcessorManagerImpl::class],
                $use[Filesystems::class],
                $use[GlobalHttpState::class]
            );
    }
}
