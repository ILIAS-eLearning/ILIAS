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

namespace ILIAS\UI\Implementation\Component\Launcher;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Link;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\MessageBox;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Modal;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Result;

class Inline implements C\Launcher\Inline
{
    use ComponentHelper;

    protected Modal\Factory $modal_factory;
    protected Link $target;
    protected string $label;
    protected string $description = '';
    protected ?string $error_note = null;
    protected null | Icon | ProgressMeter $status_icon = null;
    protected bool $launchable = true;
    protected ?Modal\Roundtrip $modal = null;
    protected \Closure $evaluation;
    protected ?MessageBox\MessageBox $instruction = null;
    protected ?MessageBox\MessageBox $status_message = null;
    protected ?ServerRequestInterface $request = null;
    protected ?string $modal_submit_label = null;
    protected ?string $modal_cancel_label = null;

    public function __construct(
        Modal\Factory $modal_factory,
        Link $target
    ) {
        $this->modal_factory = $modal_factory;
        $this->target = $target;
        $this->label = $target->getLabel();
    }

    public function getTarget(): Link
    {
        return $this->target;
    }

    public function withDescription(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withStatusIcon(null | Icon | ProgressMeter $status_icon): self
    {
        $clone = clone $this;
        $clone->status_icon = $status_icon;
        return $clone;
    }

    public function getStatusIcon(): ?C\Component
    {
        return $this->status_icon;
    }

    public function withStatusMessageBox(?MessageBox\MessageBox $status_message): self
    {
        $clone = clone $this;
        $clone->status_message = $status_message;
        return $clone;
    }
    public function getStatusMessageBox(): ?MessageBox\MessageBox
    {
        return $this->status_message;
    }

    public function withButtonLabel(string $label, bool $launchable = true): self
    {
        $clone = clone $this;
        $clone->label = $label;
        $clone->launchable = $launchable;
        return $clone;
    }

    public function getButtonLabel(): ?string
    {
        return $this->label;
    }

    public function isLaunchable(): bool
    {
        return $this->launchable;
    }

    public function withInputs(Group $fields, \Closure $evaluation, MessageBox\MessageBox $instruction = null): self
    {
        $modal = $this->modal_factory->roundtrip(
            $this->getButtonLabel(),
            $instruction,
            $fields->getInputs(),
            $this->getTarget()->getURL()->__toString()
        );
        $clone = clone $this;
        $clone->modal = $modal;
        $clone->evaluation = $evaluation;
        return $clone;
    }

    public function withRequest(ServerRequestInterface $request): self
    {
        $clone = clone $this;
        $clone->request = $request;
        return $clone;
    }

    public function getResult(): ?Result
    {
        if ($this->request && $this->request->getMethod() == "POST") {
            $modal = $this->modal->withRequest($this->request);
            $result = $modal->getForm()->getInputGroup()->getContent();
            return $result;
        }
        return null;
    }

    public function getModal(): ?Modal\Roundtrip
    {
        return $this->modal;
    }

    public function getEvaluation(): \Closure
    {
        return $this->evaluation;
    }

    public function withModalSubmitLabel(?string $label): self
    {
        $clone = clone $this;
        $clone->modal_submit_label = $label;
        return $clone;
    }

    public function getModalSubmitLabel(): ?string
    {
        return $this->modal_submit_label;
    }

    public function withModalCancelLabel(?string $label): self
    {
        $clone = clone $this;
        $clone->modal_cancel_label = $label;
        return $clone;
    }

    public function getModalCancelLabel(): ?string
    {
        return $this->modal_cancel_label;
    }
}
