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

namespace ILIAS\Wiki\Page;

use ILIAS\Wiki\InternalDataService;

class ImportResolver
{
    public function __construct(
        protected InternalDataService $data,
        protected PageDBRepository $page_repo
    ) {
    }

    /**
     * Get latest non-trashed wiki page with import id
     */
    public function getIdForImportId(string $import_id): int
    {
        foreach ($this->page_repo->getPageIdsForImportId($import_id) as $wpage_id) {
            $wiki_id = \ilWikiPage::lookupWikiId($wpage_id);
            $ref_ids = \ilObject::_getAllReferences($wiki_id);	// will be 0 if import of lm is in progress (new import)
            if (count($ref_ids) === 0 || \ilObject::_hasUntrashedReference($wiki_id)) {
                return $wpage_id;
            }
        }
        return 0;
    }

}
