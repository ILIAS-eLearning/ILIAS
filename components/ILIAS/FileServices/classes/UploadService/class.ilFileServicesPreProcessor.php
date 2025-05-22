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

use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class ilFileServicesPolicy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileServicesPreProcessor extends BlacklistExtensionPreProcessor
{
    public function __construct(
        private ilFileServicesSettings $settings,
        string $reason = 'Extension is blacklisted.'
    ) {
        parent::__construct($this->settings->getBlackListedSuffixes(), $reason);
    }

    #[\Override]
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        if ($this->settings->isByPassAllowedForCurrentUser()) {
            return new ProcessingStatus(ProcessingStatus::OK, 'Blacklist override by RBAC');
        }
        return parent::process($stream, $metadata);
    }
}
