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

namespace ILIAS\MediaObjects\Thumbs;

use ILIAS\MediaObjects\InternalDomainService;
use ILIAS\MediaObjects\InternalGUIService;

class ThumbsGUI
{
    private ThumbsManager $thumbs_manager;
    protected \ILIAS\MediaObjects\MediaObjectManager $media_manager;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
        $this->thumbs_manager = $this->domain->thumbs();
    }

    public function getThumbHtml(int $mob_id): string
    {
        $mob = new \ilObjMediaObject($mob_id);
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $im = $f->image()->responsive(
            $this->thumbs_manager->getThumbSrc($mob_id),
            $mob->getTitle()
        );
        return "<div class='ilMediaPoolPreviewThumbnail'>" .
            $r->render($im) .
            "</div>";
    }
}
