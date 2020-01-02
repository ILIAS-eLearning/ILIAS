<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Component\Popover\Item;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Factory
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Factory implements \ILIAS\UI\Component\Popover\Factory
{

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;


    /**
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }


    /**
     * @inheritdoc
     */
    public function standard($content)
    {
        return new Standard($content, $this->signal_generator);
    }


    /**
     * @inheritdoc
     */
    public function listing($items)
    {
        return new Listing($items, $this->signal_generator);
    }
}
