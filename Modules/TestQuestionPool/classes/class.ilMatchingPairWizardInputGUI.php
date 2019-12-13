<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
* This class represents a key value pair wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMatchingPairWizardInputGUI extends ilTextInputGUI
{
    protected $pairs = array();
    protected $allowMove = false;
    protected $terms = array();
    protected $definitions = array();
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->pairs = array();
        $this->terms = array();
        $this->definitions = array();
        if (is_array($a_value)) {
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
            if (is_array($a_value['term'])) {
                foreach ($a_value['term'] as $idx => $term) {
                    array_push($this->pairs, new assAnswerMatchingPair(new assAnswerMatchingTerm('', '', $term), new assAnswerMatchingDefinition('', '', $a_value['definition'][$idx]), $a_value['points'][$idx]));
                }
            }
            $term_ids = explode(",", $a_value['term_id']);
            foreach ($term_ids as $id) {
                array_push($this->terms, new assAnswerMatchingTerm('', '', $id));
            }
            $definition_ids = explode(",", $a_value['definition_id']);
            foreach ($definition_ids as $id) {
                array_push($this->definitions, new assAnswerMatchingDefinition('', '', $id));
            }
        }
    }

    /**
    * Set terms.
    *
    * @param	array	$a_terms	Terms
    */
    public function setTerms($a_terms)
    {
        $this->terms = $a_terms;
    }

    /**
    * Set definitions.
    *
    * @param	array	$a_definitions	Definitions
    */
    public function setDefinitions($a_definitions)
    {
        $this->definitions = $a_definitions;
    }

    /**
    * Set pairs.
    *
    * @param	array	$a_pairs	Pairs
    */
    public function setPairs($a_pairs)
    {
        $this->pairs = $a_pairs;
    }

    /**
    * Set allow move
    *
    * @param	boolean	$a_allow_move Allow move
    */
    public function setAllowMove($a_allow_move)
    {
        $this->allowMove = $a_allow_move;
    }

    /**
    * Get allow move
    *
    * @return	boolean	Allow move
    */
    public function getAllowMove()
    {
        return $this->allowMove;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        }
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['term'])) {
                foreach ($foundvalues['term'] as $val) {
                    if ($this->getRequired() && $val < 1) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
                foreach ($foundvalues['definition'] as $val) {
                    if ($this->getRequired() && $val < 1) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
                $max = 0;
                foreach ($foundvalues['points'] as $val) {
                    if ($val > 0) {
                        $max += $val;
                    }
                    if ($this->getRequired() && (strlen($val)) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
                if ($max <= 0) {
                    $this->setAlert($lng->txt("enter_enough_positive_points"));
                    return false;
                }
            } else {
                if ($this->getRequired()) {
                    $this->setAlert($lng->txt("msg_input_is_required"));
                    return false;
                }
            }
        } else {
            if ($this->getRequired()) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        }
        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $tpl = new ilTemplate("tpl.prop_matchingpairinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;

        foreach ($this->pairs as $pair) {
            $counter = 1;
            $tpl->setCurrentBlock("option_term");
            $tpl->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($lng->txt('please_select')));
            $tpl->setVariable("VALUE_OPTION", 0);
            $tpl->parseCurrentBlock();
            foreach ($this->terms as $term) {
                $tpl->setCurrentBlock("option_term");
                $tpl->setVariable("VALUE_OPTION", ilUtil::prepareFormOutput($term->identifier));
                $tpl->setVariable("TEXT_OPTION", $lng->txt('term') . " " . $counter);
                if ($pair->term->identifier == $term->identifier) {
                    $tpl->setVariable('SELECTED_OPTION', ' selected="selected"');
                }
                $tpl->parseCurrentBlock();
                $counter++;
            }
            $counter = 1;
            $tpl->setCurrentBlock("option_definition");
            $tpl->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($lng->txt('please_select')));
            $tpl->setVariable("VALUE_OPTION", 0);
            $tpl->parseCurrentBlock();
            foreach ($this->definitions as $definition) {
                $tpl->setCurrentBlock("option_definition");
                $tpl->setVariable("VALUE_OPTION", ilUtil::prepareFormOutput($definition->identifier));
                $tpl->setVariable("TEXT_OPTION", $lng->txt('definition') . " " . $counter);
                if ($pair->definition->identifier == $definition->identifier) {
                    $tpl->setVariable('SELECTED_OPTION', ' selected="selected"');
                }
                $tpl->parseCurrentBlock();
                $counter++;
            }

            if (strlen($pair->points)) {
                $tpl->setCurrentBlock('points_value');
                $tpl->setVariable('POINTS_VALUE', $pair->points);
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("ROW_NUMBER", $i);
            
            $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));

            $tpl->setVariable("POST_VAR", $this->getPostVar());

            $tpl->parseCurrentBlock();

            $i++;
        }
        
        $tpl->setCurrentBlock('term_ids');
        $ids = array();
        foreach ($this->terms as $term) {
            array_push($ids, $term->identifier);
        }
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("TERM_IDS", join($ids, ","));
        $tpl->parseCurrentBlock();
        
        $tpl->setCurrentBlock('definition_ids');
        $ids = array();
        foreach ($this->definitions as $definition) {
            array_push($ids, $definition->identifier);
        }
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("DEFINITION_IDS", join($ids, ","));
        $tpl->parseCurrentBlock();
        
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_POINTS", $lng->txt('points'));
        $tpl->setVariable("TEXT_DEFINITION", $lng->txt('definition'));
        $tpl->setVariable("TEXT_TERM", $lng->txt('term'));
        $tpl->setVariable("TEXT_ACTIONS", $lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/matchingpairwizard.js");
    }
}
