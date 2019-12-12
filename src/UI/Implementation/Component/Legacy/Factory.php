<?php


namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements \ILIAS\UI\Component\Legacy\Factory
{
    protected $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function legacy($content)
    {
        return new Legacy($content, $this->signal_generator);
    }
}
