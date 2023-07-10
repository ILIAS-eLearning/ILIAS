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

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\ReplaceSignal;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ReplaceSignal as ReplaceSignalImplementation;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class Slate implements ISlate\Slate
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    // allowed ARIA roles
    public const MENU = 'menu';

    /**
     * @var string[]
     */
    protected static array $allowed_aria_roles = array(
        self::MENU
    );

    protected string $name;
    protected Symbol $symbol;
    protected Signal $toggle_signal;
    protected Signal $engage_signal;
    protected ?Signal $replace_signal = null;
    protected bool $engaged = false;
    protected ?string $mainbar_tree_position = null;
    protected ?string $aria_role = null;
    protected SignalGeneratorInterface $signal_generator;

    /**
     * @param string 	$name 	name of the slate, also used as label
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        Symbol $symbol
    ) {
        $this->signal_generator = $signal_generator;
        $this->name = $name;
        $this->symbol = $symbol;

        $this->initSignals();
    }

    /**
     * Set the signals for this component.
     */
    protected function initSignals(): void
    {
        $this->toggle_signal = $this->signal_generator->create();
        $this->engage_signal = $this->signal_generator->create();
        $signal = $this->signal_generator->create(ReplaceSignalImplementation::class);
        $this->replace_signal = $signal;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getSymbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritdoc
     */
    public function getToggleSignal(): Signal
    {
        return $this->toggle_signal;
    }

    /**
     * @inheritdoc
     */
    public function getEngageSignal(): Signal
    {
        return $this->engage_signal;
    }

    /**
     * @inheritdoc
     */
    public function withEngaged(bool $state): ISlate\Slate
    {
        $clone = clone $this;
        $clone->engaged = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEngaged(): bool
    {
        return $this->engaged;
    }

    /**
     * @inheritdoc
     */
    abstract public function getContents(): array;

    /**
     * @inheritdoc
     */
    public function getReplaceSignal(): ?Signal
    {
        return $this->replace_signal;
    }

    /**
     * @inheritdoc
     */
    public function appendOnInView(Signal $signal): ISlate\Slate
    {
        return $this->appendTriggeredSignal($signal, 'in_view');
    }


    abstract public function withMappedSubNodes(callable $f): ISlate\Slate;

    /**
     * @inheritdoc
     */
    public function withMainBarTreePosition(string $tree_pos): ISlate\Slate
    {
        $clone = clone $this;
        $clone->mainbar_tree_position = $tree_pos;
        return $clone;
    }

    public function getMainBarTreePosition(): ?string
    {
        return $this->mainbar_tree_position;
    }

    public function getMainBarTreeDepth(): int
    {
        $pos = explode(':', $this->mainbar_tree_position);
        return count($pos) - 1;
    }

    /**
     * Get a slate like this, but with an additional ARIA role.
     */
    public function withAriaRole(string $aria_role): ISlate\Slate
    {
        $this->checkArgIsElement(
            "role",
            $aria_role,
            self::$allowed_aria_roles,
            implode('/', self::$allowed_aria_roles)
        );
        $clone = clone $this;
        $clone->aria_role = $aria_role;
        return $clone;
    }

    /**
     * Get the ARIA role on the slate.
     */
    public function getAriaRole(): ?string
    {
        return $this->aria_role;
    }
}
