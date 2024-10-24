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

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\Action as I;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Prompt\Prompt;

abstract class Action implements I\Action
{
    use ComponentHelper;
    /**
     * JS needs to know about the type of an action
     * and where to find the options (in case of signal)
     * Theses constants are passed to il.UI.table.data.init
     */
    public const OPT_ACTIONID = 'actId';
    public const OPT_ROWID = 'rowid';

    protected bool $async = false;
    protected ?Signal $async_signal = null;

    public function __construct(
        protected string $label,
        protected Prompt | URLBuilder $target,
        protected URLBuilderToken $row_id_parameter
    ) {

        $this->target = $target;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTarget(): Prompt|URLBuilder
    {
        return $this->target;
    }

    public function isPrompt(): bool
    {
        return $this->target instanceof Prompt;
    }

    public function withAsync(bool $async = true): self
    {
        if ($async && $this->isPrompt()) {
            throw new \LogicException('Prompt is async by default.');
        }
        $clone = clone $this;
        $clone->async = $async;
        return $clone;
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function withAsyncSignal(Signal $async_signal): self
    {
        $clone = clone $this;
        $clone->async_signal = $async_signal;
        return $clone;
    }

    //internal, should throw when null
    public function getAsyncSignal(): Signal
    {
        return $this->async_signal;
    }

    public function withRowId(string $row_id): self
    {
        $clone = clone $this;
        $clone->target = $clone->target->withParameter(
            $clone->row_id_parameter,
            [$row_id]
        );
        return $clone;
    }

    public function getURLBuilderJS(): string
    {
        $builder = $this->isPrompt() ? $this->target->getURLBuilder() : $this->target;
        return $builder->renderObject([$this->row_id_parameter]);
    }

    public function getURLBuilderTokensJS(): string
    {
        $builder = $this->isPrompt() ? $this->target->getURLBuilder() : $this->target;
        return $builder->renderTokens([$this->row_id_parameter]);
    }
}
