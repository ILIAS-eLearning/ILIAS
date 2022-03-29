<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Toast;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements \ILIAS\UI\Component\Toast\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function standard($title, Icon $icon) : Toast
    {
        return new Toast($title, $icon, $this->signal_generator);
    }

    public function container() : Container
    {
        return new Container();
    }
}
