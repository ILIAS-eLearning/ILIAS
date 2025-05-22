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

namespace ILIAS\Wiki\Settings;

use ILIAS\Wiki\InternalDataService;
use ILIAS\Wiki\InternalRepoService;
use ILIAS\Wiki\InternalDomainService;

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

    public function getById(int $id): ?Settings
    {
        return $this->repo->settings()->getById($id);
    }

    public function getStartPageOptions(int $ref_id): array
    {
        $pm = $this->domain->page()->page($ref_id);
        $options = [];
        foreach ($pm->getWikiPages() as $page) {
            $options[(string) $page->getId()] = \ilStr::shortenTextExtended($page->getTitle(), 60, true);
        }
        return $options;
    }

    public function getStartPageId(Settings $settings): ?int
    {
        return \ilWikiPage::_getPageIdForWikiTitle(
            $settings->getId(),
            $settings->getStartPage()
        );
    }
}
