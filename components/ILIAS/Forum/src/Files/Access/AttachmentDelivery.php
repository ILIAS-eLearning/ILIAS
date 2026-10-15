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
 * The delivery capability of the forum file storage, segregated from its
 * management capabilities (upload, move, clone, delete).
 */
interface AttachmentDelivery
{
    /**
     * @throws AttachmentAccessDenied
     */
    public function deliverFile(string $revision_token): void;

    /**
     * @throws AttachmentAccessDenied
     */
    public function deliverZipFile(): bool;
}
