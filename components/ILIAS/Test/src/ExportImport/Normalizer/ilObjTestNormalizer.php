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

namespace ILIAS\Test\ExportImport\Normalizer;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ilObjTest;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\IlObjectNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * @implements Normalizer<ilObjTest, array>
 */
#[Normalizes(ilObjTest::class)]
class ilObjTestNormalizer extends IlObjectNormalizer implements Normalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof ilObjTest) {
            throw new NormalizingException('Invalid value', $value);
        }

        $normalized = parent::normalize($value);
        $normalized['test_id'] = $this->tt->normalize(new Id($value->getTestId(), 'tst'));

        return $normalized;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilObjTest
    {
        if ($type !== ilObjTest::class) {
            throw new NormalizingException("Invalid type for ilObjTest: {$type}");
        }

        /** @var ilObjTest $object */
        $object = parent::denormalize($value, ilObjTest::class);
        $object->setTestId(
            $this->tt->denormalize($value['test_id'], Id::class)->getId()
        );

        return $object;
    }
}
