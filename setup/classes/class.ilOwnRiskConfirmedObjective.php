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

use ILIAS\Setup;

class ilOwnRiskConfirmedObjective extends Setup\Objective\AdminConfirmedObjective
{
    public function __construct()
    {
        parent::__construct(
            "Please note that you are running this program at your own risk and with\n" .
            "absolutely no warranty. You should perform regular backups and thorough\n" .
            "testing to prevent data loss or unpleasant surprises when running this\n" .
            "program. Are you fine with this?"
        );
    }
}
