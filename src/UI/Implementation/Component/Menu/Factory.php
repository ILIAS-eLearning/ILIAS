<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements IMenu\Factory
{
    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    public function __construct(
        SignalGeneratorInterface $signal_generator
    ) {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function drilldown(string $label, array $items) : IMenu\Drilldown
    {
        return new Drilldown($this->signal_generator, $label, $items);
    }

    /**
     * @inheritdoc
     */
    public function sub(string $label, array $items) : IMenu\Sub
    {
        return new Sub($label, $items);
    }
}
