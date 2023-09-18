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

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Packaging
{
    private const LEASED_BY = 'lb';
    private const LEASED_AT = 'la';
    private const LEASED_FOR_RID = 'r';
    private const TYPE = 't';
    private const LEASED_FOR_STREAM_URI = 'u';
    private const FORMAT = 'Y-m-d H:i:s';


    private function hash(string $string): string
    {
        return base64_encode($string);
    }

    private function unhash(string $string): string
    {
        return base64_decode($string);
    }


    public function pack(AccessToken $token): string
    {
        return $this->hash(
            json_encode([
                self::LEASED_BY => $token->leasedBy(),
                self::LEASED_AT => $token->leasedAt()->format(self::FORMAT),
                self::LEASED_FOR_RID => $token->leasedForRid()->serialize(),
                self::LEASED_FOR_STREAM_URI => $token->leasedForStreamUri(),
                self::TYPE => $token->type(),
            ])
        );
    }

    public function unpack(string $packed): AccessToken
    {
        $data = json_decode($this->unhash($packed), true);

        return new AccessToken(
            (int)($data[self::LEASED_BY] ?? 0),
            new \DateTimeImmutable((string)($data[self::LEASED_AT] ?? 'now')),
            new ResourceIdentification((string)($data[self::LEASED_FOR_RID] ?? '')),
            (string)($data[self::LEASED_FOR_STREAM_URI] ?? '')
        );
    }
}
