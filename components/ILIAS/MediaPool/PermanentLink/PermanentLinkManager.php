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

namespace ILIAS\MediaPool\PermanentLink;

use ILIAS\UICore\PageContentProvider;
use ILIAS\StaticURL\Services as StaticUrl;
use ILIAS\Data\ReferenceId;
use ILIAS\MediaPool\InternalGUIService;

class PermanentLinkManager
{
    public function __construct(
        protected StaticUrl $static_url,
        protected InternalGUIService $gui,
        protected $ref_id = 0
    ) {
        $this->ref_id = $this->gui->standardRequest()->getRefId();
    }

    public function getPermanentLink(
    ): string {
        $id = $this->ref_id;
        $uri = $this->static_url->builder()->build(
            'mep', // namespace
            $id > 0 ? new ReferenceId($id) : null
        );
        return (string) $uri;
    }

    public function setPermanentLink(
    ): void {
        $uri = $this->getPermanentLink();
        PageContentProvider::setPermaLink($uri);
    }
}
