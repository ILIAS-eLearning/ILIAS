<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements \ILIAS\UI\Component\Legacy\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function legacy(string $content) : \ILIAS\UI\Component\Legacy\Legacy
    {
        return new Legacy($content, $this->signal_generator);
    }
}
