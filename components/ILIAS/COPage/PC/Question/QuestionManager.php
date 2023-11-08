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

namespace ILIAS\COPage\PC\Question;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class QuestionManager
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
    }

    /**
     * Resolve all quesiont references
     * (after import)
     */
    public function resolveQuestionReferences(
        \DOMDocument $dom,
        array $a_mapping
    ): bool {
        $path = "//Question";
        $updated = false;
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $qref = $node->getAttribute("QRef");
            if (isset($a_mapping[$qref])) {
                $node->setAttribute("QRef", "il__qst_" . $a_mapping[$qref]["pool"]);
                $updated = true;
            }
        }
        return $updated;
    }
}
