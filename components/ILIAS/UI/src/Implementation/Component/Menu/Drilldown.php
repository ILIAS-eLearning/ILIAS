<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;

/**
 * Drilldown Menu Control
 */
class Drilldown extends Menu implements IMenu\Drilldown
{
    use JavaScriptBindable;

    protected Signal $signal;
    protected ?string $persistence_id = null;
    protected string $no_items_text = '';

    /**
     * @param array <Sub|Component\Clickable|Component\Divider\Horizontal> $items
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        \ilLanguage $lng,
        string $label,
        array $items
    ) {
        $this->checkItemParameter($items);
        $this->label = $label;
        $this->items = $items;
        $this->signal = $signal_generator->create();
        $this->no_items_text = $lng->txt('drilldown_no_items');
    }

    public function getBacklinkSignal(): Signal
    {
        return $this->signal;
    }

    public function withPersistenceId(?string $id): self
    {
        if (is_null($id)) {
            return $this;
        }
        $clone = clone $this;
        $clone->persistence_id = $id;
        return $clone;
    }

    public function getPersistenceId(): ?string
    {
        return $this->persistence_id;
    }

    public function getNoItemsText(): string
    {
        return $this->no_items_text;
    }
}
