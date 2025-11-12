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

namespace ILIAS\Help\GuidedTour\Settings;

use ilDBInterface;
use ILIAS\Help\GuidedTour\InternalDataService;
use ILIAS\Help\GuidedTour\InternalRepoService;
use ILIAS\Help\GuidedTour\InternalDomainService;

class SettingsManager
{
    protected SettingsDBRepository $repo;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
        $this->repo = $repo->settings();
    }

    public function save(Settings $settings): void
    {
        $this->repo->save($settings);
    }

    public function getByObjId(int $obj_id): ?Settings
    {
        return $this->repo->getByObjId($obj_id);
    }

    public function delete(int $obj_id): void
    {
        $this->repo->delete($obj_id);
    }

    public function getLangOptions(string $lang = ""): array
    {
        $options = [];
        $this->domain->lng()->loadLanguageModule("meta");
        foreach ($this->domain->lng()->getInstalledLanguages() as $key) {
            $options[$key] = $this->domain->lng()->txt('meta_l_' . $key);
        }
        if ($lang !== "" && !isset($options[$lang])) {
            $options[$lang] = $this->domain->lng()->txt('meta_l_' . $lang);
        }
        return $options;
    }

}
