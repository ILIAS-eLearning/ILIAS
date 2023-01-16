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

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Helper class for local user accounts (in categories)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserProfilePictureStakeholder extends AbstractResourceStakeholder
{
    private int $default_owner;

    public function __construct()
    {
        global $DIC;
        $this->default_owner = $DIC->isDependencyAvailable('user')
            ? $DIC->user()->getId()
            : (int) SYSTEM_USER_ID;
    }


    public function getId(): string
    {
        return 'usr_picture';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->default_owner;
    }
}
