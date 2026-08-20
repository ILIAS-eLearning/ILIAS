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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer;

use ILIAS\Data\UUID\Uuid;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\NormalizesLegacy;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * Example class to demonstrate the usage of legacy normalizers.
 *
 * This normalizer is used to denormalize UUIDs in data from ILIAS 11. In this example the UUIDs were stored with an
 * underscore but now the factory expects a hyphen. This is a very simple example but legacy normalizers can be used
 * transform complex data as well.
 *
 * In this case the normalizer is a subclass of the UUIDNormalizer. This may be useful if you want to reuse basic
 * functionality. You can also implement the Normalizer interface directly if you don't need to reuse functionality.
 */
#[NormalizesLegacy('11', Uuid::class)]
class Legacy11UUIDNormalizer extends UUIDNormalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        throw new NormalizingException('Normalizing of legacy data is not supported');
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): Uuid
    {
        return parent::denormalize(str_replace('_', '-', (string) $value), $type);
    }
}
