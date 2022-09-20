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

namespace ILIAS\UI\Implementation\Component\Toast;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Toast as ComponentInterface;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Toast implements ComponentInterface\Toast
{
    use ComponentHelper;
    use JavaScriptBindable;

    public const DEFAULT_VANISH_TIME_IN_MS = 5000;
    public const DEFAULT_DELAY_TIME_IN_MS = 500;

    /**
     * @var string|Shy|Link
     */
    protected $title;
    protected Icon $icon;
    protected string $description = '';
    /** @var Link[] */
    protected array $links = [];
    protected string $action = '';
    protected SignalGeneratorInterface $signal_generator;
    protected Signal $signal;
    protected int $vanishTime = Toast::DEFAULT_VANISH_TIME_IN_MS;
    protected int $delayTime = Toast::DEFAULT_DELAY_TIME_IN_MS;

    public function __construct($title, Icon $icon, SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->title = $title;
        $this->icon = $icon;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function withDescription(string $description): ComponentInterface\Toast
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withAdditionalLink(Link $link): ComponentInterface\Toast
    {
        $new = clone $this;
        $new->links[] = $link;
        return $new;
    }

    public function withoutLinks(): ComponentInterface\Toast
    {
        $new = clone $this;
        $new->links = [];
        return $new;
    }

    /**
     * @return \ILIAS\UI\Component\Link\Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function withAction(string $action): ComponentInterface\Toast
    {
        $new = clone $this;
        $new->action = $action;
        return $new;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getIcon(): Icon
    {
        return $this->icon;
    }

    public function initSignals(): void
    {
        $this->signal = $this->signal_generator->create();
    }

    public function getShowSignal(): Signal
    {
        return $this->signal;
    }

    public function withVanishTime(int $vanishTime): Toast
    {
        $new = clone $this;
        $new->vanishTime = $vanishTime;
        return $new;
    }

    public function getVanishTime(): int
    {
        return $this->vanishTime;
    }

    public function withDelayTime(int $delayTime): Toast
    {
        $new = clone $this;
        $new->delayTime = $delayTime;
        return $new;
    }

    public function getDelayTime(): int
    {
        return $this->delayTime;
    }
}
