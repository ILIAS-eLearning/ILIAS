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
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;
use ILIAS\ResourceStorage\Stakeholder\ConfidentialStakeholder;

class ilMailAttachmentStakeholder extends AbstractResourceStakeholder implements ConfidentialStakeholder
{
    public function getId(): string
    {
        return 'mail_attachments';
    }

    public function areNewResourcesConfidential(): bool
    {
        return true;
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

        if ($this->mailboxContainsResource($db, $rid, $user_id)) {
            return true;
        }

        if ($DIC->isDependencyAvailable('resourceStorage')) {
            try {
                $owner_id = $DIC->resourceStorage()->manage()->getCurrentRevision($identification)->getOwnerId();
                if ($owner_id === $user_id) {
                    return true;
                }
            } catch (Throwable) {
            }
        }

        return $this->poolOrStageContainsResource($db, $rid, $user_id);
    }

    private function mailboxContainsResource(ilDBInterface $db, string $rid, int $user_id): bool
    {
        $db->setLimit(1, 0);
        $res = $db->queryF(
            'SELECT 1 FROM ' . CollectionDBRepository::COLLECTION_ASSIGNMENT_TABLE_NAME . ' rca
             INNER JOIN mail_attachment ma ON ma.rcid = rca.' . CollectionDBRepository::C_IDENTIFICATION . '
             INNER JOIN mail m ON m.mail_id = ma.mail_id
             WHERE rca.' . CollectionDBRepository::R_IDENTIFICATION . ' = %s AND m.user_id = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
            [$rid, $user_id]
        );

        return $db->numRows($res) > 0;
    }

    private function poolOrStageContainsResource(ilDBInterface $db, string $rid, int $user_id): bool
    {
        $assignment = CollectionDBRepository::COLLECTION_ASSIGNMENT_TABLE_NAME;
        $resource_column = CollectionDBRepository::R_IDENTIFICATION;
        $collection_column = CollectionDBRepository::C_IDENTIFICATION;

        $db->setLimit(1, 0);
        $res = $db->queryF(
            'SELECT 1 FROM usr_pref p
             INNER JOIN ' . $assignment . ' rca ON rca.' . $collection_column . ' = p.value
             WHERE p.usr_id = %s AND p.keyword = %s AND rca.' . $resource_column . ' = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$user_id, ilFileDataMail::POOL_RCID_PREF_KEY, $rid]
        );
        if ($db->numRows($res) > 0) {
            return true;
        }

        $db->setLimit(1, 0);
        $res = $db->queryF(
            'SELECT 1 FROM mail_saved ms
             INNER JOIN ' . $assignment . ' rca ON rca.' . $collection_column . ' = ms.attachments
             WHERE ms.user_id = %s AND rca.' . $resource_column . ' = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT],
            [$user_id, $rid]
        );

        return $db->numRows($res) > 0;
    }
}
