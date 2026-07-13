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

class ilBlogNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    protected \ILIAS\Blog\InternalGUIService $blog_gui;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $service = $DIC->blog()->internal();
        $this->blog_gui = $service->gui();
    }

    public function getObjectLink(): string
    {
        $pl = $this->blog_gui->permanentLink($this->getNewsRefId());

        $n = $this->getNewsItem();
        $posting_id = 0;
        if ($n->getContextSubObjType() === "blp"
            && $n->getContextSubObjId() > 0) {
            $posting_id = $n->getContextSubObjId();
        }
        return $pl->getPermanentLink($posting_id);
    }
}
