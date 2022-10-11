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

namespace ILIAS\UI\Implementation\Component\Launcher;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Link;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\MessageBox;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\Input\Container\Form;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Result;

class Inline implements C\Launcher\Inline
{
    use ComponentHelper;

    protected Form\Factory $form_factory;
    protected Link $target;
    protected string $label;
    protected string $description = '';
    protected ?string $error_note = null;
    protected null | Icon | ProgressMeter $status_icon = null;
    protected bool $launchable = true;
    protected ?Form\Form $form = null;
    protected \Closure $evaluation;
    protected ?MessageBox\MessageBox $instruction = null;
    protected ?MessageBox\MessageBox $status_message = null;
    protected ?ServerRequestInterface $request = null;

    public function __construct(
        Form\Factory $form_factory,
        Link $target
    ) {
        $this->form_factory = $form_factory;
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
        $clone = clone $this;
        $clone->form = $this->form_factory->standard((string)$clone->getTarget()->getURL(), [$fields]);
        $clone->evaluation = $evaluation;
        $clone->instruction = $instruction;
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
            $form = $this->form->withRequest($this->request);
            $result = $form->getInputGroup()->getContent();
            return $result;
        }
        return null;
    }

    public function getForm(): ?Form\Form
    {
        return $this->form;
    }
    public function getEvaluation(): \Closure
    {
        return $this->evaluation;
    }

    public function getInstruction(): ?MessageBox\MessageBox
    {
        return $this->instruction;
    }
}
