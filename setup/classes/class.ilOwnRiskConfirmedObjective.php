<?php

declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
