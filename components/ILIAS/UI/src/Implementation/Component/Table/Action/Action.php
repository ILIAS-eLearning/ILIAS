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
use ILIAS\UI\Component as C;
use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

abstract class Action implements C\Table\Action\Action
{
    use ComponentHelper;

    protected bool $is_legacy_async = false;
    protected ?C\Signal $legacy_async_signal = null;

    public function __construct(
        protected string $label,
        protected C\Prompt\Prompt|URLBuilder $target,
        protected URLBuilderToken $row_id_parameter,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTarget(): C\Prompt\Prompt|URLBuilder
    {
        return $this->target;
    }

    /**
     * @inheritDoc
     */
    public function withAsync(bool $is_async = true): self
    {
        if ($this->target instanceof C\Prompt\Prompt) {
            throw new \LogicException('Action already migrated to Prompt and is async by default.');
        }
        $clone = clone $this;
        $clone->is_legacy_async = $is_async;
        return $clone;
    }

    /**
     * @deprecated use {@see self::getTarget() to determine behaviour}
     */
    public function isTargetLegacyAsync(): bool
    {
        return $this->is_legacy_async;
    }

    /**
     * @deprecated do not pass information between rendering steps via DTO!
     */
    public function withLegacyAsyncSignal(C\Signal $signal): static
    {
        $clone = clone $this;
        $clone->legacy_async_signal = $signal;
        return $clone;
    }

    /**
     * @deprecated do not pass information between rendering steps via DTO!
     */
    public function getLegacyAsyncSignal(): C\Signal
    {
        return $this->legacy_async_signal;
    }

    public function withRowId(string $row_id): self
    {
        $clone = clone $this;
        $clone->target = $clone->target->withParameter(
            $this->getRowIdParameter(),
            [$row_id]
        );
        return $clone;
    }

    public function getRowIdParameter(): URLBuilderToken
    {
        return $this->row_id_parameter;
    }
}
