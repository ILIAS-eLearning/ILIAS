<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailGlobalAddressSettingsChangedCommand
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMailGlobalAddressSettingsChangedCommand
{
    private ilDBInterface $db;
    private int $option;

    public function __construct(ilDBInterface $db, int $option)
    {
        $this->db = $db;
        $this->option = $option;
    }

    public function execute() : void
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
