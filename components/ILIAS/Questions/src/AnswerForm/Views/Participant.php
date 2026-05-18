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

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\ViewMode;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;

interface Participant
{
    public function show(
        Language $lng,
        Properties $properties,
        ?AdditionalAttemptData $attempt_data,
        ?Response $response_data,
        ViewMode $view_mode
    ): string;

    public function retrieveResponse(
        Uuid $response_id,
        Properties $properties,
        RequestWrapper $post_wrapper
    ): Response;
}
