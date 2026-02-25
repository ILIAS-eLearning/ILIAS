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

use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking as MarkingInterface;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\EditOverview;
use ILIAS\Questions\Response\Response;

class Marking implements MarkingInterface
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
        return false;
    }

    #[\Override]
    public function toStorage(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate;
    }

    #[\Override]
    public function toDelete(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate;
    }

    #[\Override]
    public function addAchievedPointsToResponse(
        Response $response
    ): Response {

    }

    #[\Override]
    public function getBestResponse(): Response
    {

    }
}
