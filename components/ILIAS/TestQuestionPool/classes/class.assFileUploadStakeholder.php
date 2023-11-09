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
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class assFileUploadStakeholder extends AbstractResourceStakeholder
{
    private int $current_user;

    public function __construct()
    {
        global $DIC;
        $anonymous = defined(
            'ANONYMOUS_USER_ID'
        ) ? ANONYMOUS_USER_ID : 13;
        $this->current_user = (int) ($DIC->isDependencyAvailable('user') ? $DIC->user()->getId() : $anonymous);
    }

    public function getId(): string
    {
        return 'qpl_file_upload';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->current_user;
    }

}
