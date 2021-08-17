<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\MainControls as IMainControls;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements IMainControls\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected Slate\Factory $slate_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Slate\Factory $slate_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->slate_factory = $slate_factory;
    }

    /**
     * @inheritdoc
     */
    public function metaBar() : IMainControls\MetaBar
    {
        return new MetaBar($this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function mainBar() : IMainControls\MainBar
    {
        return new MainBar($this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function slate() : IMainControls\Slate\Factory
    {
        return $this->slate_factory;
    }

    /**
     * @inheritdoc
     */
    public function footer(array $links, string $text = '') : IMainControls\Footer
    {
        return new Footer($links, $text);
    }

    /**
     * @inheritDoc
     */
    public function modeInfo(string $title, URI $close_action) : IMainControls\ModeInfo
    {
        return new ModeInfo($title, $close_action);
    }

    /**
     * @inheritDoc
     */
    public function systemInfo(string $headline, string $information_text) : IMainControls\SystemInfo
    {
        return new SystemInfo(
            $this->signal_generator,
            $headline,
            $information_text
        );
    }
}
