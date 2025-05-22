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

declare(strict_types=1);

use ILIAS\UI\Component\Input\Input;

/**
 * Class ilForumLP
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumLP extends ilObjectLP
{
    public function appendModeConfiguration(int $mode): array
    {
        global $DIC;

        if (ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION !== $mode) {
            return [];
        }

        $ui_factory = $DIC->ui()->factory();
        $refinery = $DIC->refinery();
        $lng = $DIC->language();

        $num_postings = $ui_factory->input()->field()->numeric(
            $lng->txt('trac_frm_contribution_num_postings')
        )->withAdditionalTransformation(
            $refinery->in()->series([
                $refinery->int()->isGreaterThanOrEqual(1),
                $refinery->int()->isLessThanOrEqual(99999)
            ])
        )->withRequired(true);

        if (is_int(ilForumProperties::getInstance($this->obj_id)->getLpReqNumPostings())) {
            $requiredNumberOfPostings = ilForumProperties::getInstance($this->obj_id)->getLpReqNumPostings();
            $num_postings = $num_postings->withValue($requiredNumberOfPostings);
        }

        return ['number_of_postings' => $num_postings];
    }

    public function saveModeConfiguration(
        string $selected_group,
        array $group_data,
        bool &$modeChanged
    ): void {
        $frm_properties = ilForumProperties::getInstance($this->obj_id);

        $current_value = $frm_properties->getLpReqNumPostings();

        if (is_numeric($group_data['number_of_postings'] ?? null)) {
            $frm_properties->setLpReqNumPostings(
                (int) $group_data['number_of_postings']
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
        if ($lp_active) {
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

    public function getModeText(int $mode): string
    {
        global $DIC;

        $text = parent::getModeText($mode);

        if ($mode === ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION &&
            $mode === $this->getCurrentMode() &&
            is_int(($number_of_postings = ilForumProperties::getInstance($this->obj_id)->getLpReqNumPostings()))) {
            try {
                $text = sprintf(
                    match ($number_of_postings) {
                        1 => $DIC->language()->txt('trac_frm_contribution_num_postings_info_s'),
                        default => $DIC->language()->txt('trac_frm_contribution_num_postings_info_p')
                    },
                    $text,
                    $number_of_postings
                );
            } catch (Throwable) {
            }
        }

        return $text;
    }
}
