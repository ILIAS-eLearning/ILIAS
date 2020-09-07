<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Question overview page content element
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCQuestionOverview extends ilPageContent
{
    public $dom;
    public $qover_node;

    /**
     * Init page content component.
     */
    public function init()
    {
        $this->setType("qover");
    }

    /**
     * Set node
     */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->qover_node = $a_node->first_child();		// this is the question overview node
    }

    /**
     * Create question overview node in xml.
     *
     * @param	object	$a_pg_obj		Page Object
     * @param	string	$a_hier_id		Hierarchical ID
     */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->qover_node = $this->dom->create_element("QuestionOverview");
        $this->qover_node = $this->node->append_child($this->qover_node);
        $this->qover_node->set_attribute("ShortMessage", "y");
    }

    /**
     * Set short message
     *
     * @param boolean $a_val t/f
     */
    public function setShortMessage($a_val)
    {
        if ($a_val) {
            $this->qover_node->set_attribute("ShortMessage", "y");
        } else {
            if ($this->qover_node->has_attribute("ShortMessage")) {
                $this->qover_node->remove_attribute("ShortMessage");
            }
        }
    }

    /**
     * Get short message
     *
     * @return boolean
     */
    public function getShortMessage()
    {
        if (is_object($this->qover_node)) {
            if ($this->qover_node->get_attribute("ShortMessage") == "y") {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Set list wrong questions
     *
     * @param boolean $a_val t/f
     */
    public function setListWrongQuestions($a_val)
    {
        if ($a_val) {
            $this->qover_node->set_attribute("ListWrongQuestions", "y");
        } else {
            if ($this->qover_node->has_attribute("ListWrongQuestions")) {
                $this->qover_node->remove_attribute("ListWrongQuestions");
            }
        }
    }

    /**
     * Get list wrong questions
     *
     * @return boolean
     */
    public function getListWrongQuestions()
    {
        if (is_object($this->qover_node)) {
            if ($this->qover_node->get_attribute("ListWrongQuestions") == "y") {
                return true;
            }
        }
        return false;
    }
}
