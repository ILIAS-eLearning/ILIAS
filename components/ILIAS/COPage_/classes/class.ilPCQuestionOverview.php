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
 * Question overview page content element
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCQuestionOverview extends ilPageContent
{
    public function init(): void
    {
        $this->setType("qover");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode(
            $a_hier_id,
            $a_pc_id,
            "QuestionOverview",
            ["ShortMessage" => "y"]
        );
    }

    /**
     * Set short message
     */
    public function setShortMessage(bool $a_val): void
    {
        $val = ($a_val) ? "y" : null;
        $this->dom_util->setAttribute($this->getChildNode(), "ShortMessage", $val);
    }

    public function getShortMessage(): bool
    {
        if (is_object($this->getChildNode())) {
            if ($this->getChildNode()->getAttribute("ShortMessage") == "y") {
                return true;
            }
        }
        return false;
    }

    public function setListWrongQuestions(bool $a_val): void
    {
        $val = ($a_val) ? "y" : null;
        $this->dom_util->setAttribute($this->getChildNode(), "ListWrongQuestions", $val);
    }

    public function getListWrongQuestions(): bool
    {
        if (is_object($this->getChildNode())) {
            if ($this->getChildNode()->getAttribute("ListWrongQuestions") == "y") {
                return true;
            }
        }
        return false;
    }
}
