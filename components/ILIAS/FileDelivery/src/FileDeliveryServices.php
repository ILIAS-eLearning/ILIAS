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
}
