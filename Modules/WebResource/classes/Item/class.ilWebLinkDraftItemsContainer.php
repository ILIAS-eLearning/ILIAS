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

/**
 * Container class for drafted Web Link items.
 * Right now this does not really need to exist, but might come in handy
 * when additional transformations on a set of drafted items are needed.
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkDraftItemsContainer extends ilWebLinkBaseItemsContainer
{
    /**
     * @var ilWebLinkDraftItem[]
     */
    protected array $items;

    /**
     * @param ilWebLinkDraftItem[]   $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function addItem(ilWebLinkDraftItem $item) : void
    {
        $this->items[] = $item;
    }

    /**
     * @return ilWebLinkDraftItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    public function getFirstItem() : ?ilWebLinkDraftItem
    {
        return $this->items[0] ?? null;
    }
}
