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
 * Class ilKprimChoiceCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilKprimChoiceCorrectionsInputGUI extends ilKprimChoiceWizardInputGUI
{
    public function setValue($a_value): void
    {
        if (is_array($a_value) && is_array($a_value['correctness'])) {
            foreach ($this->values as $index => $value) {
                if (isset($a_value['correctness'][$index])) {
                    $this->values[$index]->setCorrectness((bool) $a_value['correctness'][$index]);
                } else {
                    $this->values[$index]->setCorrectness(null);
                }
            }
        }
    }

    public function checkInput(): bool
    {
        global $DIC;
        $lng = $DIC['lng'];

        $foundvalues = $_POST[$this->getPostVar()];

        if (is_array($foundvalues)) {
            if (!isset($foundvalues['correctness']) || count($foundvalues['correctness']) < count($this->values)) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        return $this->checkSubItemsInput();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate("tpl.prop_kprimchoicecorrection_input.html", true, true, "Modules/TestQuestionPool");

        foreach ($this->values as $value) {
            /**
             * @var ilAssKprimChoiceAnswer $value
             */

            if (strlen($value->getImageFile())) {
                $imagename = $value->getImageWebPath();

                if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                    if (@file_exists($value->getThumbFsPath())) {
                        $imagename = $value->getThumbWebPath();
                    }
                }

                $tpl->setCurrentBlock('image');
                $tpl->setVariable('SRC_IMAGE', $imagename);
                $tpl->setVariable('IMAGE_NAME', $value->getImageFile());
                $tpl->setVariable('ALT_IMAGE', ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext()));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("row");

            $tpl->setVariable("ANSWER", $value->getAnswertext());

            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $value->getPosition());
            $tpl->setVariable("ID", $this->getPostVar() . "[answer][{$value->getPosition()}]");

            $tpl->setVariable(
                "CORRECTNESS_TRUE_ID",
                $this->getPostVar() . "[correctness][{$value->getPosition()}][true]"
            );
            $tpl->setVariable(
                "CORRECTNESS_FALSE_ID",
                $this->getPostVar() . "[correctness][{$value->getPosition()}][false]"
            );
            $tpl->setVariable("CORRECTNESS_TRUE_VALUE", 1);
            $tpl->setVariable("CORRECTNESS_FALSE_VALUE", 0);

            if ($value->getCorrectness() !== null) {
                if ($value->getCorrectness()) {
                    $tpl->setVariable('CORRECTNESS_TRUE_SELECTED', ' checked="checked"');
                } else {
                    $tpl->setVariable('CORRECTNESS_FALSE_SELECTED', ' checked="checked"');
                }
            }

            if ($this->getDisabled()) {
                $tpl->setVariable("DISABLED_CORRECTNESS", " disabled=\"disabled\"");
            }

            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("TRUE_TEXT", $this->qstObject->getTrueOptionLabelTranslation($this->lng, $this->qstObject->getOptionLabel()));
        $tpl->setVariable("FALSE_TEXT", $this->qstObject->getFalseOptionLabelTranslation($this->lng, $this->qstObject->getOptionLabel()));

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("DELETE_IMAGE_HEADER", $this->lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $this->lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $this->lng->txt('answer'));

        $tpl->setVariable("OPTIONS_TEXT", $this->lng->txt('options'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
