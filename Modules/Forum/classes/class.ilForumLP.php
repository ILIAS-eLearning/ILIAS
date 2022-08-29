<?php

declare(strict_types=1);

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
 * Class ilForumLP
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumLP extends ilObjectLP
{
    public function appendModeConfiguration(int $mode, ilRadioOption $modeElement): void
    {
        global $DIC;

        if (ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION === $mode) {
            $num_postings = new ilNumberInputGUI(
                $DIC->language()->txt('trac_frm_contribution_num_postings'),
                'number_of_postings'
            );
            $num_postings->allowDecimals(false);
            $num_postings->setMinValue(1);
            $num_postings->setMaxValue(99999);
            $num_postings->setSize(4);
            $num_postings->setRequired(true);
            if (is_int(ilForumProperties::getInstance($this->obj_id)->getLpReqNumPostings())) {
                $requiredNumberOfPostings = ilForumProperties::getInstance($this->obj_id)->getLpReqNumPostings();
                $num_postings->setValue((string) $requiredNumberOfPostings);
            }
            $modeElement->addSubItem($num_postings);
        }
    }

    public function saveModeConfiguration(ilPropertyFormGUI $form, bool &$modeChanged): void
    {
        $frm_properties = ilForumProperties::getInstance($this->obj_id);

        $current_value = $frm_properties->getLpReqNumPostings();

        if (is_numeric($form->getInput('number_of_postings'))) {
            $frm_properties->setLpReqNumPostings(
                (int) $form->getInput('number_of_postings')
            );
        } else {
            $frm_properties->setLpReqNumPostings(null);
        }
        $frm_properties->update();

        if ($current_value !== $frm_properties->getLpReqNumPostings()) {
            $modeChanged = true;
        }
    }

    public static function getDefaultModes(bool $lp_active): array
    {
        if (true === $lp_active) {
            return [
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION,
            ];
        }

        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
        ];
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes(): array
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION,
        ];
    }
}
