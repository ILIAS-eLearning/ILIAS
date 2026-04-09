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

namespace ILIAS\TestQuestionPool\ExportImport\Normalizer;

use ilObjQuestionPool;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\IlObjectNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * @implements Normalizer<ilObjQuestionPool, array>
 */
#[Normalizes(ilObjQuestionPool::class)]
class ilObjQuestionPoolNormalizer extends IlObjectNormalizer implements Normalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof ilObjQuestionPool) {
            throw new NormalizingException('Invalid value', $value);
        }

        $normalized = parent::normalize($value);
        $normalized['skill_service_enabled'] = $value->isSkillServiceEnabled();

        return $normalized;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilObjQuestionPool
    {
        if ($type !== ilObjQuestionPool::class) {
            throw new NormalizingException("Invalid type for ilObjQuestionPool: {$type}");
        }

        /** @var ilObjQuestionPool $object */
        $object = parent::denormalize($value, ilObjQuestionPool::class);
        $object->setSkillServiceEnabled($this->tt->bool($value['skill_service_enabled']));

        return $object;
    }
}
