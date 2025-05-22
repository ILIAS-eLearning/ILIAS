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

namespace ILIAS\Exercise\Settings;

use ILIAS\Exercise\InternalDataService;
use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\Settings\Settings;

class SettingsManager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }


    public function create(Settings $settings): void
    {
        $this->repo->settings()->create($settings);
    }

    public function update(Settings $settings): void
    {
        $this->repo->settings()->update($settings);
    }

    public function getByObjId(int $id): ?Settings
    {
        return $this->repo->settings()->getByObjId($id);
    }

    public function clone($from_id, $to_id): void
    {
        $settings = $this->repo->settings()->getByObjId($from_id);
        $settings = $settings->withObjId($to_id);
        $this->update($settings);
    }
}
