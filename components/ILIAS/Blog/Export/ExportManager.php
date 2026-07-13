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

namespace ILIAS\Blog\Export;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalRepoService;
use ILIAS\Blog\InternalDomainService;

class ExportManager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    public function isCommentsExportPossible(int $blog_id): bool
    {
        $setting = $this->domain->settings();
        $notes = $this->domain->notes();
        $privacy = \ilPrivacySettings::getInstance();

        if ($setting->get("disable_comments")) {
            return false;
        }
        if (!$privacy->enabledCommentsExport()) {
            return false;
        }
        if (!$notes->commentsActive($blog_id)) {
            return false;
        }
        return true;
    }
}
