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

namespace ILIAS\FileServices\Upload;

use ILIAS\Filesystem\Configuration\FilesystemConfig;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\AbstractRecursiveZipPreProcessor;

/**
 * Rejects uploads with extensions blacklisted in the file services settings.
 * Respects RBAC-based bypass for privileged users.
 */
class FileServicesPreProcessor extends AbstractRecursiveZipPreProcessor
{
    private ?array $blacklist = null;

    public function __construct(
        private readonly FilesystemConfig $settings,
        private readonly string $reason = 'Extension is blacklisted.'
    ) {
        // No DB reads in constructor — lazy via checkPath()
    }

    #[\Override]
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        if ($this->settings->isByPassAllowedForCurrentUser()) {
            return new ProcessingStatus(ProcessingStatus::OK, 'Blacklist override by RBAC');
        }
        return parent::process($stream, $metadata);
    }

    protected function checkPath(string $path): bool
    {
        $this->blacklist ??= $this->settings->getBlackListedSuffixes();

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (preg_match('/^ph(p[3457]?|t|tml|ar)$/i', $extension)) {
            return false;
        }

        return !in_array($extension, $this->blacklist, true);
    }

    protected function getRejectionMessage(): string
    {
        return $this->reason;
    }

    protected function getOKMessage(): string
    {
        return 'Extension is not blacklisted.';
    }
}
