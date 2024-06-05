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

use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\ResourceStorage\Policy\WhiteAndBlacklistedFileNamePolicy;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class Temporary
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilTemporaryStakeholder extends AbstractResourceStakeholder
{
    public function getId(): string
    {
        return 'irss_temp';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->default_owner;
    }
}
