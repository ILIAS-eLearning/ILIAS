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

namespace ILIAS\PersonalWorkspace\PermanentLink;

use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Services as StaticUrl;
use ILIAS\UICore\PageContentProvider;

class PermanentLinkManager
{
    public function __construct(
        protected StaticUrl $static_url,
        protected int $wsp_id
    ) {
    }

    public function getPermanentLink(): string
    {
        return (string) $this->static_url->builder()->build(
            'wsp',
            $this->wsp_id > 0 ? new ReferenceId($this->wsp_id) : null
        );
    }

    public function setPermanentLink(): void
    {
        PageContentProvider::setPermaLink($this->getPermanentLink());
    }
}
