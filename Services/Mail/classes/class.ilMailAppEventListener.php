<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Services\User\ChangedUserFieldAttribute;

/**
 * Class ilMailAppEventListener
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMailAppEventListener implements ilAppEventListener
{
    private Container $dic;

    protected string $component = '';
    protected string $event = '';
    /** @var ChangedUserFieldAttribute[] */
    protected array $parameters = [];

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }

    public function withComponent(string $component) : self
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    public function withEvent(string $event) : self
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    public function withParameters(array $parameters) : self
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    private function isRelevantEvent() : bool
    {
        return $this->component === 'Services/User'
            && $this->event === 'onUserFieldAttributesChanged';
    }

    public function handle() : void
    {
        if ($this->isRelevantEvent()
            && isset($this->parameters['visible_second_email'])
            && !(bool) $this->parameters['visible_second_email']->getNewValue()) {
            switch ((int) ($this->dic->settings()->get('mail_address_option') ?? ilMailOptions::FIRST_EMAIL)) {
                case ilMailOptions::SECOND_EMAIL:
                case ilMailOptions::BOTH_EMAIL:
                    $globalAddressSettingsChangedCommand = new ilMailGlobalAddressSettingsChangedCommand(
                        $this->dic->database(),
                        ilMailOptions::FIRST_EMAIL
                    );
                    $globalAddressSettingsChangedCommand->execute();
                    break;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter) : void
    {
        $listener = new static();
        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }
}
