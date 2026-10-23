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

namespace ILIAS\Test\ExportImport;

use ILIAS\DI\Container;
use ILIAS\Test\ExportImport\Normalize\Envelopes\QuestionSetConfig;
use ILIAS\Test\ExportImport\Normalize\Normalizer\AttemptResultNormalizer;
use ILIAS\Test\ExportImport\Normalize\Normalizer\ExportableNormalizer;
use ILIAS\Test\ExportImport\Normalize\Normalizer\ParticipantNormalizer;
use ILIAS\Test\ExportImport\Normalize\Normalizer\ParticipantResultNormalizer;
use ILIAS\Test\ExportImport\Normalize\Normalizer\QuestionSetConfigNormalizer;
use ILIAS\Test\ExportImport\Normalize\Normalizer\ilObjTestNormalizer;
use ILIAS\Test\ExportImport\Normalize\Normalizer\ilTestSequenceNormalizer;
use ILIAS\Test\Participants\Participant;
use ILIAS\Test\Results\Data\AttemptResult;
use ILIAS\Test\Results\Data\ParticipantResult;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Normalize\Normalizer\ilTestSkillLevelThresholdNormalizer;

final class TestNormalizerRegistration
{
    public function __construct(
        private readonly Container $dic,
        private readonly TestDIC $test_dic
    ) {
    }

    public function register(Registry $registry, Transformations $transformations): void
    {
        $registry->register(\ilObjTest::class, new ilObjTestNormalizer($transformations));
        $registry->register(
            \ilTestSequence::class,
            new ilTestSequenceNormalizer(
                $transformations, 
                $this->dic->database(), 
                $this->test_dic['question.general_properties.repository']
            )
        );
        $registry->register(AttemptResult::class, new AttemptResultNormalizer($transformations));
        $registry->register(
            ParticipantResult::class,
            new ParticipantResultNormalizer($transformations)
        );
        $registry->register(Participant::class, new ParticipantNormalizer($transformations));
        $registry->register(Exportable::class, new ExportableNormalizer());
        $registry->register(
            QuestionSetConfig::class,
            new QuestionSetConfigNormalizer(
                $transformations, 
                $this->dic->database(), 
                $this->dic->language(), 
                $this->dic->repositoryTree(), 
                $this->dic['component.repository'],
                $this->test_dic['logging.logger'],
                $this->test_dic['question.general_properties.repository']
            )
        );
        $registry->register(
            \ilTestSkillLevelThreshold::class,
            new ilTestSkillLevelThresholdNormalizer($transformations, $this->dic->database())
        );
    }
}
