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

/**
 * Passes a delivery request on only after a guard confirmed that the addressed
 * container belongs to the addressed forum object.
 */
final class GuardedAttachmentDelivery implements AddressedAttachmentDelivery
{
    public function __construct(
        private readonly AddressedAttachmentDelivery $delivery,
        private readonly AttachmentAccessGuard $guard
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
        $this->assertAccessIsGranted();

        $this->delivery->deliverFile($revision_token);
    }

    public function deliverZipFile(): bool
    {
        $this->assertAccessIsGranted();

        return $this->delivery->deliverZipFile();
    }

    /**
     * @throws AttachmentAccessDenied
     */
    private function assertAccessIsGranted(): void
    {
        $decision = $this->guard->decide(
            $this->delivery->forumObjId(),
            $this->delivery->containerId()
        );

        if (!$decision->isGranted()) {
            throw AttachmentAccessDenied::because($decision->denialReason());
        }
    }
}
