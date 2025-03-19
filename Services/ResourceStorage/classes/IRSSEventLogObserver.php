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

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Events\Observer;
use ILIAS\ResourceStorage\Events\Event;
use ILIAS\ResourceStorage\Events\Data;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class IRSSEventLogObserver implements Observer
{
    public function __construct(private \ilLogger $logger)
    {
    }

    public function getId(): string
    {
        return self::class;
    }

    private function appendData(string $to_message, ?Data $data = null): string
    {
        return $to_message . ': ' . ($data ? json_encode($data->getArrayCopy()) : '');
    }


    public function update(Event $event, ?Data $data): void
    {
        match ($event->value) {
            Event::COLLECTION_RESOURCE_ADDED => $this->logger->info($this->appendData("Collection resource added", $data)),
            Event::FLAVOUR_BUILD_SUCCESS => $this->logger->info($this->appendData("Flavour build success", $data)),
            Event::FLAVOUR_BUILD_FAILED => $this->logger->warning($this->appendData("Flavour build failed", $data)),
            default => $this->logger->debug($this->appendData($event->value, $data))
        };
    }

    public function updateFailed(\Throwable $e, Event $event, ?Data $data): void
    {
        // nothing to do
    }

}
