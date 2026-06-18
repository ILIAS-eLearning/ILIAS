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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class ilMailAttachmentStakeholder extends AbstractResourceStakeholder
{
    public function getId(): string
    {
        return 'mail_attachments';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->default_owner;
    }

    public function getOwnerOfResource(ResourceIdentification $identification): int
    {
        return $this->default_owner;
    }

    public function canBeAccessedByCurrentUser(ResourceIdentification $identification): bool
    {
        global $DIC;

        if (!$DIC->isDependencyAvailable('user') || !$DIC->isDependencyAvailable('database')) {
            return false;
        }

        $user_id = $DIC->user()->getId();
        $db = $DIC->database();
        $rid = $identification->serialize();

        $res = $db->queryF(
            'SELECT 1 FROM il_resource_rca rca
             INNER JOIN mail_attachment ma ON ma.rcid = rca.rcid
             INNER JOIN mail m ON m.mail_id = ma.mail_id
             WHERE rca.rid = %s AND m.user_id = %s
             LIMIT 1',
            [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
            [$rid, $user_id]
        );

        return $db->numRows($res) > 0;
    }
}
