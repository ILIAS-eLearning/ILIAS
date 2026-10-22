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

use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
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

        if (!$DIC->isDependencyAvailable('resourceStorage')) {
            return false;
        }

        $user_id = $DIC->user()->getId();
        $irss = $DIC->resourceStorage();

        try {
            if ($irss->manage()->getCurrentRevision($identification)->getOwnerId() === $user_id) {
                return true;
            }
        } catch (Throwable) {
        }

        return $this->anyCollectionContains(
            $irss,
            $this->collectionsOfUser($DIC->database(), $user_id),
            $identification
        );
    }

    /**
     * @return list<string> Collections of the user's mails, attachment pool and compose stage
     */
    private function collectionsOfUser(ilDBInterface $db, int $user_id): array
    {
        $rcids = [];

        $res = $db->queryF(
            'SELECT DISTINCT ma.rcid FROM mail_attachment ma
             INNER JOIN mail m ON m.mail_id = ma.mail_id
             WHERE m.user_id = %s AND ma.rcid IS NOT NULL',
            [ilDBConstants::T_INTEGER],
            [$user_id]
        );
        while ($row = $db->fetchAssoc($res)) {
            $rcids[] = (string) $row['rcid'];
        }

        $res = $db->queryF(
            'SELECT value FROM usr_pref WHERE usr_id = %s AND keyword = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT],
            [$user_id, ilFileDataMail::POOL_RCID_PREF_KEY]
        );
        while ($row = $db->fetchAssoc($res)) {
            $rcids[] = (string) $row['value'];
        }

        $res = $db->queryF(
            'SELECT attachments FROM mail_saved WHERE user_id = %s',
            [ilDBConstants::T_INTEGER],
            [$user_id]
        );
        while ($row = $db->fetchAssoc($res)) {
            $stage = MailAttachments::fromDb(is_string($row['attachments'] ?? null) ? $row['attachments'] : null);
            if ($stage !== null && $stage->isIrss()) {
                $rcids[] = $stage->rcid()->serialize();
            }
        }

        return array_values(array_unique(array_filter(
            $rcids,
            static fn(string $rcid): bool => $rcid !== '' && $rcid !== '-'
        )));
    }

    /**
     * @param list<string> $rcids
     */
    private function anyCollectionContains(
        ResourceStorage $irss,
        array $rcids,
        ResourceIdentification $identification
    ): bool {
        if ($rcids === []) {
            return false;
        }

        $irss->preloadCollections($rcids);
        foreach ($rcids as $rcid) {
            try {
                if ($irss->collection()->get(new ResourceCollectionIdentification($rcid))->isIn($identification)) {
                    return true;
                }
            } catch (Throwable) {
            }
        }

        return false;
    }
}
