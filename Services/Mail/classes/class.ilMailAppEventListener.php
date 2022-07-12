<?php declare(strict_types=1);

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
        if (isset($this->parameters['visible_second_email'])
            && $this->isRelevantEvent()
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
        $listener = new self();
        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }
}
