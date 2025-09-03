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

namespace ILIAS\Questions\AnswerForm\Views;

use ILIAS\Questions\Question\Persistence\UpdateQuery;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

interface Edit
{
    public function create(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|UpdateQuery;

    public function edit(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|UpdateQuery;

    public function other(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $cmd
    ): array|UpdateQuery;
}
