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
 * Class ilMailGlobalAddressSettingsChangedCommand
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMailGlobalAddressSettingsChangedCommand
{
    public function __construct(private ilDBInterface $db, private int $option)
    {
    }

    public function execute(): void
    {
        $this->db->manipulateF(
            "UPDATE settings SET value = %s WHERE module = 'common' AND keyword = 'mail_address_option'",
            ["integer"],
            [$this->option]
        );
        $this->db->manipulateF(
            "UPDATE mail_options SET mail_address_option = %s",
            ["integer"],
            [$this->option]
        );
    }
}
