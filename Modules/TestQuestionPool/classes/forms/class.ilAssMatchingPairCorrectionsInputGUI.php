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
 * Class class.ilAssMatchingPairCorrectionsInputGUI
 *
 * @author  Björn Heyser <info@bjoernheyser.de>
 * @version $Id$
 *
 * @package Modules/Test(QuestionPool)
 */
class ilAssMatchingPairCorrectionsInputGUI extends ilMatchingPairWizardInputGUI
{
    private string $path_including_prefix;

    public function getPairs(): array
    {
        return $this->pairs;
    }

    public function setThumbsWebPathWithPrefix(string $path_including_prefix): void
    {
        $this->path_including_prefix = $path_including_prefix;
    }

    public function setValue($a_value): void
    {
        if (is_array($a_value)) {
            if (is_array($a_value['points'])) {
                foreach ($a_value['points'] as $idx => $term) {
                    $this->pairs[$idx]->withPoints($a_value['points'][$idx]);
                }
            }
        }
    }

    public function checkInput(): bool
    {
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            $max = 0;
            foreach ($foundvalues['points'] as $val) {
                if ($val > 0) {
                    $max += $val;
                }
                if ($this->getRequired() && (strlen($val)) == 0) {
                    $this->setAlert($this->lng->txt("msg_input_is_required"));
                    return false;
                }
            }
            if ($max <= 0) {
                $this->setAlert($this->lng->txt("enter_enough_positive_points"));
                return false;
            }
        } else {
            if ($this->getRequired()) {
                $this->setAlert($this->lng->txt("msg_input_is_required"));
                return false;
            }
        }

        return $this->checkSubItemsInput();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate("tpl.prop_matchingpaircorrection_input.html", true, true, "Modules/TestQuestionPool");
        $i = 0;

        $term_ids = [];
        $definition_ids = [];
        foreach ($this->pairs as $pair) {
            $tpl->setCurrentBlock('row');

            foreach ($this->terms as $term) {
                if ($pair->getTerm()->getIdentifier() !== $term->getIdentifier()) {
                    continue;
                }
                $term_ids[] = $term->getIdentifier();
                if ($term->getText() !== '') {
                    $tpl->setVariable('TERM', ilLegacyFormElementsUtil::prepareTextareaOutput($term->getText(), true, true));
                }
                if ($term->getPicture() !== '') {
                    $tpl->setCurrentBlock('term_image');
                    $tpl->setVariable('THUMBNAIL_HREF', $this->path_including_prefix . $term->getPicture());
                    $tpl->setVariable('THUMB_ALT', $this->lng->txt('image'));
                    $tpl->setVariable('THUMB_TITLE', $this->lng->txt('image'));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock('row');
                }
                break;
            }
            foreach ($this->definitions as $definition) {
                if ($pair->getDefinition()->getIdentifier() !== $definition->getIdentifier()) {
                    continue;
                }
                $definition_ids[] = $definition->getIdentifier();
                if ($definition->getText() !== '') {
                    $tpl->setVariable('DEFINITION', ilLegacyFormElementsUtil::prepareTextareaOutput($definition->getText(), true, true));
                }
                if ($definition->getPicture() !== '') {
                    $tpl->setCurrentBlock('definition_image');
                    $tpl->setVariable('THUMBNAIL_HREF', $this->path_including_prefix . $definition->getPicture());
                    $tpl->setVariable('THUMB_ALT', $this->lng->txt('image'));
                    $tpl->setVariable('THUMB_TITLE', $this->lng->txt('image'));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock('row');
                }
                break;
            }

            $tpl->setVariable('POINTS_VALUE', $pair->getPoints());
            $tpl->setVariable('ROW_NUMBER', $i);

            $tpl->setVariable('ID', $this->getPostVar() . "[$i]");
            $tpl->setVariable('POST_VAR', $this->getPostVar());

            $tpl->parseCurrentBlock();

            $i++;
        }

        $tpl->setCurrentBlock('term_ids');
        $tpl->setVariable('POST_VAR', $this->getPostVar());
        $tpl->setVariable('TERM_IDS', join(',', $term_ids));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('definition_ids');
        $tpl->setVariable('POST_VAR', $this->getPostVar());
        $tpl->setVariable('DEFINITION_IDS', join(',', $definition_ids));
        $tpl->parseCurrentBlock();

        $tpl->setVariable('ELEMENT_ID', $this->getPostVar());
        $tpl->setVariable('TEXT_POINTS', $this->lng->txt('points'));
        $tpl->setVariable('TEXT_DEFINITION', $this->lng->txt('definition'));
        $tpl->setVariable('TEXT_TERM', $this->lng->txt('term'));
        $tpl->setVariable('TEXT_ACTIONS', $this->lng->txt('actions'));

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
