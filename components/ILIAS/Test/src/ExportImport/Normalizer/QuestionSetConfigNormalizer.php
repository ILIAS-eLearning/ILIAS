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

use ILIAS\DI\Container;
use ILIAS\Test\ExportImport\Envelopes\QuestionSetConfig;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ilObjTest;
use ilTestException;
use ilTestFixedQuestionSetConfig;
use ilTestRandomQuestionSetConfig;
use ilTestQuestionSetConfig;
use ilTestRandomQuestionSetSourcePoolDefinition;
use ReflectionClass;

/**
 * @implements Normalizer<QuestionSetConfig, array>
 */
#[Normalizes(QuestionSetConfig::class)]
class QuestionSetConfigNormalizer implements Normalizer
{
    public function __construct(
        private readonly Transformations $tt,
        private readonly Container $dic,
        private readonly TestDIC $test_dic,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof QuestionSetConfig) {
            throw new NormalizingException('Invalid value', $value);
        }

        $normalized = [
            'config' => $this->normalizeQuestionSetConfig($value->getConfig()),
            'test_obj' => $this->normalizeTestObj($value->getConfig()),
        ];

        if ($value->isRandom()) {
            $normalized['definitions'] = $this->tt->normalize($value->getDefinitions());
            $normalized['staging_pools'] = $this->normalizeStagingPools($value->getStagingPools());
        }

        return $normalized;
    }

    private function normalizeQuestionSetConfig(ilTestQuestionSetConfig $config): array
    {
        if ($config instanceof ilTestFixedQuestionSetConfig) {
            return [
                'type' => ilObjTest::QUESTION_SET_TYPE_FIXED,
            ];
        }

        if ($config instanceof ilTestRandomQuestionSetConfig) {
            return [
                'type' => ilObjTest::QUESTION_SET_TYPE_RANDOM,
                'homogeneous' => $config->arePoolsWithHomogeneousScoredQuestionsRequired(),
                'amount_mode' => $config->getQuestionAmountConfigurationMode(),
                'amount' => $config->getQuestionAmountPerTest(),
                'sync_timestamp' => $config->getLastQuestionSyncTimestamp(),
            ];
        }

        throw new NormalizingException('Invalid value', $config);
    }

    private function normalizeStagingPools(array $staging_pools): array
    {
        $normalized = [];
        foreach ($staging_pools as $pool_id => $questions) {
            $normalized[] = [
                'pool_id' => $this->tt->normalize(new Id($pool_id, 'pool')),
                'questions' => array_map(
                    fn($question) => $this->tt->normalize(
                        new Id($question, 'question')
                    ),
                    $questions
                ),
            ];
        }
        return $normalized;
    }

    private function normalizeTestObj(ilTestQuestionSetConfig $config): array
    {
        $reflection = new ReflectionClass($config);
        $property = $reflection->getProperty('test_obj');
        $test_obj = $property->getValue($config);

        if (!$test_obj instanceof ilObjTest) {
            throw new NormalizingException('Invalid test object', $test_obj);
        }

        return [
            'obj_id' => $this->tt->normalize(new Id($test_obj->getId(), 'object')),
            'test_id' => $this->tt->normalize(new Id($test_obj->getTestId(), 'tst')),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): mixed
    {
        if ($type !== QuestionSetConfig::class) {
            throw new NormalizingException("Invalid type for QuestionSetConfig: {$type}");
        }

        $test_obj = $this->denormalizeTestObj($value['test_obj']);
        $config = $this->denormalizeQuestionSetConfig($value['config'], $test_obj);

        if (!$config instanceof ilTestRandomQuestionSetConfig) {
            return new QuestionSetConfig($config);
        }

        $definitions = array_map(
            fn($definition) => $this->denormalizeSourcePoolDefinition($definition, $test_obj),
            $value['definitions']
        );
        $staging_pools = $this->denormalizeStagingPools($value['staging_pools']);

        return new QuestionSetConfig($config, $definitions, $staging_pools);
    }

    private function denormalizeQuestionSetConfig(array $normalized, ilObjTest $test_obj): ilTestQuestionSetConfig
    {
        if($normalized['type'] === ilObjTest::QUESTION_SET_TYPE_FIXED) {
            return new ilTestFixedQuestionSetConfig(
                $this->dic->repositoryTree(),
                $this->dic->database(),
                $this->dic->language(),
                $this->test_dic['logging.logger'],
                $this->dic['component.repository'],
                $test_obj,
                $this->test_dic['question.general_properties.repository']
            );
        }

        $config = new ilTestRandomQuestionSetConfig(
            $this->dic->repositoryTree(),
            $this->dic->database(),
            $this->dic->language(),
            $this->test_dic['logging.logger'],
            $this->dic['component.repository'],
            $test_obj,
            $this->test_dic['question.general_properties.repository']
        );

        $amount_mode = $this->tt->string($normalized['amount_mode']);
        if (!$config->isValidQuestionAmountConfigurationMode($amount_mode)) {
            throw new ilTestException("Invalid random test question set config amount mode given: {$amount_mode}");
        }

        $config->setQuestionAmountConfigurationMode($amount_mode);
        $config->setQuestionAmountPerTest($this->tt->nullableInt($normalized['amount']));
        $config->setPoolsWithHomogeneousScoredQuestionsRequired($this->tt->nullableBool($normalized['homogeneous']));
        $config->setLastQuestionSyncTimestamp($this->tt->nullableInt($normalized['sync_timestamp']));

        return $config;
    }

    private function denormalizeSourcePoolDefinition(array $normalized, ilObjTest $test_obj): ilTestRandomQuestionSetSourcePoolDefinition
    {
        $definition = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->dic->database(),
            $test_obj
        );

        return $this->tt->denormalize($normalized, $definition);
    }

    private function denormalizeStagingPools(array $normalized): array
    {
        $staging_pools = [];
        foreach ($normalized as $staging_pool) {
            $pool_id = $this->tt->denormalize($staging_pool['pool_id'], Id::class)->getId();

            $staging_pools[$pool_id] = array_map(
                fn($question): mixed => $this->tt->denormalize($question, Id::class)->getId(),
                $staging_pool['questions']
            );
        }
        return $staging_pools;
    }

    private function denormalizeTestObj(array $normalized): ilObjTest
    {
        $test_obj = new ilObjTest(0, false);
        $test_obj->setTestId($this->tt->denormalize($normalized['test_id'], Id::class)->getId());
        $test_obj->setId($this->tt->denormalize($normalized['obj_id'], Id::class)->getId());

        return $test_obj;
    }
}
