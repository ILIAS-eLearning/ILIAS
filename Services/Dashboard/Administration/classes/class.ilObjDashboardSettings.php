<?php // CR: declare(strict_types=1) is missing

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Dashboard settings
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjDashboardSettings extends ilObject
{
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        $this->type = "dshs";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
