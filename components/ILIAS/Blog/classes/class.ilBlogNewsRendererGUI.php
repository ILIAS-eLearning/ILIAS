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

/**
 * Blog news renderer
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBlogNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    public function getObjectLink(): string
    {
        global $DIC;

        $pl = $DIC->blog()->internal()->gui()->permanentLink($this->getNewsRefId());

        $n = $this->getNewsItem();
        $posting_id = 0;
        if ($n->getContextSubObjType() === "blp"
            && $n->getContextSubObjId() > 0) {
            $posting_id = $n->getContextSubObjId();
        }
        return $pl->getPermanentLink($posting_id);
    }
}
