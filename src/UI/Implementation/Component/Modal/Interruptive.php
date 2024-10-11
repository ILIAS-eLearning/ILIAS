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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data\FormMethod;

class Interruptive extends Modal implements M\Interruptive
{
    /**
     * @var M\InterruptiveItem\InterruptiveItem[]
     */
    protected array $items = array();
    protected ?string $action_button_label = null;
    protected ?string $cancel_button_label = null;

    public function __construct(
        protected string $title,
        protected string $message,
        protected string $form_action,
        protected FormMethod $form_method,
        SignalGeneratorInterface $signal_generator
    ) {
        parent::__construct($signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function withFormAction(string $form_action): M\Interruptive
    {
        $clone = clone $this;
        $clone->form_action = $form_action;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAffectedItems(array $items): M\Interruptive
    {
        $types = array(M\InterruptiveItem\InterruptiveItem::class);
        $this->checkArgListElements('items', $items, $types);
        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActionButtonLabel(): ?string
    {
        return $this->action_button_label;
    }


    /**
     * @inheritdoc
     */
    public function withActionButtonLabel(string $action_label): M\Interruptive
    {
        $clone = clone $this;
        $clone->action_button_label = $action_label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCancelButtonLabel(): ?string
    {
        return $this->cancel_button_label;
    }

    /**
     * @inheritdoc
     */
    public function withCancelButtonLabel(string $cancel_label): M\Interruptive
    {
        $clone = clone $this;
        $clone->cancel_button_label = $cancel_label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAffectedItems(): array
    {
        return $this->items;
    }

    public function getFormAction(): string
    {
        return $this->form_action;
    }

    public function getFormMethod(): FormMethod
    {
        return $this->form_method;
    }
}
