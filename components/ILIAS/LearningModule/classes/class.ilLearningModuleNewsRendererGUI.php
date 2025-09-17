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

use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Services as StaticUrl;

/**
 * Learning Module news renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    public function getObjectLink(): string
    {
        global $DIC;
        /** @var StaticUrl $static_url */
        $static_url = $DIC['static_url'];

        $n = $this->getNewsItem();
        if ($n->getContextSubObjType() == "pg"
            && $n->getContextSubObjId() > 0) {
            $uri = $static_url->builder()->build(
                'pg', // namespace
                null, // ref_id
                [
                    $n->getContextSubObjId(),
                    $this->getNewsRefId()
                ]
            );
            return (string) $uri;
            //$add = "&target=pg_".$n->getContextSubObjId()."_".$this->getNewsRefId();
            return ilLink::_getLink($n->getContextSubObjId() . "_" . $this->getNewsRefId(), "pg");
        }
        return ilLink::_getLink($this->getNewsRefId());
    }
}
