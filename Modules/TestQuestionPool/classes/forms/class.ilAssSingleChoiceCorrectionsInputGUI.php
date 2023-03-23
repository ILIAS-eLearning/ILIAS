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
 * Class ilAssSingleChoiceCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssSingleChoiceCorrectionsInputGUI extends ilSingleChoiceWizardInputGUI
{
    /**
     * @var assSingleChoice
     */
    protected $qstObject;

    public function setValue($a_value): void
    {
        if (is_array($a_value)) {
            if (is_array($a_value['points'])) {
                foreach ($a_value['points'] as $index => $value) {
                    $this->values[$index]->setPoints($value);
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
            // check points
            $max = 0;
            if (is_array($foundvalues['points'])) {
                foreach ($foundvalues['points'] as $points) {
                    $points = str_replace(',', '.', $points);
                    if ($points > $max) {
                        $max = $points;
                    }
                    if (((strlen($points)) == 0) || (!is_numeric($points))) {
                        $this->setAlert($lng->txt("form_msg_numeric_value_required"));
                        return false;
                    }
                }
            }
            if ($max == 0) {
                $this->setAlert($lng->txt("enter_enough_positive_points"));
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
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $lng = $DIC->language();

        $tpl = new ilTemplate("tpl.prop_singlechoicecorrection_input.html", true, true, "Modules/TestQuestionPool");

        $i = 0;

        if ($this->values === null) {
            $this->values = $this->value;
        }

        foreach ($this->values as $value) {
            if ($this->qstObject->isSingleline()) {
                if (strlen($value->getImage())) {
                    $imagename = $this->qstObject->getImagePathWeb() . $value->getImage();
                    if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                        if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getImage())) {
                            $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getImage();
                        }
                    }

                    $tpl->setCurrentBlock('image');
                    $tpl->setVariable('SRC_IMAGE', $imagename);
                    $tpl->setVariable('IMAGE_NAME', $value->getImage());
                    $tpl->setVariable('ALT_IMAGE', ilLegacyFormElementsUtil::prepareFormOutput($value->getAnswertext()));
                    $tpl->parseCurrentBlock();
                } else {
                    $tpl->setCurrentBlock('image');
                    $tpl->touchBlock('image');
                    $tpl->parseCurrentBlock();
                }
            }

            $tpl->setCurrentBlock("answer");
            $tpl->setVariable("ANSWER", $value->getAnswertext());
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("prop_points_propval");
            $tpl->setVariable("POINTS_POST_VAR", $this->getPostVar());
            $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints()));
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("row");
            $tpl->parseCurrentBlock();
        }

        if ($this->qstObject->isSingleline()) {
            $tpl->setCurrentBlock("image_heading");
            $tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
            $tpl->setVariable("TXT_MAX_SIZE", ilFileUtils::getFileSizeInfo());
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("points_heading");
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
        $tpl->parseCurrentBlock();

        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
