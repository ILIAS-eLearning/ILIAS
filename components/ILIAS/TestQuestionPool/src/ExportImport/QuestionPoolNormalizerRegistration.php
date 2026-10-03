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

namespace ILIAS\TestQuestionPool\ExportImport;

use ILIAS\DI\Container;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Normalize\Normalizer\ilAssQuestionSkillAssignmentNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Normalize\Normalizer\ilObjQuestionPoolNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Normalize\Normalizer\SuggestedSolutionNormalizer;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolution;

final class QuestionPoolNormalizerRegistration
{
    public function __construct(private readonly Container $dic) {
    }

    public function register(Registry $registry, Transformations $transformations): void
    {
        $registry->register(
            \ilObjQuestionPool::class,
            new ilObjQuestionPoolNormalizer($transformations)
        );
        $registry->register(
            SuggestedSolution::class,
            new SuggestedSolutionNormalizer($transformations)
        );
        $registry->register(
            \ilAssQuestionSkillAssignment::class,
            new ilAssQuestionSkillAssignmentNormalizer($transformations, $this->dic->database())
        );
    }
}
