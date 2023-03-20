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
 */

declare(strict_types=1);

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Modal\RoundTrip;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Dropzone\File\File as FileDropzone;
use ILIAS\UI\Component\Input\Field\File as FileInput;
use ILIAS\UI\Component\Signal;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\ReplaceSignal;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Closable;
use ILIAS\UI\Component\Component;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class File implements FileDropzone
{
    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;

    protected SignalGeneratorInterface $signal_generator;
    protected Signal $clear_signal;
    protected RoundTrip $modal;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        FieldFactory $field_factory,
        NameSource $name_source,
        FileInput $file_input,
        string $title,
        string $post_url
    ) {
        $this->signal_generator = $signal_generator;
        $this->clear_signal = $signal_generator->create();
        $this->modal = new RoundTrip(
            $signal_generator,
            $field_factory,
            $name_source,
            $title,
            null,
            [$file_input],
            $post_url
        );
    }

    public function getModal(): RoundTrip
    {
        return $this->modal;
    }

    public function getClearSignal(): Signal
    {
        return $this->clear_signal;
    }

    public function getTitle(): string
    {
        return $this->modal->getTitle();
    }

    public function withOnClose(Signal $signal): self
    {
        $clone = clone $this;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $clone->modal = $clone->modal->withOnClose($signal);
        return $clone;
    }

    public function appendOnClose(Signal $signal): self
    {
        $clone = clone $this;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $clone->modal = $clone->modal->appendOnClose($signal);
        return $clone;
    }

    public function getAsyncRenderUrl(): string
    {
        return $this->modal->getAsyncRenderUrl();
    }

    public function withAsyncRenderUrl(string $url)
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withAsyncRenderUrl($url);
        return $clone;
    }

    public function withCloseWithKeyboard(bool $state): self
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withCloseWithKeyboard($state);
        return $clone;
    }

    public function getCloseWithKeyboard(): bool
    {
        return $this->modal->getCloseWithKeyboard();
    }

    public function getShowSignal(): Signal
    {
        return $this->modal->getShowSignal();
    }

    public function getCloseSignal(): Signal
    {
        return $this->modal->getCloseSignal();
    }

    public function withOnLoad(Signal $signal)
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withOnLoad($signal);
        return $clone;
    }

    public function appendOnLoad(Signal $signal)
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->appendOnLoad($signal);
        return $clone;
    }

    public function getContent(): array
    {
        return $this->modal->getContent();
    }

    public function getActionButtons(): array
    {
        return $this->modal->getActionButtons();
    }

    public function getCancelButtonLabel(): string
    {
        return $this->modal->getCancelButtonLabel();
    }

    public function withActionButtons(array $buttons): self
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withActionButtons($buttons);
        return $clone;
    }

    public function withCancelButtonLabel(string $label): self
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withCancelButtonLabel($label);
        return $clone;
    }

    public function getReplaceSignal(): ReplaceSignal
    {
        return $this->modal->getReplaceSignal();
    }

    public function getPostURL(): string
    {
        return $this->modal->getPostURL();
    }

    public function withSubmitCaption(string $caption): self
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withSubmitCaption($caption);
        return $clone;
    }

    public function getSubmitCaption(): ?string
    {
        return $this->modal->getSubmitCaption();
    }

    public function getInputs(): array
    {
        return $this->modal->getInputs();
    }

    public function withRequest(ServerRequestInterface $request): self
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withRequest($request);
        return $clone;
    }

    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->modal = $clone->modal->withAdditionalTransformation($trafo);
        return $clone;
    }

    public function getData()
    {
        return $this->modal->getData();
    }

    public function getError(): ?string
    {
        return $this->modal->getError();
    }

    public function withOnDrop(Signal $signal): self
    {
        return $this->withTriggeredSignal($signal, 'drop');
    }

    public function withAdditionalDrop(Signal $signal): self
    {
        return $this->appendTriggeredSignal($signal, 'drop');
    }

    public function withResetSignals(): self
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    public function initSignals(): void
    {
        $this->clear_signal = $this->signal_generator->create();
        $this->modal->initSignals();
    }
}
