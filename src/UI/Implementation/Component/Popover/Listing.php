<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class ListingPopover
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Listing extends Popover implements C\Popover\Listing
{
    /**
     * @var C\Component[]
     */
    protected array $items;

    /**
     * @param C\Component[] $items
     */
    public function __construct(array $items, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        // TODO Correct type hinting and checks on list item, once this component is available in the framework
        $types = array( C\Component::class );
        $this->checkArgListElements('items', $items, $types);
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
