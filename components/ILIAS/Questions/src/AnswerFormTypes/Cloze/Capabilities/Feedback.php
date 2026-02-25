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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Capabilities;

use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Feedback as FeedbackInterface;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\EditOverview;
use ILIAS\Questions\Question\Response;

class Feedback implements FeedbackInterface
{
    #[\Override]
    public function isConfigured(): bool
    {
        return false;
    }

    #[\Override]
    public function edit(
        Environment $environment
    ): self|Async|EditForm|EditOverview {
        return match ($environment->getStep()) {
            self::STEP_EDIT_BASIC_PROPERTIES => $this->startEditing($environment),
            self::STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES =>
                $this->addLegacyTextToBasicProperties($environment),
            self::STEP_CONFIRMED_GAP_REMOVAL,
            self::STEP_PROCESS_BASIC_PROPERTIES => $this->processBasicEditingForm(
                $environment->withPreservedTableRowIdsParameter()
            ),
            default => $this->forwardCmdToEditGaps(
                $environment->withPreservedTableRowIdsParameter(),
                $step
            )
        };
    }

    #[\Override]
    public function toStorage(
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate;
    }

    #[\Override]
    public function toDelete(
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate;
    }

    #[\Override]
    public function getGeneralFeedback(
        Response $response
    ): array {

    }

    #[\Override]
    public function getSpecificFeedback(
        Response $response,
        string $answer_id
    ): array {

    }
}
