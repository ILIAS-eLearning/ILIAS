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

namespace ILIAS\FileDelivery;

use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface FileDeliveryServices
{
    public function delivery(): StreamDelivery;

    public function legacyDelivery(): LegacyDelivery;

    public function buildTokenURL(
        FileStream $stream,
        string $filename,
        Disposition $disposition,
        int $user_id,
        int $valid_for_at_least_hours
    ): URI;

    /**
     * Delivers the file from the ILIAS domain even when User Content Isolation is
     * active, for content that must run on the same origin as ILIAS. The file does
     * not profit from the isolation.
     *
     * @deprecated We do not want content outside of the User Content Isolation; this only exists
     *             until the remaining consumers (e.g. SCORM) run on the content domain and
     *             will be removed with ILIAS 13.
     * @internal Only for {@see \ILIAS\ResourceStorage\Consumer\ContainerURIConsumer::withoutIsolation()},
     *           which is the one way for consumers to deliver without isolation.
     */
    public function buildTokenURLWithoutIsolation(
        FileStream $stream,
        string $filename,
        Disposition $disposition,
        int $user_id,
        int $valid_for_at_least_hours
    ): URI;
}
