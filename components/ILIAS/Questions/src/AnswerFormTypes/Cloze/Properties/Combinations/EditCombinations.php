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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Renderable;

class EditCombinations
{
    private const string STEP_EDIT_COMBINATIONS_OVERVIEW = 'eco';

    private const string LANG_VAR_EDIT_COMBINATIONS = 'edit_combinations';

    public function __construct(
        private readonly Factory $combinations_factory
    ) {
    }

    public function addCombinationsSubTab(
        Environment $environment
    ): void {
        $environment->addEditAnswerFormSubTab(
            self::STEP_EDIT_COMBINATIONS_OVERVIEW,
            self::LANG_VAR_EDIT_COMBINATIONS
        );
    }

    public function show(
        Environment $environment
    ): Async|Renderable|Properties {
        $environment->addEditAnswerFormSubTab(
            self::STEP_EDIT_COMBINATIONS_OVERVIEW,
            self::LANG_VAR_EDIT_COMBINATIONS
        );

        $environment->activateEditAnswerFormSubTab(
            self::STEP_EDIT_COMBINATIONS_OVERVIEW
        );

        $combinations_overview = $this->buildOverview($environment);

        $step = $environment->getStep();
        if ($step === self::STEP_EDIT_COMBINATIONS_OVERVIEW
            || $step === '') {
            return $combinations_overview;
        }

        return $combinations_overview->doAction();
    }

    private function buildOverview(
        Environment $environment
    ): Overview {
        return new Overview(
            $environment,
            $this->combinations_factory
        );
    }
}
