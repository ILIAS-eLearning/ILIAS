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
use ILIAS\Questions\Presentation\Layout\Viewable;

class Edit
{
    private const string SUB_ACTION_EDIT_COMBINATIONS_OVERVIEW = 'eco';

    private const string LANG_VAR_EDIT_COMBINATIONS = 'edit_combinations';

    public function __construct(
        private readonly Factory $combinations_factory
    ) {
    }

    public function addCombinationsSubTab(
        Environment $environment
    ): void {
        $environment->addEditAnswerFormSubTab(
            self::SUB_ACTION_EDIT_COMBINATIONS_OVERVIEW,
            self::LANG_VAR_EDIT_COMBINATIONS
        );
    }

    public function show(
        Environment $environment
    ): Async|Viewable|Properties|null {
        if (!$environment->isMarkingRequired()) {
            return null;
        }

        $environment->addEditAnswerFormSubTab(
            self::SUB_ACTION_EDIT_COMBINATIONS_OVERVIEW,
            self::LANG_VAR_EDIT_COMBINATIONS
        );

        $environment->activateEditAnswerFormSubTab(
            self::SUB_ACTION_EDIT_COMBINATIONS_OVERVIEW
        );

        $combinations_overview = $this->buildOverview($environment);

        $sub_action = $environment->getSubAction();
        if ($sub_action === self::SUB_ACTION_EDIT_COMBINATIONS_OVERVIEW
            || $sub_action === '') {
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
