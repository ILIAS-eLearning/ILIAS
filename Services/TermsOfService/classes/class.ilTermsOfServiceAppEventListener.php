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

/**
 * Class ilTermsOfServiceAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAppEventListener implements ilAppEventListener
{
    protected ilTermsOfServiceHelper $helper;
    protected string $component = '';
    protected string $event = '';
    protected array $parameters = [];

    public function __construct(ilTermsOfServiceHelper $helper)
    {
        $this->helper = $helper;
    }

    public function withComponent(string $component): self
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    public function withEvent(string $event): self
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    public function withParameters(array $parameters): self
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    protected function isUserDeletionEvent(): bool
    {
        return (
            'Services/User' === $this->component &&
            'deleteUser' === $this->event
        );
    }

    public function handle(): void
    {
        if ($this->isUserDeletionEvent()) {
            $this->helper->deleteAcceptanceHistoryByUser($this->parameters['usr_id']);
        }
    }

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        $listener = new self(new ilTermsOfServiceHelper());
        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }
}
