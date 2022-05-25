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
    public php4DOMElement $qover_node;

    public function init() : void
    {
        $this->setType("qover");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->qover_node = $a_node->first_child();		// this is the question overview node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->qover_node = $this->dom->create_element("QuestionOverview");
        $this->qover_node = $this->node->append_child($this->qover_node);
        $this->qover_node->set_attribute("ShortMessage", "y");
    }

    /**
     * Set short message
     */
    public function setShortMessage(bool $a_val) : void
    {
        if ($a_val) {
            $this->qover_node->set_attribute("ShortMessage", "y");
        } else {
            if ($this->qover_node->has_attribute("ShortMessage")) {
                $this->qover_node->remove_attribute("ShortMessage");
            }
        }
    }

    public function getShortMessage() : bool
    {
        if (is_object($this->qover_node)) {
            if ($this->qover_node->get_attribute("ShortMessage") == "y") {
                return true;
            }
        }
        return false;
    }
    
    public function setListWrongQuestions(bool $a_val) : void
    {
        if ($a_val) {
            $this->qover_node->set_attribute("ListWrongQuestions", "y");
        } else {
            if ($this->qover_node->has_attribute("ListWrongQuestions")) {
                $this->qover_node->remove_attribute("ListWrongQuestions");
            }
        }
    }

    public function getListWrongQuestions() : bool
    {
        if (is_object($this->qover_node)) {
            if ($this->qover_node->get_attribute("ListWrongQuestions") == "y") {
                return true;
            }
        }
        return false;
    }
}
