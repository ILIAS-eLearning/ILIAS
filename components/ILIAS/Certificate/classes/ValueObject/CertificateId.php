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

namespace ILIAS\Certificate\ValueObject;

use InvalidArgumentException;

class CertificateId
{
    private const UUID_LENGTH = 36;
    private const UUID_SEPARATOR_COUNT = 4;

    private readonly string $certificate_id;

    public function __construct(string $certificate_id)
    {
        if ($certificate_id === '') {
            throw new InvalidArgumentException('certificate_id cannot be empty.');
        }

        if (strlen($certificate_id) !== self::UUID_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'certificate_id must be a valid UUID. UUID must be %s characters long.',
                self::UUID_LENGTH
            ));
        }

        if (substr_count($certificate_id, '-') !== 4) {
            throw new InvalidArgumentException(sprintf(
                'certificate_id must be a valid UUID. UUID must contain %s "-" characters.',
                self::UUID_SEPARATOR_COUNT
            ));
        }
        $this->certificate_id = $certificate_id;
    }

    public function asString(): string
    {
        return $this->certificate_id;
    }
}
