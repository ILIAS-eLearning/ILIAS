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

namespace ILIAS\UI\Implementation\Component\Prompt;

use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;

class Prompt implements I\Prompt
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected ?string $title = null;
    protected Signal $show_signal;
    protected Signal $close_signal;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        protected URLBuilder $async_url_builder,
    ) {
        $this->show_signal = $signal_generator->create();
        $this->close_signal = $signal_generator->create();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function withTitle(string $title): static
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withParameter(URLBuilderToken $token, string|array $value): static
    {
        $clone = clone $this;
        $clone->async_url_builder = $clone->async_url_builder->withParameter($token, $value);
        return $clone;
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->async_url_builder;
    }

    /**
     * @param URI|null $uri @deprecated, legacy usages should fail loudly since another endpoint
     *                      would be used, which most likely results in weird behaviour.
     */
    public function getShowSignal(?URI $uri = null): Signal
    {
        if (null !== $uri) {
            throw new \LogicException('Prompt target must be changed using withParameter() instead.');
        }
        $signal = clone $this->show_signal;
        $signal = $signal->withOptions(['url' => (string) $this->getUrlBuilder()->buildURI()]);
        return $signal;
    }

    public function getCloseSignal(): Signal
    {
        return $this->close_signal;
    }
}
