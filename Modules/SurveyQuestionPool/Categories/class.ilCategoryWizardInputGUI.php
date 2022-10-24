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
 * This class represents a survey question category wizard property
 * in a property form.
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilCategoryWizardInputGUI extends ilTextInputGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ?SurveyCategories $values = null;
    protected bool $allowMove = false;
    protected bool $disabled_scale = true;
    protected bool $show_wizard = false;
    protected bool $show_save_phrase = false;
    protected string $categorytext;
    protected bool $show_neutral_category = false;
    protected string $neutral_category_title;
    protected bool $use_other_answer;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);

        $this->show_wizard = false;
        $this->show_save_phrase = false;
        $this->categorytext = $lng->txt('answer');
        $this->use_other_answer = false;

        $this->setMaxLength(1000); // #6218
    }

    public function getUseOtherAnswer(): bool
    {
        return $this->use_other_answer;
    }

    public function setUseOtherAnswer(bool $a_value): void
    {
        $this->use_other_answer = $a_value;
    }

    public function getCategoryCount(): int
    {
        if (!is_object($this->values)) {
            return 0;
        }
        return $this->values->getCategoryCount();
    }

    protected function calcNeutralCategoryScale(): int
    {
        if (is_object($this->values)) {
            $scale = 0;
            for ($i = 0; $i < $this->values->getCategoryCount(); $i++) {
                $cat = $this->values->getCategory($i);
                if ($cat->neutral == 0) {
                    $scale += 1;
                }
            }
            return $scale + 1;
        }

        return 99;
    }

    public function setShowNeutralCategory(bool $a_value): void
    {
        $this->show_neutral_category = $a_value;
    }

    public function getShowNeutralCategory(): bool
    {
        return $this->show_neutral_category;
    }

    public function setNeutralCategoryTitle(string $a_title): void
    {
        $this->neutral_category_title = $a_title;
    }

    public function getNeutralCategoryTitle(): string
    {
        return $this->neutral_category_title;
    }

    /**
     * @param array|string $a_value
     */
    public function setValue($a_value): void
    {
        $this->values = new SurveyCategories();
        if (is_array($a_value)) {
            if (is_array($a_value['answer'])) {
                foreach ($a_value['answer'] as $index => $value) {
                    $this->values->addCategory($value, $a_value['other'][$index] ?? 0, 0, null, $a_value['scale'][$index] ?? null);
                }
            }
        }
        if (array_key_exists('neutral', $a_value)) {
            $scale = $this->str($this->postvar . '_neutral_scale');
            $scale = ($scale === "")
                ? null
                : (int) $scale;
            $this->values->addCategory(
                $a_value['neutral'],
                0,
                1,
                null,
                $scale
            );
        }
    }

    public function setValues(SurveyCategories $a_values): void
    {
        $this->values = $a_values;
    }

    public function getValues(): SurveyCategories
    {
        return $this->values;
    }

    public function setAllowMove(bool $a_allow_move): void
    {
        $this->allowMove = $a_allow_move;
    }

    public function getAllowMove(): bool
    {
        return $this->allowMove;
    }

    public function setShowWizard(bool $a_value): void
    {
        $this->show_wizard = $a_value;
    }

    public function getShowWizard(): bool
    {
        return $this->show_wizard;
    }

    public function setCategoryText(string $a_text): void
    {
        $this->categorytext = $a_text;
    }

    public function getCategoryText(): string
    {
        return $this->categorytext;
    }

    public function setShowSavePhrase(bool $a_value): void
    {
        $this->show_save_phrase = $a_value;
    }

    public function getShowSavePhrase(): bool
    {
        return $this->show_save_phrase;
    }

    public function getDisabledScale(): bool
    {
        return $this->disabled_scale;
    }

    public function setDisabledScale(bool $a_value): void
    {
        $this->disabled_scale = $a_value;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;
        $foundvalues = $this->getInput();
        $neutral_scale = $this->getNeutralScaleInput();
        $neutral = $this->getNeutralInput();

        if (count($foundvalues) > 0) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $idx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && ($this->getRequired() && (!isset($foundvalues['other'][$idx])))) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
            // check neutral column
            /*            see #33267
                           if (array_key_exists('neutral', $foundvalues)) {
                            if ((strlen($neutral) == 0) && ($this->getRequired())) {
                                $this->setAlert($lng->txt("msg_input_is_required"));
                                return false;
                            }
                        }*/
            // check scales
            if (isset($foundvalues['scale'])) {
                foreach ($foundvalues['scale'] as $scale) {
                    //scales required
                    if ((strlen($scale)) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                    //scales positive number
                    if (!ctype_digit($scale) || $scale <= 0) {
                        $this->setAlert($lng->txt("msg_input_only_positive_numbers"));
                        return false;
                    }
                }
                //scales no duplicates.
                if (count(array_unique($foundvalues['scale'])) !== count($foundvalues['scale'])) {
                    $this->setAlert($lng->txt("msg_duplicate_scale"));
                    return false;
                }
            }

            // check neutral column scale
            if ($neutral_scale != "") {
                if (is_array($foundvalues['scale'])) {
                    if (in_array($neutral_scale, $foundvalues['scale'])) {
                        $this->setAlert($lng->txt("msg_duplicate_scale"));
                        return false;
                    }
                }
            }
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }

    public function getInput(): array
    {
        $val = $this->arrayArray($this->getPostVar());
        $val = ilArrayUtil::stripSlashesRecursive($val);
        return $val;
    }

    public function getNeutralScaleInput(): string
    {
        return $this->str($this->getPostVar() . '_neutral_scale');
    }

    public function getNeutralInput(): string
    {
        $val = $this->strArray($this->getPostVar());
        return $val["neutral"];
    }

    public function insert(
        ilTemplate $a_tpl
    ): void {
        $lng = $this->lng;

        $neutral_category = null;
        $tpl = new ilTemplate("tpl.prop_categorywizardinput.html", true, true, "Modules/SurveyQuestionPool");
        if (is_object($this->values)) {
            for ($i = 0; $i < $this->values->getCategoryCount(); $i++) {
                $cat = $this->values->getCategory($i);
                if (!$cat->neutral) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_scale_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($this->values->getScale($i))
                    );
                    $tpl->parseCurrentBlock();

                    if ($this->getUseOtherAnswer()) {
                        $tpl->setCurrentBlock("other_answer_checkbox");
                        $tpl->setVariable("POST_VAR", $this->getPostVar());
                        $tpl->setVariable("OTHER_ID", $this->getPostVar() . "[other][$i]");
                        $tpl->setVariable("ROW_NUMBER", $i);
                        if ($cat->other) {
                            $tpl->setVariable("CHECKED_OTHER", ' checked="checked"');
                        }
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
                    $tpl->setVariable("POST_VAR", $this->getPostVar());
                    $tpl->setVariable("ROW_NUMBER", $i);
                    $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
                    $tpl->setVariable("SIZE", $this->getSize());
                    $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                    if ($this->getDisabled()) {
                        $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
                    }

                    $tpl->setVariable("SCALE_ID", $this->getPostVar() . "[scale][$i]");
                    if ($this->getDisabledScale()) {
                        $tpl->setVariable("DISABLED_SCALE", " disabled=\"disabled\"");
                    }

                    $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
                    $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                    $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                    $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
                    $tpl->parseCurrentBlock();
                } else {
                    $neutral_category = $cat;
                }
            }
        }

        if ($this->getShowWizard()) {
            $tpl->setCurrentBlock("wizard");
            $tpl->setVariable("CMD_WIZARD", 'cmd[addPhrase]');
            $tpl->setVariable("WIZARD_BUTTON", ilUtil::getImagePath('wizard.svg'));
            $tpl->setVariable("WIZARD_TEXT", $lng->txt('add_phrase'));
            $tpl->parseCurrentBlock();
        }

        if ($this->getShowSavePhrase()) {
            $tpl->setCurrentBlock('savephrase');
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("VALUE_SAVE_PHRASE", $lng->txt('save_phrase'));
            $tpl->parseCurrentBlock();
        }

        if ($this->getShowNeutralCategory()) {
            if (is_object($neutral_category) && strlen($neutral_category->title)) {
                $tpl->setCurrentBlock("prop_text_neutral_propval");
                $tpl->setVariable(
                    "PROPERTY_VALUE",
                    ilLegacyFormElementsUtil::prepareFormOutput($neutral_category->title)
                );
                $tpl->parseCurrentBlock();
            }
            if ($this->getNeutralCategoryTitle() !== '') {
                $tpl->setCurrentBlock("neutral_category_title");
                $tpl->setVariable("NEUTRAL_COLS", ($this->getUseOtherAnswer()) ? 4 : 3);
                $tpl->setVariable(
                    "CATEGORY_TITLE",
                    ilLegacyFormElementsUtil::prepareFormOutput($this->getNeutralCategoryTitle())
                );
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("prop_scale_neutral_propval");
            $scale = (is_object($neutral_category) && $neutral_category->scale > 0) ? $neutral_category->scale : $this->values->getNewScale();
            $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($scale));
            $tpl->parseCurrentBlock();

            if ($this->getUseOtherAnswer()) {
                $tpl->touchBlock('other_answer_neutral');
            }

            $tpl->setCurrentBlock('neutral_row');
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ID", $this->getPostVar() . "_neutral");
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
            }
            $tpl->setVariable("SCALE_ID", $this->getPostVar() . "_neutral_scale");
            if ($this->getDisabledScale()) {
                $tpl->setVariable("DISABLED_SCALE", " disabled=\"disabled\"");
            }
            $tpl->parseCurrentBlock();
        }

        if ($this->getUseOtherAnswer()) {
            $tpl->setCurrentBlock('other_answer_title');
            $tpl->setVariable("OTHER_TEXT", $lng->txt('use_other_answer'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $this->getCategoryText());
        $tpl->setVariable("SCALE_TEXT", $lng->txt('scale'));
        $tpl->setVariable("ACTIONS_TEXT", $lng->txt('actions'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        $tpl = $this->tpl;
        $tpl->addJavaScript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavaScript("./Modules/SurveyQuestionPool/Categories/js/categorywizard.js");
    }
}
