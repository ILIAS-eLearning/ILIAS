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

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Item\Notification as NotificationItem;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Button\Bulky;

/**
 * Class Notification
 * @package ILIAS\UI\Implementation\Component\MainControls\Slate
 */
class Notification extends Slate implements ISlate\Notification
{
    /**
     * @var array<Slate|Bulky>
     */
    protected array $contents = [];

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        $notification_items,
        Symbol $symbol
    ) {
        $this->contents = $notification_items;
        parent::__construct($signal_generator, $name, $symbol);
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry(NotificationItem $entry): ISlate\Notification
    {
        $clone = clone $this;
        $clone->contents[] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function withMappedSubNodes(callable $f): ISlate\Notification
    {
        return $this;
    }
}
