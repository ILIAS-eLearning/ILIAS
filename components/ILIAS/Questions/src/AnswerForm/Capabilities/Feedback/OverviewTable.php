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

namespace ILIAS\Questions\AnswerForm\Capabilities\Feedback;

use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Table\Data as DataTable;

interface OverviewTable
{
    public function getCreateModal(
        Environment $environment
    ): RoundTripModal;

    public function getTable(
        Environment $environment,
        Feedback $feedback
    ): DataTable;

    public function doAction(
        Environment $environment,
        Feedback $feedback,
        string $action
    ): Async|RoundTripModal|Feedback;
}
