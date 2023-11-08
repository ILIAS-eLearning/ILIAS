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

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use InvalidArgumentException;

/**
 * Class SystemInfo
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SystemInfo implements MainControls\SystemInfo
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected string $head_line;
    protected string $information_text;
    protected ?URI $dismiss_action = null;
    protected string $denotation = self::DENOTATION_NEUTRAL;
    protected Signal $close_signal;
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator, string $head_line, string $information_text)
    {
        $this->signal_generator = $signal_generator;
        $this->head_line = $head_line;
        $this->information_text = $information_text;
        $this->initSignals();
    }

    protected function initSignals(): void
    {
        $this->close_signal = $this->signal_generator->create();
    }

    public function getHeadLine(): string
    {
        return $this->head_line;
    }

    public function getInformationText(): string
    {
        return $this->information_text;
    }

    public function isDismissable(): bool
    {
        return !is_null($this->dismiss_action);
    }

    public function getDismissAction(): URI
    {
        return $this->dismiss_action;
    }

    public function withDismissAction(?URI $uri): MainControls\SystemInfo
    {
        $clone = clone $this;
        $clone->dismiss_action = $uri;
        return $clone;
    }

    public function withDenotation(string $denotation): MainControls\SystemInfo
    {
        if (
            $denotation !== MainControls\SystemInfo::DENOTATION_NEUTRAL
            && $denotation !== MainControls\SystemInfo::DENOTATION_IMPORTANT
            && $denotation !== MainControls\SystemInfo::DENOTATION_BREAKING
        ) {
            throw new InvalidArgumentException("Unknown denotation '$denotation'");
        }

        $clone = clone $this;
        $clone->denotation = $denotation;
        return $clone;
    }

    public function getDenotation(): string
    {
        return $this->denotation;
    }

    public function getCloseSignal(): Signal
    {
        return $this->close_signal;
    }

    /**
     * @inheritDoc
     */
    public function withResetSignals(): SystemInfo
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }
}
