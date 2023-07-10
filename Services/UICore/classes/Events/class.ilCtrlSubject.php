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

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilCtrlSubject
{
    /**
     * @var array<ilCtrlEvent, ilCtrlObserver[]>
     */
    protected array $observer_groups = [];

    public function __construct()
    {
        $this->initObserverGroup((ilCtrlEvent::ALL)->value);
    }

    public function attach(ilCtrlObserver $observer, ilCtrlEvent $event): void
    {
        $this->initObserverGroup($event->value);

        $this->observer_groups[$event->value][] = $observer;
    }

    public function detach(ilCtrlObserver $observer, ilCtrlEvent $event = ilCtrlEvent::ALL): void
    {
        $this->initObserverGroup($event->value);

        foreach ($this->observer_groups[$event->value] as $index => $attached_observer) {
            if ($attached_observer->getId() === $observer->getId()) {
                unset($this->observer_groups[$event->value][$index]);
            }
        }
    }

    public function notify(ilCtrlEvent $event, ?string $data): void
    {
        $this->initObserverGroup($event->value);

        $observers = array_merge(
            $this->observer_groups[(ilCtrlEvent::ALL)->value],
            $this->observer_groups[$event->value],
        );

        foreach ($observers as $interessted_observer) {
            $interessted_observer->update($event, $data);
        }
    }

    protected function initObserverGroup(string $group): void
    {
        if (!isset($this->observer_groups[$group])) {
            $this->observer_groups[$group] = [];
        }
    }
}
