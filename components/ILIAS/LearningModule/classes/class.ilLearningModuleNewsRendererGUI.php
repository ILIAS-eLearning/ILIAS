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

/**
 * Learning Module news renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    public function getObjectLink(): string
    {
        $n = $this->getNewsItem();
        if ($n->getContextSubObjType() == "pg"
            && $n->getContextSubObjId() > 0) {
            //$add = "&target=pg_".$n->getContextSubObjId()."_".$this->getNewsRefId();
            return ilLink::_getLink($n->getContextSubObjId() . "_" . $this->getNewsRefId(), "pg");
        }
        return ilLink::_getLink($this->getNewsRefId());
    }
}
