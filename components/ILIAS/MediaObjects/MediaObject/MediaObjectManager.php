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

namespace ILIAS\MediaObjects;

use ilDBInterface;
use ILIAS\Exercise\IRSS\IRSSWrapper;

class MediaObjectManager
{
    protected MediaObjectRepository $repo;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected \ilMobStakeholder $stakeholder
    ) {
        $this->repo = $repo->mediaObject();
    }

    public function create(
        int $id,
        string $title
    ): void {
        $this->repo->create(
            $id,
            $title,
            $this->stakeholder
        );
    }

    public function addFileFromLegacyUpload(int $mob_id, string $upload_name, string $location): void
    {
        $this->repo->addFileFromLegacyUpload($mob_id, $upload_name, $location);
    }

    public function getLocationSrc(int $mob_id, string $location): string
    {
        return $this->repo->getLocationSrc($mob_id, $location);
    }

}
