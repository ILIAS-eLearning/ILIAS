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

namespace ILIAS\TestQuestionPool\ExportImport\Normalize\Normalizer;

use ilTestSkillLevelThreshold;
use ilDBInterface;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;

/**
 * @implements Normalizer<ilTestSkillLevelThreshold, array>
 */
class ilTestSkillLevelThresholdNormalizer implements Normalizer
{
    public function __construct(
        private readonly Transformations $tt,
        private readonly ilDBInterface $db,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof ilTestSkillLevelThreshold) {
            throw new NormalizingException('Invalid value', $value);
        }

        return [
            'id' => $this->tt->normalize(new Id($value->getSkillLevelId(), 'skill_level')),
            'test_id' => $this->tt->normalize(new Id($value->getTestId(), 'tst')),
            'skill_base_id' => $this->tt->normalize(new Id($value->getSkillBaseId(), 'skill_base')),
            'skill_tref_id' => $this->tt->normalize(new Id($value->getSkillTrefId(), 'skill_tref')),
            'threshold' => $value->getThreshold(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilTestSkillLevelThreshold
    {
        if ($type !== ilTestSkillLevelThreshold::class) {
            throw new NormalizingException("Invalid type for ilTestSkillLevelThreshold: {$type}");
        }

        $threshold = new ilTestSkillLevelThreshold($this->db);
        $threshold->setSkillLevelId($this->tt->denormalize($value['id'], Id::class)->getId());
        $threshold->setTestId($this->tt->denormalize($value['test_id'], Id::class)->getId());
        $threshold->setSkillBaseId($this->tt->denormalize($value['skill_base_id'], Id::class)->getId());
        $threshold->setSkillTrefId($this->tt->denormalize($value['skill_tref_id'], Id::class)->getId());
        $threshold->setThreshold($this->tt->int($value['threshold']));

        return $threshold;
    }
}
