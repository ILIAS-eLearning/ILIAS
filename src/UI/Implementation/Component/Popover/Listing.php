<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
