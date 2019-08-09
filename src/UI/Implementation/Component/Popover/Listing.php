<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use \ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class ListingPopover
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Listing extends Popover implements Component\Popover\Listing
{

    /**
     * @var Component\Component[]
     */
    protected $items;


    /**
     * @param Component\Component[]    $items
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct($items, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        // TODO Correct type hinting and checks on list item, once this component is available in the framework
        $types = array( Component\Component::class );
        $this->checkArgListElements('items', $items, $types);
        $this->items = $items;
    }


    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
