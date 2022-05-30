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
 * LM presentation (separate toc screen) explorer GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMTableOfContentsExplorerGUI extends ilLMTOCExplorerGUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilLMPresentationService $a_lm_pres,
        string $a_lang = "-"
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_lm_pres, $a_lang);
        $chaps = ilLMObject::_getAllLMObjectsOfLM($this->lm->getId(), $a_type = "st");
        foreach ($chaps as $c) {
            $this->setNodeOpen($c);
        }
    }
}
