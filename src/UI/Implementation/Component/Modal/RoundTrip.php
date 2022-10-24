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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\ReplaceSignal;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTrip extends Modal implements M\RoundTrip
{
    /**
     * @var Button\Button[]
     */
    protected array $action_buttons = array();

    /**
     * @var Component[]
     */
    protected array $content;
    protected string $title;
    protected string $cancel_button_label = 'cancel';
    protected ReplaceSignal $replace_signal;

    /**
     * @param Component|Component[] $content
     */
    public function __construct(string $title, $content, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        $content = $this->toArray($content);
        $types = array(Component::class);
        $this->checkArgListElements('content', $content, $types);
        $this->title = $title;
        $this->content = $content;
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
    public function withActionButtons(array $buttons): M\RoundTrip
    {
        $types = array(Button\Button::class);
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

    public function withCancelButtonLabel(string $label): M\RoundTrip
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
     */
    public function initSignals(): void
    {
        parent::initSignals();
        //signal generator from parent class
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->replace_signal = $this->signal_generator->create(ReplaceSignal::class);
    }

    /**
     * @inheritdoc
     */
    public function withContent(array $content): M\RoundTrip
    {
        $clone = clone $this;
        $clone->content = $content;

        return $clone;
    }
}
