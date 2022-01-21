<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component as C;

/**
 * Class Factory
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Factory implements C\Popover\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function standard($content) : C\Popover\Standard
    {
        return new Standard($content, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function listing($items) : C\Popover\Listing
    {
        return new Listing($items, $this->signal_generator);
    }
}
