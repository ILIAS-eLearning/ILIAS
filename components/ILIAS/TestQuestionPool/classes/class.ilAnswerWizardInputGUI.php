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

use ILIAS\TestQuestionPool\ilTestLegacyFormsHelper;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;
use ILIAS\UI\Component\Button\Factory as ButtonFactory;

/**
* This class represents a single choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilAnswerWizardInputGUI extends ilTextInputGUI
{
    protected $values = [];
    protected $allowMove = false;
    protected $allowAddRemove = true;
    protected $singleline = true;
    protected $qstObject = null;
    protected $minvalue = false;
    protected $minvalueShouldBeGreater = false;

    protected ilTestLegacyFormsHelper $forms_helper;
    protected GlyphFactory $glyph_factory;
    protected ButtonFactory $button_factory;
    protected Renderer $renderer;

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        global $DIC;

        $this->forms_helper = new ilTestLegacyFormsHelper();
        $this->button_factory = $DIC->ui()->factory()->button();
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC->ui()->renderer();

        $this->setSize('25');
        $this->validationRegexp = "";
    }

    public function setValue($a_value): void
    {
        $this->values = [];

        $answers = $this->forms_helper->transformArray($a_value, 'answer', $this->refinery->kindlyTo()->string());
        $images = $this->forms_helper->transformArray($a_value, 'imagename', $this->refinery->kindlyTo()->string());
        $points = $this->forms_helper->transformPoints($a_value);

        foreach ($answers as $index => $value) {
            $answer = new ASS_AnswerBinaryStateImage(
                $value,
                $points[$index] ?? 0.0,
                $index,
                true,
                null,
                -1
            );
            if ($this->forms_helper->inArray($images, $index)) {
                $answer->setImage($images[$index]);
            }
            $this->values[] = $answer;
        }
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values): void
    {
        $this->values = $a_values;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
    * Set singleline
    *
    * @param	boolean	$a_value	Value
    */
    public function setSingleline($a_value): void
    {
        $this->singleline = $a_value;
    }

    /**
    * Get singleline
    *
    * @return	boolean	Value
    */
    public function getSingleline(): bool
    {
        return $this->singleline;
    }

    /**
    * Set question object
    *
    * @param	object	$a_value	test object
    */
    public function setQuestionObject($a_value): void
    {
        $this->qstObject = &$a_value;
    }

    /**
    * Get question object
    *
    * @return	object	Value
    */
    public function getQuestionObject(): ?object
    {
        return $this->qstObject;
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
     * @return bool
     */
    public function isAddRemoveAllowed(): bool
    {
        return $this->allowAddRemove;
    }

    /**
     * @param bool $allowAddRemove
     */
    public function setAllowAddRemove($allowAddRemove): void
    {
        $this->allowAddRemove = $allowAddRemove;
    }

    /**
     * Set minvalueShouldBeGreater
     *
     * @param	boolean	$a_bool	true if the minimum value should be greater than minvalue
     */
    public function setMinvalueShouldBeGreater($a_bool): void
    {
        $this->minvalueShouldBeGreater = $a_bool;
    }

    /**
     * Get minvalueShouldBeGreater
     *
     * @return	boolean	true if the minimum value should be greater than minvalue
     */
    public function minvalueShouldBeGreater(): bool
    {
        return $this->minvalueShouldBeGreater;
    }
    /**
     * Set Minimum Value.
     *
     * @param	float	$a_minvalue	Minimum Value
     */
    public function setMinValue($a_minvalue): void
    {
        $this->minvalue = $a_minvalue;
    }

    /**
     * Get Minimum Value.
     *
     * @return	float	Minimum Value
     */
    public function getMinValue()
    {
        return $this->minvalue;
    }
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        $data = $this->raw($this->getPostVar());

        if (!is_array($data)) {
            $this->setAlert($this->lng->txt('msg_input_is_required'));
            return false;
        }

        // check points
        $points = $this->forms_helper->checkPointsInputEnoughPositive($data, true);
        if (!is_array($points)) {
            $this->setAlert($this->lng->txt($points));
            return false;
        }
        foreach ($points as $value) {
            if ($value !== 0.0 && $this->getMinValue() !== false) {
                if (($this->minvalueShouldBeGreater() && $points <= $this->getMinValue()) ||
                    (!$this->minvalueShouldBeGreater() && $points < $this->getMinValue())) {
                    $this->setAlert($this->lng->txt('form_msg_value_too_low'));
                    return false;
                }
            }
        }

        // check answers
        $answers = $this->forms_helper->transformArray($data, 'answer', $this->refinery->kindlyTo()->string());
        foreach ($answers as $answer) {
            if ($answer === '') {
                $this->setAlert($this->lng->txt('msg_input_is_required'));
                return false;
            }
        }

        return $this->checkSubItemsInput();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate($this->getTemplate(), true, true, "components/ILIAS/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext())
                    );
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                    );
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('singleline');
                $tpl->setVariable("SIZE", $this->getSize());
                $tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
                $tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            } elseif (!$this->getSingleline()) {
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                    );
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('multiline');
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareTextareaOutput($value->getAnswertext()));
                $tpl->setVariable("MULTILINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("MULTILINE_ROW_NUMBER", $i);
                $tpl->setVariable("MULTILINE_POST_VAR", $this->getPostVar());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_MULTILINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", $this->renderer->render(
                    $this->button_factory->shy('', '')->withSymbol($this->glyph_factory->up())
                ));
                $tpl->setVariable("DOWN_BUTTON", $this->renderer->render(
                    $this->button_factory->shy('', '')->withSymbol($this->glyph_factory->down())
                ));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
            if ($this->isAddRemoveAllowed()) {
                $tpl->setVariable("ADD_REMOVE_ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                    $this->button_factory->shy('', '')->withSymbol($this->glyph_factory->add())
                ));
                $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                    $this->button_factory->shy('', '')->withSymbol($this->glyph_factory->remove())
                ));
            }
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
            }
            $tpl->parseCurrentBlock();
            $i++;
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $this->getTextInputLabel($this->lng));
        $tpl->setVariable("POINTS_TEXT", $this->getPointsInputLabel($this->lng));
        $tpl->setVariable("COMMANDS_TEXT", $this->lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("assets/js/answerwizardinput.js");
        $tpl->addJavascript("assets/js/answerwizard.js");
    }

    /**
     * @param $lng
     * @return mixed
     */
    protected function getTextInputLabel($lng)
    {
        return $lng->txt('answer_text');
    }

    /**
     * @param $lng
     * @return mixed
     */
    protected function getPointsInputLabel($lng)
    {
        return $lng->txt('points');
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return "tpl.prop_answerwizardinput.html";
    }

    protected function prepareFormOutput(float|string $input, bool $strip = false): string
    {
        $input_string = $this->refinery->kindlyTo()->string()->transform($input);
        return str_replace(
            ['{', '}', '\\'],
            ['&#123;', '&#125;', '&#92;'],
            htmlspecialchars(
                $strip ? ilUtil::stripSlashes($input_string) : $input_string,
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
                null,
                false
            )
        );
    }
}
