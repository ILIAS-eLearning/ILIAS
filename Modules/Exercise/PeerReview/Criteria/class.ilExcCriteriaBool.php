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
 * Class ilExcCriteriaBool
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCriteriaBool extends ilExcCriteria
{
    public function getType(): string
    {
        return "bool";
    }


    // PEER REVIEW

    public function addToPeerReviewForm($a_value = null): void
    {
        $lng = $this->lng;

        if (!$this->isRequired()) {
            $input = new ilCheckboxInputGUI($this->getTitle(), "prccc_bool_" . $this->getId());
            $input->setInfo($this->getDescription());
            $input->setRequired($this->isRequired());
            $input->setChecked($a_value > 0);
        } else {
            $input = new ilSelectInputGUI($this->getTitle(), "prccc_bool_" . $this->getId());
            $input->setInfo($this->getDescription());
            $input->setRequired($this->isRequired());
            $input->setValue($a_value);
            $options = array();
            if (!$a_value) {
                $options[""] = $lng->txt("please_select");
            }
            $options[1] = $lng->txt("yes");
            $options[-1] = $lng->txt("no");
            $input->setOptions($options);
        }
        $this->form->addItem($input);
    }

    public function importFromPeerReviewForm(): int
    {
        return (int) $this->form->getInput("prccc_bool_" . $this->getId());
    }

    public function hasValue($a_value): int
    {
        return (int) $a_value;
    }

    public function getHTML($a_value): string
    {
        $lng = $this->lng;

        $caption = null;
        if ($this->isRequired() && $a_value < 0) {
            $caption = $lng->txt("no");
        } elseif ($a_value == 1) {
            $caption = $lng->txt("yes");
        }
        return $caption;
    }
}
