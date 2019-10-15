<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls as IMainControls;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements IMainControls\Factory
{
    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @var Slate\Factory
     */
    protected $slate_factory;

    /**
     * @param SignalGeneratorInterface $signal_generator
     */
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
    public function footer(array $links, string $text='') : IMainControls\Footer
    {
        return new Footer($links, $text);
    }
}
