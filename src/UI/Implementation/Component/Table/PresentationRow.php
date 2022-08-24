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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Listing\Descriptive;

class PresentationRow implements T\PresentationRow
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var	Button|Dropdown|null
     */
    private $action = null;

    protected Signal $show_signal;
    protected Signal $close_signal;
    protected Signal $toggle_signal;
    private ?string $headline = null;
    private ?string $subheadline = null;
    private array $important_fields = [];
    private Descriptive $content;
    private ?string $further_fields_headline = null;
    private array $further_fields = [];
    private array $data;
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function withResetSignals(): T\PresentationRow
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * Set the signals for this component.
     */
    protected function initSignals(): void
    {
        $this->show_signal = $this->signal_generator->create();
        $this->close_signal = $this->signal_generator->create();
        $this->toggle_signal = $this->signal_generator->create();
    }

    /**
     * @inheritdoc
     */
    public function getShowSignal(): Signal
    {
        return $this->show_signal;
    }

    /**
     * @inheritdoc
     */
    public function getCloseSignal(): Signal
    {
        return $this->close_signal;
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
    public function withHeadline($headline): T\PresentationRow
    {
        $this->checkStringArg("string", $headline);
        $clone = clone $this;
        $clone->headline = $headline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    /**
     * @inheritdoc
     */
    public function withSubheadline($subheadline): T\PresentationRow
    {
        $this->checkStringArg("string", $subheadline);
        $clone = clone $this;
        $clone->subheadline = $subheadline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSubheadline(): ?string
    {
        return $this->subheadline;
    }

    /**
     * @inheritdoc
     */
    public function withImportantFields(array $fields): T\PresentationRow
    {
        $clone = clone $this;
        $clone->important_fields = $fields;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getImportantFields(): array
    {
        return $this->important_fields;
    }


    /**
     * @inheritdoc
     */
    public function withContent(Descriptive $content): T\PresentationRow
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContent(): Descriptive
    {
        return $this->content;
    }


    /**
     * @inheritdoc
     */
    public function withFurtherFieldsHeadline($headline): T\PresentationRow
    {
        $this->checkStringArg("string", $headline);
        $clone = clone $this;
        $clone->further_fields_headline = $headline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFurtherFieldsHeadline(): ?string
    {
        return $this->further_fields_headline;
    }

    /**
     * @inheritdoc
     */
    public function withFurtherFields(array $fields): T\PresentationRow
    {
        $clone = clone $this;
        $clone->further_fields = $fields;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFurtherFields(): array
    {
        return $this->further_fields;
    }


    /**
     * @inheritdoc
     */
    public function withAction($action): T\PresentationRow
    {
        $check =
            is_null($action)
            || $action instanceof Button
            || $action instanceof Dropdown;

        $expected =
            " NULL or " .
            " \ILIAS\UI\Component\Button\Button or " .
            " \ILIAS\UI\Component\ropdown\Dropdown";

        $this->checkArg("action", $check, $this->wrongTypeMessage($expected, $action));
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->action;
    }
}
