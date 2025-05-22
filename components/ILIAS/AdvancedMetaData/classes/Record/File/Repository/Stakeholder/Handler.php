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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Stakeholder;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Stakeholder\HandlerInterface as FileRepositoryStakeholderInterface;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class Handler extends AbstractResourceStakeholder implements FileRepositoryStakeholderInterface
{
    public function getId(): string
    {
        return "AdvancedMetaDataFiles";
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->default_owner;
    }

    public function withOwnerId(
        int $owner_id
    ): FileRepositoryStakeholderInterface {
        $clone = clone $this;
        $clone->setOwner($owner_id);
        return $clone;
    }
}
