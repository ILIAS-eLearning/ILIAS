<?php

declare(strict_types=1);

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

class ilForumNotificationEventsFormGUI
{
    /** @var array<string, int> */
    private array $events = [
        'notify_modified' => ilForumNotificationEvents::UPDATED,
        'notify_censored' => ilForumNotificationEvents::CENSORED,
        'notify_uncensored' => ilForumNotificationEvents::UNCENSORED,
        'notify_post_deleted' => ilForumNotificationEvents::POST_DELETED,
        'notify_thread_deleted' => ilForumNotificationEvents::THREAD_DELETED,
    ];

    public function __construct(
        private string $action,
        private ?array $predefined_values,
        private \ILIAS\UI\Factory $ui_factory,
        private ilLanguage $lng
    ) {
    }

    public function getValueForEvent(string $event): int
    {
        if (isset($this->events[$event])) {
            return $this->events[$event];
        }

        throw new InvalidArgumentException(sprintf('Event "%s" is not supported.', $event));
    }

    /**
     * @return list<string>
     */
    public function getValidEvents(): array
    {
        return array_keys($this->events);
    }

    public function build(): \ILIAS\UI\Component\Input\Container\Form\Form
    {
        $items = [];

        foreach (array_keys($this->events) as $key) {
            $checkbox = $this->ui_factory->input()->field()->checkbox($this->lng->txt($key));
            if ($this->predefined_values !== null && isset($this->predefined_values[$key])) {
                $checkbox = $checkbox->withValue($this->predefined_values[$key]);
            }

            $items[$key] = $checkbox;
        }

        $hidden = $this->ui_factory->input()->field()->hidden();
        if ($this->predefined_values !== null && isset($this->predefined_values['hidden_value'])) {
            $hidden = $hidden->withValue((string) $this->predefined_values['hidden_value']);
        }
        $items['hidden_value'] = $hidden;

        return $this->ui_factory->input()->container()->form()->standard(
            $this->action,
            $items
        );
    }
}
