<?php

declare(strict_types=1);

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

namespace ILIAS\Taxonomy;

/**
 * Domain facade
 */
class DomainService
{
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain
    ) {
        $this->domain = $domain;
    }

    /**
     * Returns the taxonomies (ids or array with id/title) used by a repository object
     */
    public function getUsageOfObject(int $obj_id, bool $include_titles = false): array
    {
        return $this->domain->usage()->getUsageOfObject($obj_id, $include_titles);
    }

    public function isActivated(int $obj_id): bool
    {
        return $this->domain->settings($obj_id)->isActivated();
    }
}
