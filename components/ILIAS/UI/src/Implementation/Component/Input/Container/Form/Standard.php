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

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Prompt\IsPromptContentInternal;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable as JavaScriptBindableTrait;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This implements a standard form.
 */
class Standard extends Form implements C\Input\Container\Form\Standard, IsPromptContentInternal, JavaScriptBindable
{
    use HasPostURL;
    use JavaScriptBindableTrait;

    protected ?string $submit_caption = null;
    protected Signal $submit_signal;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        FieldFactory $field_factory,
        NameSource $name_source,
        string $post_url,
        array $inputs
    ) {
        parent::__construct($field_factory, $name_source, $inputs);
        $this->setPostURL($post_url);
        $this->submit_signal = $signal_generator->create();
    }

    /**
     * @inheritDoc
     */
    public function withSubmitLabel(string $label): C\Input\Container\Form\Standard
    {
        $clone = clone $this;
        $clone->submit_caption = $label;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSubmitLabel(): ?string
    {
        return $this->submit_caption;
    }

    public function getPromptButtons(): array
    {
        return [];
    }

    public function getPromptTitle(): string
    {
        return '';
    }

    public function getSubmitSignal(): Signal
    {
        return $this->submit_signal;
    }
}
