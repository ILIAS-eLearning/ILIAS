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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjAccessibilitySettings extends ilObject
{
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "accs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public static function getControlConceptStatus(): bool
    {
        global $DIC;

        $settings = $DIC->settings();

        return (bool) $settings->get('acc_ctrl_cpt_status', '1');
    }

    public static function saveControlConceptStatus(bool $status): void
    {
        global $DIC;

        $settings = $DIC->settings();
        $settings->set('acc_ctrl_cpt_status', (int) $status);
    }
}
