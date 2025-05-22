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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\ilTestLegacyFormsHelper;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;

/**
 * This class represents a key value pair wizard property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup    ServicesForm
 */
class ilMatchingPairWizardInputGUI extends ilTextInputGUI
{
    protected $pairs = [];
    protected $allowMove = false;
    protected $terms = [];
    protected $definitions = [];

    protected ilTestLegacyFormsHelper $forms_helper;
    protected GlyphFactory $glyph_factory;
    protected Renderer $renderer;

    /**
     * Constructor
     *
     * @param string $a_title Title
     * @param string $a_postvar Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        global $DIC;

        $this->forms_helper = new ilTestLegacyFormsHelper();
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC->ui()->renderer();
    }

    public function setValue($a_value): void
    {
        $this->pairs = [];
        $this->terms = [];
        $this->definitions = [];

        $to_int = $this->refinery->kindlyTo()->int();
        $points = $this->forms_helper->transformPoints($a_value);
        $terms = $this->forms_helper->transformArray($a_value, 'term', $to_int);
        $definitions = $this->forms_helper->transformArray($a_value, 'definition', $to_int);

        foreach ($terms as $index => $term) {
            $this->pairs[] = new assAnswerMatchingPair(
                new assAnswerMatchingTerm('', '', $term),
                new assAnswerMatchingDefinition('', '', $definitions[$index] ?? 0),
                $points[$index]
            );
        }

        $term_ids = explode(',', $a_value['term_id']);
        foreach ($term_ids as $id) {
            $this->terms[] = new assAnswerMatchingTerm('', '', (int) $id);
        }

        $definition_ids = explode(',', $a_value['definition_id']);
        foreach ($definition_ids as $id) {
            $this->definitions[] = new assAnswerMatchingDefinition('', '', (int) $id);
        }
    }

    /**
     * Set terms.
     *
     * @param array $a_terms Terms
     */
    public function setTerms($a_terms): void
    {
        $this->terms = $a_terms;
    }

    /**
     * Set definitions.
     *
     * @param array $a_definitions Definitions
     */
    public function setDefinitions($a_definitions): void
    {
        $this->definitions = $a_definitions;
    }

    /**
     * Set pairs.
     *
     * @param array $a_pairs Pairs
     */
    public function setPairs($a_pairs): void
    {
        $this->pairs = $a_pairs;
    }

    /**
     * Set allow move
     *
     * @param boolean $a_allow_move Allow move
     */
    public function setAllowMove($a_allow_move): void
    {
        $this->allowMove = $a_allow_move;
    }

    /**
     * Get allow move
     *
     * @return    boolean    Allow move
     */
    public function getAllowMove(): bool
    {
        return $this->allowMove;
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    boolean        Input ok, true/false
     */
    public function checkInput(): bool
    {
        $to_int = $this->refinery->kindlyTo()->int();
        $data = $this->raw($this->getPostVar());

        if (!is_array($data)) {
            $this->setAlert($this->lng->txt('msg_input_is_required'));
            return false;
        }

        // check points
        $result_points = $this->forms_helper->checkPointsInputEnoughPositive($data, true);
        if (!is_array($result_points)) {
            $this->setAlert($this->lng->txt($result_points));
            return false;
        }

        // check answers
        $terms = $this->forms_helper->transformArray($data, 'term', $to_int);
        $definitions = $this->forms_helper->transformArray($data, 'definition', $to_int);
        foreach ([$terms, $definitions] as $value) {
            if ($value < 1 && $this->getRequired()) {
                $this->setAlert($this->lng->txt('msg_input_is_required'));
                return false;
            }
        }

        return $this->checkSubItemsInput();
    }

    /**
     * Insert property html
     * @return    void    Size
     */
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $global_tpl = $DIC['tpl'];
        $global_tpl->addJavascript("assets/js/answerwizardinput.js");
        $global_tpl->addJavascript("assets/js/matchingpairwizard.js");

        $tpl = new ilTemplate("tpl.prop_matchingpairinput.html", true, true, "components/ILIAS/TestQuestionPool");
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
                $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", $this->renderer->render(
                    $this->glyph_factory->up()->withAction('#')
                ));
                $tpl->setVariable("DOWN_BUTTON", $this->renderer->render(
                    $this->glyph_factory->down()->withAction('#')
                ));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("ROW_NUMBER", $i);

            $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
            $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                $this->glyph_factory->add()->withAction('#')
            ));
            $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                $this->glyph_factory->remove()->withAction('#')
            ));

            $tpl->setVariable("POST_VAR", $this->getPostVar());

            $tpl->parseCurrentBlock();

            $i++;
        }

        $tpl->setCurrentBlock('term_ids');
        $ids = [];
        foreach ($this->terms as $term) {
            array_push($ids, $term->getIdentifier());
        }
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("TERM_IDS", join(",", $ids));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('definition_ids');
        $ids = [];
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
