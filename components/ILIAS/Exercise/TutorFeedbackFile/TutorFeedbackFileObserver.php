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

namespace ILIAS\Exercise\TutorFeedbackFile;

use ILIAS\ResourceStorage\Events\Event;
use ILIAS\ResourceStorage\Events\Data;
use ILIAS\ResourceStorage\Events\Observer;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\ResourceStorage\Events\Throwable;

class TutorFeedbackFileObserver implements Observer
{
    public function __construct(
        protected InternalDomainService $domain,
        protected int $ass_id
    ) {
    }

    public function getId(): string
    {
        return "exc_feedback_file";
    }

    public function update(Event $event, ?Data $data): void
    {
        $log = $this->domain->log();
        $log->debug("Update observer called.");
        $this->domain->assignment()->tutorFeedbackFile($this->ass_id)->sendNotification(
            $data["rcid"],
            $data["rid"]
        );
    }

    public function updateFailed(\Throwable $e, Event $event, ?Data $data): void
    {
        // TODO: Implement updateFailed() method.
    }

}
