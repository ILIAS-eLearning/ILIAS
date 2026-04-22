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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ilObjTest;
use ilTestFixedQuestionSetConfig;
use ilTestRandomQuestionSetConfig;
use ilTestQuestionSetConfig;

/**
 * @implements Normalizer<ilTestQuestionSetConfig, array>
 */
#[Normalizes(ilTestQuestionSetConfig::class)]
class QuestionSetConfigNormalizer implements Normalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if ($value instanceof ilTestFixedQuestionSetConfig) {
            return [
                'type' => ilObjTest::QUESTION_SET_TYPE_FIXED,
            ];
        }

        if ($value instanceof ilTestRandomQuestionSetConfig) {
            return [
                'type' => ilObjTest::QUESTION_SET_TYPE_RANDOM,
                'homogeneous' => $value->arePoolsWithHomogeneousScoredQuestionsRequired(),
                'amount_mode' => $value->getQuestionAmountConfigurationMode(),
                'amount' => $value->getQuestionAmountPerTest(),
                'sync_timestamp' => $value->getLastQuestionSyncTimestamp(),
            ];
        }

        throw new NormalizingException('Invalid value', $value);
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilTestQuestionSetConfig
    {
        if (!in_array(ilTestQuestionSetConfig::class, class_parents($type))) {
            throw new NormalizingException("Invalid type for ilTestQuestionSetConfig: {$type}");
        }

        //TODO: Implement denormalization
    }
}
