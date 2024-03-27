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

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input\Container\Form\FormWithoutSubmitButton;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\ReplaceSignal;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Modal as M;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTrip extends Modal implements M\RoundTrip
{
    /**
     * @var Button\Button[]
     */
    protected array $action_buttons = [];

    /**
     * @var Component[]
     */
    protected array $content;

    protected ReplaceSignal $replace_signal;
    protected Signal $submit_signal;
    protected FormWithoutSubmitButton $form;
    protected string $title;
    protected string $cancel_button_label = 'cancel';
    protected string $submit_button_label = 'save';

    /**
     * @param Component[]|Component|null $content
     * @param FormInput[]                $inputs
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        FieldFactory $field_factory,
        NameSource $name_source,
        string $title,
        $content,
        array $inputs = [],
        ?string $post_url = null
    ) {
        parent::__construct($signal_generator);

        $content = (null !== $content) ? $this->toArray($content) : [];
        $this->checkArgListElements('content', $content, [Component::class]);

        $this->form = new FormWithoutSubmitButton(
            $signal_generator,
            $field_factory,
            $name_source,
            $post_url ?? '',
            $inputs,
        );

        $this->title = $title;
        $this->content = $content;

        $this->initSignals();
    }

    public function getForm(): FormWithoutSubmitButton
    {
        return $this->form;
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
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function getActionButtons(): array
    {
        return $this->action_buttons;
    }

    /**
     * @inheritdoc
     */
    public function withActionButtons(array $buttons): self
    {
        $types = [Button\Button::class];
        $this->checkArgListElements('buttons', $buttons, $types);
        $clone = clone $this;
        $clone->action_buttons = $buttons;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCancelButtonLabel(): string
    {
        return $this->cancel_button_label;
    }

    public function withCancelButtonLabel(string $label): self
    {
        $clone = clone $this;
        $clone->cancel_button_label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getReplaceSignal(): ReplaceSignal
    {
        return $this->replace_signal;
    }

    /**
     * Set the show/close/replace signals for this modal
     * Set up the submit signal for form submissions
     */
    public function initSignals(): void
    {
        parent::initSignals();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->replace_signal = $this->signal_generator->create(ReplaceSignal::class);
        $this->submit_signal = $this->signal_generator->create();
    }

    /**
     * @inheritdoc
     */
    public function getInputs(): array
    {
        return $this->form->getInputs();
    }

    /**
     * @inheritdoc
     */
    public function withRequest(ServerRequestInterface $request): self
    {
        $clone = clone $this;
        $clone->form = $clone->form->withRequest($request);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->form = $clone->form->withAdditionalTransformation($trafo);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->form->getData();
    }

    /**
     * @inheritdoc
     */
    public function getError(): ?string
    {
        return $this->form->getError();
    }

    /**
     * @inheritDoc
     */
    public function getPostURL(): string
    {
        return $this->form->getPostURL();
    }

    /**
     * @inheritDoc
     */
    public function withSubmitCaption(string $caption): self
    {
        $clone = clone $this;
        $clone->submit_button_label = $caption;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSubmitCaption(): ?string
    {
        return $this->submit_button_label;
    }

    public function getSubmitSignal(): Signal
    {
        return $this->submit_signal;
    }

    /**
     * No dedicated name can be set for this subform
     */
    public function withDedicatedName(string $dedicated_name): self
    {
        return $this;
    }
}
