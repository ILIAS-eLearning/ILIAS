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

namespace ILIAS\Forum\Files\Access;

use ilLogger;

/**
 * Records denied attachment requests, so repeated attempts to address a container
 * through a foreign forum object remain traceable.
 */
final class LoggedAttachmentDelivery implements AddressedAttachmentDelivery
{
    public function __construct(
        private readonly AddressedAttachmentDelivery $delivery,
        private readonly ilLogger $logger,
        private readonly int $acting_usr_id
    ) {
    }

    public function forumObjId(): int
    {
        return $this->delivery->forumObjId();
    }

    public function containerId(): int
    {
        return $this->delivery->containerId();
    }

    public function deliverFile(string $revision_token): void
    {
        try {
            $this->delivery->deliverFile($revision_token);
        } catch (AttachmentAccessDenied $denial) {
            $this->record($denial);

            throw $denial;
        }
    }

    public function deliverZipFile(): bool
    {
        try {
            return $this->delivery->deliverZipFile();
        } catch (AttachmentAccessDenied $denial) {
            $this->record($denial);

            throw $denial;
        }
    }

    private function record(AttachmentAccessDenied $denial): void
    {
        $this->logger->warning(
            'Denied forum attachment request: ' . $denial->reason()->logMessage(),
            [
                'reason' => $denial->reason()->value,
                'forum_obj_id' => $this->delivery->forumObjId(),
                'container_id' => $this->delivery->containerId(),
                'usr_id' => $this->acting_usr_id
            ]
        );
    }
}
