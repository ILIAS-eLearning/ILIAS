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

class ilEssayKeywordWizardInputGUI extends ilSingleChoiceWizardInputGUI
{
    public function setValue($a_value): void
    {
        $answers = $this->forms_helper->transformArray($a_value, 'answer', $this->refinery->kindlyTo()->string());
        $points = $this->forms_helper->transformPoints($a_value, 'points');
        $points_unchecked = $this->forms_helper->transformPoints($a_value, 'points_unchecked');

        $this->values = [];
        foreach ($answers as $index => $value) {
            $answer = new ASS_AnswerMultipleResponseImage(
                $value,
                $points[$index] ?? 0.0,
                $index,
                $points_unchecked[$index] ?? 0.0
            );
            $this->values[] = $answer;
        }
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    boolean        Input ok, true/false
     */
    public function checkInput(): bool
    {
        $data = $this->raw($this->getPostVar());

        if (!is_array($data)) {
            $this->setAlert($this->lng->txt('msg_input_is_required'));
            return false;
        }

        // check answers
        $answers = $this->checkAnswersInput($data);
        if (!is_array($answers)) {
            $this->setAlert($this->lng->txt($answers));
            return false;
        }

        // check points
        $result = $this->forms_helper->checkPointsInputEnoughPositive($data, true);
        if (!is_array($result)) {
            $this->setAlert($this->lng->txt($result));
            return false;
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

        $tpl = new ilTemplate("tpl.prop_essaykeywordswizardinput.html", true, true, "components/ILIAS/TestQuestionPool");
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
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getPointsChecked())
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
            } else {
                if (!$this->getSingleline()) {
                    if (is_object($value)) {
                        $tpl->setCurrentBlock("prop_points_propval");
                        $tpl->setVariable(
                            "PROPERTY_VALUE",
                            ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints())
                        );
                        $tpl->parseCurrentBlock();
                    }
                }
            }

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            $tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
            }
            $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                $this->glyph_factory->add()->withAction('#')
            ));
            $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                $this->glyph_factory->remove()->withAction('#')
            ));
            $tpl->parseCurrentBlock();
            $i++;
        }

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
        $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));
        $tpl->setVariable("POINTS_CHECKED_TEXT", $lng->txt('checkbox_checked'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("assets/js/answerwizardinput.js");
        $tpl->addJavascript("assets/js/essaykeywordwizard.js");
    }
}
