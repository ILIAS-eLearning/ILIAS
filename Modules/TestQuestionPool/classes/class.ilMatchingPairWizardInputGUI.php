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
* This class represents a key value pair wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMatchingPairWizardInputGUI extends ilTextInputGUI
{
    protected $pairs = [];
    protected $allowMove = false;
    protected $terms = [];
    protected $definitions = [];

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

    public function setValue($a_value): void
    {
        $this->pairs = array();
        $this->terms = array();
        $this->definitions = array();
        if (is_array($a_value)) {
            if (isset($a_value['term']) && is_array($a_value['term'])) {
                foreach ($a_value['term'] as $idx => $term) {
                    $this->pairs[] = new assAnswerMatchingPair(
                        new assAnswerMatchingTerm('', '', $term),
                        new assAnswerMatchingDefinition('', '', $a_value['definition'][$idx]),
                        (float) $a_value['points'][$idx]
                    );
                }
            }
            $term_ids = explode(",", $a_value['term_id']);
            foreach ($term_ids as $id) {
                $this->terms[] = new assAnswerMatchingTerm('', '', $id);
            }
            $definition_ids = explode(",", $a_value['definition_id']);
            foreach ($definition_ids as $id) {
                $this->definitions[] = new assAnswerMatchingDefinition('', '', $id);
            }
        }
    }

    /**
    * Set terms.
    *
    * @param	array	$a_terms	Terms
    */
    public function setTerms($a_terms): void
    {
        $this->terms = $a_terms;
    }

    /**
    * Set definitions.
    *
    * @param	array	$a_definitions	Definitions
    */
    public function setDefinitions($a_definitions): void
    {
        $this->definitions = $a_definitions;
    }

    /**
    * Set pairs.
    *
    * @param	array	$a_pairs	Pairs
    */
    public function setPairs($a_pairs): void
    {
        $this->pairs = $a_pairs;
    }

    /**
    * Set allow move
    *
    * @param	boolean	$a_allow_move Allow move
    */
    public function setAllowMove($a_allow_move): void
    {
        $this->allowMove = $a_allow_move;
    }

    /**
    * Get allow move
    *
    * @return	boolean	Allow move
    */
    public function getAllowMove(): bool
    {
        return $this->allowMove;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        global $DIC;
        $lng = $DIC['lng'];

        if (is_array($_POST[$this->getPostVar()])) {
            $foundvalues = ilArrayUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        } else {
            $foundvalues = $_POST[$this->getPostVar()];
        }
        if (is_array($foundvalues)) {
            // check answers
            if (isset($foundvalues['term']) && is_array($foundvalues['term'])) {
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
                    if ($this->getRequired() && (strlen($val)) === 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                    $val = str_replace(",", ".", $val);
                    if (!is_numeric($val)) {
                        $this->setAlert($lng->txt("form_msg_numeric_value_required"));
                        return false;
                    }

                    $val = (float) $val;
                    if ($val > 0) {
                        $max += $val;
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
    * @return	void	Size
    */
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $global_tpl = $DIC['tpl'];
        $global_tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $global_tpl->addJavascript("./Modules/TestQuestionPool/templates/default/matchingpairwizard.js");

        $tpl = new ilTemplate("tpl.prop_matchingpairinput.html", true, true, "Modules/TestQuestionPool");
        $i = 0;

        foreach ($this->pairs as $pair) {
            $counter = 1;
            $tpl->setCurrentBlock("option_term");
            $tpl->setVariable("TEXT_OPTION", ilLegacyFormElementsUtil::prepareFormOutput($lng->txt('please_select')));
            $tpl->setVariable("VALUE_OPTION", 0);
            $tpl->parseCurrentBlock();
            foreach ($this->terms as $term) {
                $tpl->setCurrentBlock("option_term");
                $tpl->setVariable("VALUE_OPTION", ilLegacyFormElementsUtil::prepareFormOutput($term->getIdentifier()));
                $tpl->setVariable("TEXT_OPTION", $lng->txt('term') . " " . $counter);
                if ($pair->getTerm()->getIdentifier() == $term->getIdentifier()) {
                    $tpl->setVariable('SELECTED_OPTION', ' selected="selected"');
                }
                $tpl->parseCurrentBlock();
                $counter++;
            }
            $counter = 1;
            $tpl->setCurrentBlock("option_definition");
            $tpl->setVariable("TEXT_OPTION", ilLegacyFormElementsUtil::prepareFormOutput($lng->txt('please_select')));
            $tpl->setVariable("VALUE_OPTION", 0);
            $tpl->parseCurrentBlock();
            foreach ($this->definitions as $definition) {
                $tpl->setCurrentBlock("option_definition");
                $tpl->setVariable("VALUE_OPTION", ilLegacyFormElementsUtil::prepareFormOutput($definition->getIdentifier()));
                $tpl->setVariable("TEXT_OPTION", $lng->txt('definition') . " " . $counter);
                if ($pair->getDefinition()->getIdentifier() == $definition->getIdentifier()) {
                    $tpl->setVariable('SELECTED_OPTION', ' selected="selected"');
                }
                $tpl->parseCurrentBlock();
                $counter++;
            }


            $tpl->setCurrentBlock('points_value');
            $tpl->setVariable('POINTS_VALUE', $pair->getPoints());
            $tpl->parseCurrentBlock();

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
            array_push($ids, $term->getIdentifier());
        }
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("TERM_IDS", join(",", $ids));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('definition_ids');
        $ids = array();
        foreach ($this->definitions as $definition) {
            array_push($ids, $definition->getIdentifier());
        }
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("DEFINITION_IDS", join(",", $ids));
        $tpl->parseCurrentBlock();

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_POINTS", $lng->txt('points'));
        $tpl->setVariable("TEXT_DEFINITION", $lng->txt('definition'));
        $tpl->setVariable("TEXT_TERM", $lng->txt('term'));
        $tpl->setVariable("TEXT_ACTIONS", $lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
