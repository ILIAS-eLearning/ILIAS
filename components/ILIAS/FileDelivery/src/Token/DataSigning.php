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

namespace ILIAS\FileDelivery\Token;

use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileDelivery\Token\Signer\Payload\Payload;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface DataSigning
{
    public function getSignedStreamToken(
        FileStream $stream,
        string $filename,
        Disposition $disposition,
        int $user_id,
        ?\DateTimeImmutable $until = null
    ): string;

    public function verifyStreamToken(string $token): ?Payload;

    public function sign(array $data, string $salt, ?\DateTimeImmutable $until = null): string;

    public function verify(string $token, string $salt): ?array;
}
